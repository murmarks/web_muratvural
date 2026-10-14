<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Automated_Bulk_Edits' ) ) {

	class WPSE_Automated_Bulk_Edits {

		private static $instance = null;
		public $formulas_data    = null;
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
			add_action( 'vg_sheet_editor/scheduled/bulk-edit', array( $this, 'run_scheduled_bulk_edits' ) );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ), 999 );
			add_action( 'wp_ajax_vgse_scheduled_bulk_edits_modal', array( $this, 'get_scheduled_bulk_edits_modal' ) );
			add_action( 'vg_sheet_editor/formulas/after_form_fields', array( $this, 'render_form_fields' ) );
			add_action( 'vg_sheet_editor/automations/scheduled_jobs_table/bulk-edit/after_all_actions', array( $this, 'after_job_actions' ), 10, 2 );

		}

		public function get_edit_action_args( $column, $edit_type, $post_type ) {
			if ( ! $this->formulas_data ) {
				// Local cache for performance
				$this->formulas_data = WPSE_Formulas_UI_Obj()->get_formulas_data();
			}

			$column_settings = VGSE()->helpers->get_column_settings( $column, $post_type );
			$value_type      = empty( $column_settings['value_type'] ) ? 'text' : $column_settings['value_type'];
			if ( ! isset( $this->formulas_data['columns_actions'][ $value_type ][ $edit_type ] ) ) {
				return;
			}

			if ( $this->formulas_data['columns_actions'][ $value_type ][ $edit_type ] === 'default' ) {
				$out = $this->formulas_data['default_actions'][ $edit_type ];
			} else {
				$out = $this->formulas_data['columns_actions'][ $value_type ][ $edit_type ];
			}
			return $out;
		}

		public function after_job_actions( $row, $post_type ) {
			if ( ! WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				return;
			}
			$job         = WPSE_Automations_Core::get_job( $row['job_id'] );
			$action_args = $this->get_edit_action_args( $job['task_args']['column'], $job['task_args']['action_name'], $job['sheet_key'] );
			if ( ! $action_args ) {
				return;
			}

			$spreadsheet_columns = VGSE()->helpers->get_provider_columns( $job['sheet_key'], false );
			$column_title        = isset( $spreadsheet_columns[ $job['task_args']['column'] ] ) ? $spreadsheet_columns[ $job['task_args']['column'] ]['title'] : $job['task_args']['column'];
			?>
			<div class="job-details hidden">
				<h2><?php esc_html_e( 'Details', 'vg_sheet_editor_automations' ); ?></h2>
				<p><b><?php esc_html_e( 'Column', 'vg_sheet_editor_automations' ); ?>:</b> <?php echo esc_html( $column_title ); ?></p>
				<p><b><?php esc_html_e( 'Type of edit' ); ?>:</b> <?php echo esc_html( $action_args['label'] ); ?></p>
				<?php foreach ( $action_args['input_fields'] as $field_index => $input_field ) { ?>
					<p><b><?php echo esc_html( trim( $input_field['label'], '.' ) ); ?>:</b> <?php echo wp_kses_post( $job['task_args']['formula_data'][ $field_index ] ); ?></p>
				<?php } ?>				
				<?php do_action( 'vg_sheet_editor/automations/job_details/bulk-edit/after_job_details', $job, $post_type ); ?>
				<button type="button" class="button advanced-edit-bulk-edit"><?php esc_html_e( 'Edit details', 'vg_sheet_editor_automations' ); ?></button>
			</div>
			<?php
		}

		public function render_form_fields( $post_type ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}
			?>
			<li class="schedule-for-later-bulk-edit">
				<?php WPSE_Automations_Core_Obj()->render_form_fields( $post_type, 'bulk-edit', true ); ?>
			</li>
			<?php
		}

		public function init_rest_api() {
			$GLOBALS['wpse_automated_bulk_edits_rest'] = $this->get_job_api();
			$GLOBALS['wpse_automated_bulk_edits_rest']->register_hooks();
			$GLOBALS['wpse_automated_bulk_edits_rest']->register_routes();

			return $GLOBALS['wpse_automated_bulk_edits_rest'];
		}

		public function register_toolbar_items( $editor ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}

			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				$editor->args['toolbars']->register_item(
					'scheduled_bulk_edits',
					array(
						'type'                  => 'button',
						'content'               => __( 'View scheduled bulk edits', 'vg_sheet_editor_automations' ),
						'id'                    => 'scheduled_bulk_edits',
						'allow_in_frontend'     => true,
						'toolbar_key'           => 'primary',
						'extra_html_attributes' => 'data-remodal-target="vgse_scheduled_bulk_edits_modal"',
						'footer_callback'       => array( $this, 'render_scheduled_bulk_edits_popup' ),
						'parent'                => 'run_formula',
						'required_capability'   => 'manage_options',
						'icon'                  => 'fa fa-calendar',
					),
					$post_type
				);
			}
		}

		public function get_scheduled_bulk_edits_modal() {
			$job_api   = $this->get_job_api();
			$rows      = $job_api->get_scheduled_jobs_for_modal();
			$post_type = VGSE()->helpers->sanitize_table_key( $_GET['post_type'] );
			ob_start();
			include __DIR__ . '/views/scheduled-bulk-edits-modal.php';
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}
		public function render_scheduled_bulk_edits_popup( $post_type ) {
			WP_Sheet_Editor_Toolbar::render_base_modal_html( 60, 'vgse_scheduled_bulk_edits_modal', true, 'vgse-scheduled-actions-popup custom-ajax-insert' );
		}

		public function get_job_api() {
			if ( is_null( self::$job_api ) ) {
				self::$job_api = new WPSE_Automated_Job_REST_Base( '/sheet/bulk-edit', WP_REST_Server::CREATABLE, __( 'Scheduled bulk edits', 'vg_sheet_editor_automations' ), __( 'Bulk edit completed', 'vg_sheet_editor_automations' ), null );
			}
			return self::$job_api;
		}

		public function run_scheduled_bulk_edits( $job_id ) {
			$job_api = $this->get_job_api();
			$job_api->run_scheduled_task( $job_id );
		}


		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automated_Bulk_Edits();
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

if ( ! function_exists( 'WPSE_Automated_Bulk_Edits_Obj' ) ) {
	function WPSE_Automated_Bulk_Edits_Obj() {
		return WPSE_Automated_Bulk_Edits::get_instance();
	}
}
WPSE_Automated_Bulk_Edits_Obj();
