<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Automated_Save_Rows' ) ) {

	class WPSE_Automated_Save_Rows {

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

			add_action( 'vg_sheet_editor/scheduled/rows', array( $this, 'run_scheduled_save_rows' ) );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ), 999 );
			add_action( 'wp_ajax_vgse_scheduled_save_rows_modal', array( $this, 'get_scheduled_save_rows_modal' ) );
			add_filter( 'vg_sheet_editor/scheduled/rows/request_params_for_task', array( $this, 'paginate_data_before_saving' ) );
			add_filter( 'vg_sheet_editor/automations/scheduled_jobs_table/extra_job_data/rows', array( $this, 'add_modified_cells' ), 10, 2 );
			add_action( 'vg_sheet_editor/automations/after_job_edited/rows', array( $this, 'update_cell_data_after_job_edited' ), 10, 3 );
			add_action( 'vg_sheet_editor/automations/scheduled_jobs_table/rows/after_all_actions', array( $this, 'after_job_actions' ), 10, 2 );
		}

		public function after_job_actions( $row, $post_type ) {
			if ( ! WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				return;
			}
			$job         = WPSE_Automations_Core::get_job( $row['job_id'] );
			?>
			<div class="job-details hidden">
				<h2><?php esc_html_e( 'Modified cells', 'vg_sheet_editor' ); ?></h2>
				<div class="editable-cells"></div>
				<?php do_action( 'vg_sheet_editor/automations/job_details/rows/after_job_details', $job, $post_type ); ?>
			</div>
			<?php
		}

		public function update_cell_data_after_job_edited( $registered_params, $date_changed, $request ) {
			global $wpdb;
			$dirty_params = $request->get_params();
			if ( ! isset( $dirty_params['extra_data'] ) || ! isset( $dirty_params['extra_data']['cells_data'] ) || ! WPSE_Automations_Core::user_can_edit_its_own_job( $request['job_id'] ) ) {
				return;
			}

			$job                      = WPSE_Automations_Core::get_job( $request['job_id'] );
			$cell_data                = VGSE()->helpers->sanitize_data_for_db( $dirty_params['extra_data']['cells_data'], $job['sheet_key'] );
			$job['task_args']['data'] = $cell_data;

			$wpdb->update(
				$wpdb->prefix . 'wpse_scheduled_tasks',
				array(
					'task_args' => wp_json_encode( $job['task_args'] ),
				),
				array(
					'job_id' => $job['job_id'],
					'author' => get_current_user_id(),
				),
				array( '%s' ),
				array( '%s', '%d' )
			);
		}


		public function add_modified_cells( $extra_data, $row ) {
			if ( WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				$job                      = WPSE_Automations_Core::get_job( $row['job_id'] );
				$spreadsheet_columns      = VGSE()->helpers->get_provider_columns( $job['sheet_key'], false );
				$extra_data['cells_data'] = array();
				$base_row                 = array();
				foreach ( $job['task_args']['data'] as $sheet_row ) {
					$base_row = array_merge( $base_row, $sheet_row );
				}
				$column_titles = array();
				foreach ( $base_row as $key => $value ) {
					if ( isset( $spreadsheet_columns[ $key ] ) && ! empty( $spreadsheet_columns[ $key ]['title'] ) ) {
						$column_titles[] = $spreadsheet_columns[ $key ]['title'];
					} else {
						$column_titles[] = $key;
					}
				}

				foreach ( $job['task_args']['data'] as $sheet_row ) {
					$extra_data['cells_data'][] = wp_parse_args( $sheet_row, $base_row );
				}
				$extra_data['column_titles'] = $column_titles;
			}
			return $extra_data;
		}


		public function init_rest_api() {
			$GLOBALS['wpse_automated_save_rows_rest'] = $this->get_job_api();
			$GLOBALS['wpse_automated_save_rows_rest']->register_hooks();
			$GLOBALS['wpse_automated_save_rows_rest']->register_routes();

			return $GLOBALS['wpse_automated_save_rows_rest'];
		}

		public function paginate_data_before_saving( $job ) {
			$all_data                           = $job['task_args']['data'];
			$total                              = count( $all_data );
			$per_page                           = ! empty( $job['task_args']['per_page'] ) ? (int) $job['task_args']['per_page'] : VGSE()->get_option( 'automations_save_cells_batch_size', VGSE()->get_option( 'be_posts_per_page_save', 8 ) );
			$pages                              = array_chunk( $all_data, $per_page );
			$data                               = isset( $pages[ $job['task_args']['page'] - 1 ] ) ? $pages[ $job['task_args']['page'] - 1 ] : array();
			$job['task_args']['data']           = $data;
			$job['task_args']['max_pages']      = count( $pages );
			$job['task_args']['total']          = count( $all_data );
			$job['task_args']['processed_rows'] = ( $per_page >= $total || ( $per_page * $job['task_args']['page'] ) >= $total ) ? $total : $per_page * $job['task_args']['page'];

			return $job;
		}


		public function register_toolbar_items( $editor ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}

			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				$editor->args['toolbars']->register_item(
					'save_rows_later',
					array(
						'type'                  => 'button',
						'content'               => __( 'Save later', 'vg_sheet_editor_automations' ),
						'id'                    => 'save_rows_later',
						'allow_in_frontend'     => true,
						'toolbar_key'           => 'primary',
						'extra_html_attributes' => 'data-remodal-target="vgse_save_later_modal"',
						'footer_callback'       => array( $this, 'render_save_later_popup' ),
						'parent'                => 'save',
						'required_capability'   => 'manage_options',
						'icon'                  => 'fa fa-clock-o',
						'css_class'             => 'wpse-save-later',
					),
					$post_type
				);
				$editor->args['toolbars']->register_item(
					'scheduled_save_rows',
					array(
						'type'                  => 'button',
						'content'               => __( 'View scheduled changes', 'vg_sheet_editor_automations' ),
						'id'                    => 'scheduled_save_rows',
						'allow_in_frontend'     => true,
						'toolbar_key'           => 'primary',
						'extra_html_attributes' => 'data-remodal-target="vgse_scheduled_save_rows_modal"',
						'footer_callback'       => array( $this, 'render_scheduled_save_rows_popup' ),
						'parent'                => 'save',
						'required_capability'   => 'manage_options',
						'icon'                  => 'fa fa-calendar',
					),
					$post_type
				);
			}
		}

		public function render_save_later_popup( $post_type ) {
			include __DIR__ . '/views/save-later-popup.php';
		}
		public function get_scheduled_save_rows_modal() {
			$job_api   = $this->get_job_api();
			$rows      = $job_api->get_scheduled_jobs_for_modal();
			$post_type = VGSE()->helpers->sanitize_table_key( $_GET['post_type'] );
			ob_start();
			include __DIR__ . '/views/scheduled-save-rows-modal.php';
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}
		public function render_scheduled_save_rows_popup( $post_type ) {
			WP_Sheet_Editor_Toolbar::render_base_modal_html( 60, 'vgse_scheduled_save_rows_modal', true, 'vgse-scheduled-actions-popup custom-ajax-insert' );
		}

		public function get_job_api() {
			if ( is_null( self::$job_api ) ) {
				self::$job_api = new WPSE_Automated_Job_REST_Base( '/sheet/rows', WP_REST_Server::CREATABLE, __( 'Scheduled cell changes', 'vg_sheet_editor_automations' ), __( 'Saving completed', 'vg_sheet_editor_automations' ), null );
			}
			return self::$job_api;
		}

		public function run_scheduled_save_rows( $job_id ) {
			$job_api = $this->get_job_api();
			$job_api->run_scheduled_task( $job_id );
		}


		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automated_Save_Rows();
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

if ( ! function_exists( 'WPSE_Automated_Save_Rows_Obj' ) ) {
	function WPSE_Automated_Save_Rows_Obj() {
		return WPSE_Automated_Save_Rows::get_instance();
	}
}
WPSE_Automated_Save_Rows_Obj();


