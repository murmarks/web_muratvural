<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Automated_AI_Add_New' ) ) {

	class WPSE_Automated_AI_Add_New {

		private static $instance = null;
		public static $job_api   = null;

		private function __construct() {
		}

		public function init() {
			if ( ! class_exists( 'WP_Sheet_Editor_AI_Core' ) ) {
				return;
			}
			add_action( 'rest_api_init', array( $this, 'init_rest_api' ), 11 );
			add_action( 'vg_sheet_editor/initialized', array( $this, 'after_wpse_init' ) );
		}
		public function after_wpse_init() {

			if ( ! WPSE_Automations_Core::can_initialize_on_request() ) {
				return;
			}

			add_action( 'vg_sheet_editor/ai/after_add_new_fields', array( $this, 'render_fields' ) );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ), 999 );
			add_action( 'wp_ajax_vgse_scheduled_ai_add_new_modal', array( $this, 'get_scheduled_ai_add_new_modal' ) );

			add_action( 'vg_sheet_editor/scheduled/add-new', array( $this, 'run_scheduled_ai_add_new' ) );
			add_filter( 'vg_sheet_editor/scheduled/add-new/request_params_for_task', array( $this, 'paginate_data_before_saving' ) );
			add_filter( 'vg_sheet_editor/automations/scheduled_jobs_table/extra_job_data/add-new', array( $this, 'add_modified_cells' ), 10, 2 );
			add_action( 'vg_sheet_editor/automations/scheduled_jobs_table/add-new/after_all_actions', array( $this, 'after_job_actions' ), 10, 2 );
		}

		public function render_fields( $post_type ) {
			?>
			<?php WPSE_Automations_Core_Obj()->render_form_fields( $post_type, 'ai_add_new', true ); ?>
			<?php
		}

		public function after_job_actions( $row, $post_type ) {
			if ( ! WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				return;
			}
			$job = WPSE_Automations_Core::get_job( $row['job_id'] );
			?>
			<div class="job-details hidden">
				<h2><?php esc_html_e( 'Parameters for new rows', 'vg_sheet_editor' ); ?></h2>
				<p><b><?php esc_html_e( 'How many rows do you want to add?', 'vg_sheet_editor_ai' ); ?></b> <?php echo esc_html( $job['task_args']['number_rows'] ); ?></p>
				<p><b><?php esc_html_e( 'What columns do you want to auto fill with the AI?', 'vg_sheet_editor_ai' ); ?></b> <?php echo esc_html( implode( ', ', $job['task_args']['columns_auto_fill'] ) ); ?></p>
				<p><b><?php esc_html_e( 'Prompt', 'vg_sheet_editor_ai' ); ?></b> <?php echo wp_kses_post( $job['task_args']['prompt'] ); ?></p>
				<?php do_action( 'vg_sheet_editor/automations/job_details/add-new/after_job_details', $job, $post_type ); ?>
				<button type="button" class="button advanced-edit-ai-add-new"><?php esc_html_e( 'Edit details', 'vg_sheet_editor_automations' ); ?></button>
			</div>
			<?php
		}


		public function add_modified_cells( $extra_data, $row ) {
			if ( WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				$job                             = WPSE_Automations_Core::get_job( $row['job_id'] );
				$extra_data['number_rows']       = $job['task_args']['number_rows'];
				$extra_data['columns_auto_fill'] = $job['task_args']['columns_auto_fill'];
				$extra_data['prompt']            = $job['task_args']['prompt'];
			}
			return $extra_data;
		}


		public function init_rest_api() {
			$GLOBALS['wpse_automated_ai_add_new_rest'] = $this->get_job_api();
			$GLOBALS['wpse_automated_ai_add_new_rest']->register_hooks();
			$GLOBALS['wpse_automated_ai_add_new_rest']->register_routes();

			return $GLOBALS['wpse_automated_ai_add_new_rest'];
		}

		public function paginate_data_before_saving( $job ) {
			$per_page = ! empty( $job['task_args']['per_page'] ) ? (int) $job['task_args']['per_page'] : VGSE()->get_option( 'duplicate_batch_size' );
			$total    = $job['task_args']['total'];
			if ( $job['page'] === 1 ) {
				$job['task_args']['processed_rows'] = 0;
			}
			$pending_rows = $total - $job['task_args']['processed_rows'];

			$rows_to_process                     = $pending_rows > $per_page ? $per_page : $pending_rows;
			$job['task_args']['processed_rows'] += $rows_to_process;

			WPSE_Automations_Core::update_task_args(
				array(
					'total'          => $total,
					'number_rows'    => $rows_to_process,
					'max_pages'      => ceil( $total / $per_page ),
					'processed_rows' => $job['task_args']['processed_rows'],
				),
				$job
			);
			$job['task_args']['max_pages']   = ceil( $total / $per_page );
			$job['task_args']['number_rows'] = $rows_to_process;

			return $job;
		}


		public function register_toolbar_items( $editor ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}

			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				$editor->args['toolbars']->register_item(
					'scheduled_ai_add_new',
					array(
						'type'                  => 'button',
						'content'               => __( 'View scheduled new rows', 'vg_sheet_editor_automations' ),
						'id'                    => 'scheduled_ai_add_new',
						'allow_in_frontend'     => true,
						'toolbar_key'           => 'primary',
						'extra_html_attributes' => 'data-remodal-target="vgse_scheduled_ai_add_new_modal"',
						'footer_callback'       => array( $this, 'render_scheduled_ai_add_new_popup' ),
						'parent'                => 'add_rows',
						'required_capability'   => 'manage_options',
						'icon'                  => 'fa fa-calendar',
					),
					$post_type
				);
			}
		}

		public function get_scheduled_ai_add_new_modal() {
			$job_api   = $this->get_job_api();
			$rows      = $job_api->get_scheduled_jobs_for_modal();
			$post_type = VGSE()->helpers->sanitize_table_key( $_GET['post_type'] );
			ob_start();
			include __DIR__ . '/views/scheduled-ai-add-new-modal.php';
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}
		public function render_scheduled_ai_add_new_popup( $post_type ) {
			WP_Sheet_Editor_Toolbar::render_base_modal_html( 60, 'vgse_scheduled_ai_add_new_modal', true, 'vgse-scheduled-actions-popup custom-ajax-insert' );
		}

		public function get_job_api() {
			if ( is_null( self::$job_api ) ) {
				self::$job_api = new WPSE_Automated_Job_REST_Base( '/ai/add-new', WP_REST_Server::CREATABLE, __( 'Scheduled new rows', 'vg_sheet_editor_automations' ), __( 'Rows created', 'vg_sheet_editor_automations' ), null );
			}
			return self::$job_api;
		}

		public function run_scheduled_ai_add_new( $job_id ) {
			$job_api = $this->get_job_api();
			$job_api->run_scheduled_task( $job_id );
		}


		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automated_AI_Add_New();
				self::$instance->init();
			}
			return self::$instance;
		}

		public function __set( $name, $value ) {
			$this->name = $value;
		}

		public function __get( $name ) {
			return $this->name;
		}
	}
}

if ( ! function_exists( 'WPSE_Automated_AI_Add_New_Obj' ) ) {
	function WPSE_Automated_AI_Add_New_Obj() {
		return WPSE_Automated_AI_Add_New::get_instance();
	}
}
WPSE_Automated_AI_Add_New_Obj();
