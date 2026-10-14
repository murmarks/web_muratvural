<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSE_Automated_Exports' ) ) {

	class WPSE_Automated_Exports {

		private static $instance = null;
		public static $job_api   = null;

		private function __construct() {
		}

		public function init() {
			add_action( 'rest_api_init', array( $this, 'init_rest_api' ), 11 );
			add_action( 'vg_sheet_editor/initialized', array( $this, 'after_wpse_init' ) );
		}
		public function after_wpse_init() {

			if ( ! WPSE_Automations_Core::can_initialize_on_request() ) {
				return;
			}
			add_action( 'vg_sheet_editor/scheduled/export-rows', array( $this, 'run_scheduled_export' ) );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ), 999 );
			add_action( 'vg_sheet_editor/export/after_form_fields', array( $this, 'render_schedule_fields' ) );
			add_action( 'wp_ajax_vgse_scheduled_exports_modal', array( $this, 'get_scheduled_exports_modal' ) );
			add_action( 'vg_sheet_editor/automations/scheduled_jobs_table/export-rows/after_all_actions', array( $this, 'after_job_actions' ), 10, 2 );
			add_action( 'vg_sheet_editor/automations/job_completed_email/before_data_list', array( $this, 'add_details_to_job_completed_email' ), 10, 2 );
			add_filter( 'vg_sheet_editor/exports/allow_to_save_for_later', array( $this, 'dont_save_for_later_scheduled_exports_as_manual_exports' ), 10, 3 );
		}

		/**
		 * Don't save this export in the list of saved manual exports because this export is a
		 * scheduled task, saved/managed in the scheduled exports popup.
		 *
		 * @param  bool $allowed_to_save
		 * @param  array $out
		 * @param  array $clean_data
		 * @return bool
		 */
		public function dont_save_for_later_scheduled_exports_as_manual_exports( $allowed_to_save, $out, $clean_data ) {
			$job_id        = preg_replace( '/-r-.+$/', '', $clean_data['wpse_job_id'] );
			$scheduled_job = WPSE_Automations_Core::get_job( $job_id );
			if ( ! empty( $scheduled_job ) ) {
				$allowed_to_save = false;
			}
			return $allowed_to_save;
		}
		public function add_details_to_job_completed_email( $job, $response_data ) {
			if ( $job['category'] === 'export-rows' ) {
				$spreadsheet_columns = wp_list_pluck( VGSE()->helpers->get_provider_columns( $job['sheet_key'], false ), 'title', 'key' );
				$export_columns_keys = array_flip( explode( ',', $job['task_args']['custom_enabled_columns'] ) );
				$column_titles       = array_values( array_intersect_key( $spreadsheet_columns, $export_columns_keys ) );

				?>
<li><b><?php esc_html_e( 'Exported columns:', 'vg_sheet_editor_automations' ); ?></b>
				<?php echo esc_html( implode( ', ', $column_titles ) ); ?>
		</li>
					<?php
			}
		}

		public function render_schedule_fields( $post_type ) {
			WPSE_Automations_Core_Obj()->render_form_fields( $post_type, 'export-rows' );
		}
		public function after_job_actions( $row, $post_type ) {
			if ( ! WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				return;
			}
			$job = WPSE_Automations_Core::get_job( $row['job_id'] );

			$spreadsheet_columns = wp_list_pluck( VGSE()->helpers->get_provider_columns( $job['sheet_key'], false ), 'title', 'key' );
			$export_columns_keys = array_flip( explode( ',', $job['task_args']['custom_enabled_columns'] ) );
			$column_titles       = array_values( array_intersect_key( $spreadsheet_columns, $export_columns_keys ) );
			?>
			<div class="job-details hidden">
				<h2><?php esc_html_e( 'Details', 'vg_sheet_editor_automations' ); ?></h2>
				<p><b><?php esc_html_e( 'Columns', 'vg_sheet_editor_automations' ); ?>:</b> <?php echo esc_html( implode( ', ', $column_titles ) ); ?></p>
				<p><b><?php esc_html_e( 'Rows per page', 'vg_sheet_editor_automations' ); ?>:</b> <?php echo esc_html( $job['task_args']['posts_per_page'] ); ?></p>
				<?php do_action( 'vg_sheet_editor/automations/job_details/export-rows/after_job_details', $job, $post_type ); ?>
				<button type="button" class="button advanced-edit-export"><?php esc_html_e( 'Edit details', 'vg_sheet_editor_automations' ); ?></button>
			</div>
			<?php
		}

		public function init_rest_api() {
			$GLOBALS['wpse_automated_exports_rest'] = $this->get_job_api();
			$GLOBALS['wpse_automated_exports_rest']->register_hooks();
			$GLOBALS['wpse_automated_exports_rest']->register_routes();

			return $GLOBALS['wpse_automated_exports_rest'];
		}

		function register_toolbar_items( $editor ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}

			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				$editor->args['toolbars']->register_item(
					'scheduled_exports',
					array(
						'type'                  => 'button',
						'content'               => __( 'View scheduled exports', 'vg_sheet_editor_automations' ),
						'id'                    => 'scheduled_exports',
						'allow_in_frontend'     => true,
						'toolbar_key'           => 'secondary',
						'extra_html_attributes' => 'data-remodal-target="vgse_scheduled_exports_modal"',
						'footer_callback'       => array( $this, 'render_scheduled_exports_popup' ),
						'parent'                => 'export_csv',
						'required_capability'   => 'manage_options',
						'icon'                  => 'fa fa-calendar',
					),
					$post_type
				);
			}
		}
		function render_scheduled_exports_popup( $post_type ) {
			WP_Sheet_Editor_Toolbar::render_base_modal_html( 60, 'vgse_scheduled_exports_modal', true, 'vgse-scheduled-actions-popup custom-ajax-insert' );
		}
		public function get_job_api() {
			if ( is_null( self::$job_api ) ) {
				self::$job_api = new WPSE_Automated_Job_REST_Base( '/sheet/export-rows', WP_REST_Server::CREATABLE, __( 'Scheduled exports', 'vg_sheet_editor_automations' ), __( 'Export completed', 'vg_sheet_editor_automations' ), 'export_file_url', 'export_file_name' );
			}
			return self::$job_api;
		}
		function get_scheduled_exports_modal() {
			$job_api   = $this->get_job_api();
			$rows      = $job_api->get_scheduled_jobs_for_modal();
			$post_type = VGSE()->helpers->sanitize_table_key( $_GET['post_type'] );
			ob_start();
			include __DIR__ . '/views/scheduled-exports-modal.php';
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}
		public function run_scheduled_export( $job_id ) {
			$job_api = $this->get_job_api();
			$job_api->run_scheduled_task( $job_id );
		}


		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automated_Exports();
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

if ( ! function_exists( 'wpse_automated_exports_obj' ) ) {
	function wpse_automated_exports_obj() {
		return WPSE_Automated_Exports::get_instance();
	}
}
wpse_automated_exports_obj();


