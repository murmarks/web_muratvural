<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Automated_Imports' ) ) {

	class WPSE_Automated_Imports {

		private static $instance    = null;
		public static $job_api      = null;
		public static $ftp_importer = null;
		public static $url_importer = null;

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
			add_action( 'vg_sheet_editor/import/before_data_sources', array( $this, 'render_form_fields' ) );
			add_action( 'vg_sheet_editor/import/after_content', array( $this, 'render_result_step' ) );
			add_action( 'vg_sheet_editor/scheduled/import-rows', array( $this, 'run_scheduled_import' ) );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ), 999 );
			add_action( 'wp_ajax_vgse_scheduled_imports_modal', array( $this, 'get_scheduled_imports_modal' ) );
			add_action( 'vg_sheet_editor/automations/scheduled_jobs_table/import-rows/after_all_actions', array( $this, 'after_job_actions' ), 10, 2 );
			add_filter( 'vg_sheet_editor/scheduled/import-rows/request_params_for_task', array( $this, 'remove_unmapped_columns_from_csv' ), 10, 2 );

			// FTP
			if ( ! self::$ftp_importer ) {
				self::$ftp_importer = new WPSE_Automations_External_Import_FTP(
					array(
						'source_key'     => 'ftp',
						'source_label'   => 'FTP',
						'file_field_key' => 'ftp_file',
					)
				);
			}
			if ( ! self::$url_importer ) {
				self::$url_importer = new WPSE_Automations_External_Import_URL(
					array(
						'source_key'     => 'csv_url',
						'source_label'   => 'URL',
						'file_field_key' => 'file_url',
					)
				);
			}

			add_action( 'update_option_' . VGSE()->options_key, array( $this, 'maybe_delete_import_hashes' ), 10, 2 );
			add_action( 'vg_sheet_editor/automations/scheduled_jobs_table/import-rows/after_all_actions', array( $this, 'render_retry_batch_button' ), 10, 2 );
			add_action( 'vg_sheet_editor/scheduled/import-rows/before_task_execution', array( $this, 'verify_later_if_retry_needed' ), 10, 3 );
			add_action( 'vg_sheet_editor/scheduled/import-check-if-retry-needed', array( $this, 'maybe_auto_retry_import_batch' ) );
			add_action( 'vg_sheet_editor/scheduled/import-rows/after_batch_processed', array( $this, 'after_batch_processed' ), 10, 3 );
		}
		/**
		 * Cancel any retry action scheduled for this job, because the batch was processed successfully
		 *
		 * @param array $job The scheduled job object.
		 * @param array  $response_data The response data for the job.
		 * @param object  $runner The job runner instance.
		 */
		public function after_batch_processed( $job, $response_data, $runner ) {
			global $wpdb;

			$action_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT action_id FROM {$wpdb->prefix}actionscheduler_actions 
     			WHERE hook = %s AND args LIKE %s",
					'vg_sheet_editor/scheduled/import-check-if-retry-needed',
					'%"' . $wpdb->esc_like( $job['job_id'] ) . '"%'
				)
			);
			if ( $action_id ) {
				ActionScheduler_Store::instance()->delete_action( $action_id );
			}

			WPSE_Automations_Core::set_new_job_settings( $job['job_id'], array( 'last_auto_retry' => null ) );
		}
		/**
		 * Attempts to automatically retry an import batch if it is identified as stuck.
		 *
		 * @param string $job_id The ID of the job whose import batch is being checked for auto-retry.
		 */
		public function maybe_auto_retry_import_batch( $job_id ) {
			$job = WPSE_Automations_Core::get_job( $job_id );
			if ( ! $job ) {
				WPSE_Logger_Obj()->entry( __( 'No job found to retry', 'vg_sheet_editor_automations' ), $job_id );
				return;
			}
			// Check if the last execution timestamp is older than 1 hour
			if ( isset( $job['last_auto_retry'] ) && strtotime( $job['last_auto_retry'] ) >= ( time() - HOUR_IN_SECONDS ) ) {
				WPSE_Logger_Obj()->entry( __( 'The last retry was made less than an hour ago for job ID %s, skipping', 'vg_sheet_editor_automations' ), $job_id );
				return;
			}
			$stuck_action = WPSE_Automations_Core::get_stuck_batch( $job );
			if ( $stuck_action ) {
				WPSE_Logger_Obj()->entry( sprintf( __( 'A stuck action has been found for job ID %s and it will be retried', 'vg_sheet_editor_automations' ), $job_id ), $job_id );
				WPSE_Automations_Core::retry_failed_batch( $job_id );
				// Save the current timestamp as the last auto retry execution time
				WPSE_Automations_Core::set_new_job_settings( $job_id, array( 'last_auto_retry' => current_time( 'mysql', true ) ) );
			} else {
				WPSE_Logger_Obj()->entry( sprintf( __( 'No stuck action found for job ID %s, so no retry needed at this moment.', 'vg_sheet_editor_automations' ), $job_id ), $job_id );
			}
		}

		/**
		 * Checks in 15 minutes if a retry of an import job will be needed
		 *
		 * This method checks if a specific import job that also updates existing products needs to be retried after 15 minutes.
		 * If so, it schedules a single action to check again if a retry is needed.
		 *
		 *
		 * @param string    $job_id         The ID of the import job that needs to be checked.
		 * @param array  $job            The data of the import job that needs to be checked.
		 * @param object $runner          The runner object that is used to run the action.
		 * @return void
		 */
		public function verify_later_if_retry_needed( $job_id, $job, $runner ) {
			if ( ! $job || $job['category'] !== 'import-rows' ) {
				return;
			}

			// If the import is not configured to also update existing products, don't allow to retry to avoid creating duplicates
			if ( empty( $job['task_args']['existing_check_csv_field'] ) || empty( $job['task_args']['existing_check_wp_field'] ) ) {
				return;
			}

			// Disabled this check because this self-healing mechanism is useful for any import, as long
			// as we can safely update rows that were already imported
			// if ( empty( $job['task_args']['auto_retry_failed_batches'] ) ) {
			//  return;
			// }

			$minutes_threshold = (int) VGSE()->get_option( 'automations_import_auto_retry_delay', 15 );
			if ( ! $minutes_threshold ) {
				return;
			}
			$utc_timestamp = strtotime( current_time( 'mysql', true ) . ' +' . $minutes_threshold . ' minutes' );
			$group_slug    = 'sheet-editor-import-retry-' . $job['job_id'];
			ActionScheduler_Store::instance()->cancel_actions_by_group( $group_slug );
			$result = as_schedule_single_action(
				$utc_timestamp,
				'vg_sheet_editor/scheduled/import-check-if-retry-needed',
				array(
					// job_id must always be the first key, and content_ids must always be the second key
					// because we depend on this order for some sql queries
					'job_id' => $job['job_id'],
				),
				$group_slug
			);
		}

		/**
		* Renders the retry batch button for failed or stuck import jobs.
		*
		* @param array $row The row containing job data.
		* @param string $post_type The post type being imported.
		*
		* @return void
		*/
		function render_retry_batch_button( $row, $post_type ) {
			$job_id = $row['job_id'];
			$job    = WPSE_Automations_Core::get_job( $row['job_id'] );

			// If the import is not configured to also update existing products, don't allow to retry to avoid creating duplicates
			if ( ! $job || empty( $job['task_args']['existing_check_csv_field'] ) || empty( $job['task_args']['existing_check_wp_field'] ) ) {
				return;
			}
			if ( $job['status'] === 'failed' || WPSE_Automations_Core::get_stuck_batch( $job ) ) {
				?>
				<button type="button" class="button retry-batch" data-wpse-tooltip="up" aria-label="<?php echo esc_attr( __( 'Retry the last batch that failed or is stuck processing.', 'vg_sheet_editor_automations' ) ); ?>"><i class="fa fa-refresh"></i></button>
				<?php
			}
		}

		/**
		* Determines whether to delete import hash files when the 'automations_gs_import_all_rows_always' setting is changed.
		*
		* @param mixed $old_value The previous value of the setting.
		* @param mixed $value The new value of the setting.
		*
		* @return void
		*/
		public function maybe_delete_import_hashes( $old_value, $value ) {
			if ( is_array( $old_value ) && is_array( $value ) && empty( $old_value['automations_gs_import_all_rows_always'] ) && ! empty( $value['automations_gs_import_all_rows_always'] ) ) {

				$files = glob( WPSE_CSV_API_Obj()->long_lived_dir . 'row-hashes-*.csv' );
				foreach ( $files as $file_path ) {
					if ( file_exists( $file_path ) ) {
						unlink( $file_path );
					}
				}
			}
		}

		/**
		* Removes unmapped columns from a CSV file before importing.
		*
		* @param array $job The job data.
		* @param WPSE_CSV_Runner $runner The CSV runner object.
		*
		* @return array
		*/
		public function remove_unmapped_columns_from_csv( $job, $runner ) {
			if ( ! $job || ! empty( $job['task_args']['file_position'] ) || 'google_sheets' === $job['task_args']['source'] ) {
				return $job;
			}

			$this->_remove_unmapped_columns_from_csv( $job );
			return $job;
		}

		public function _remove_unmapped_columns_from_csv( $job ) {

			$base_dir  = wp_normalize_path( WPSE_CSV_API_Obj()->imports_dir );
			$file_path = $base_dir . $job['task_args']['import_file'];

			$mapped_columns = array_keys( array_filter( array_combine( $job['task_args']['source_column'], $job['task_args']['sheet_editor_column'] ) ) );

			if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
				return;
			}

			$base_dir       = wp_normalize_path( WPSE_CSV_API_Obj()->imports_dir );
			$temp_file_path = $base_dir . sanitize_file_name( wp_generate_uuid4() ) . '.csv';

			// Loop in batches of 100 rows to prevent memory leaks
			$position       = 0;
			$csv_data       = WPSE_CSV_API_Obj()->get_rows( $file_path, ',', false, 100, $position );
			$mapped_columns = array_fill_keys( $mapped_columns, '' );
			while ( $csv_data['rows'] ) {
				if ( empty( $csv_data['rows'] ) ) {
					break;
				}
				$new_rows = array();
				foreach ( $csv_data['rows'] as $index => $row ) {
					if ( empty( $row ) ) {
						continue;
					}

					$new_rows[] = array_intersect_key( $row, $mapped_columns );
				}

				WPSE_CSV_API_Obj()->_array_to_csv( $new_rows, $temp_file_path );
				$position = $csv_data['file_position'];
				$csv_data = WPSE_CSV_API_Obj()->get_rows( $file_path, ',', false, 100, $position );
			}
			unlink( $file_path );
			rename( $temp_file_path, $file_path );
		}
		public function after_job_actions( $row, $post_type ) {
			if ( ! WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				return;
			}
			$job = WPSE_Automations_Core::get_job( $row['job_id'] );

			$column_titles = array();
			foreach ( $job['task_args']['source_column'] as $index => $external_column_title ) {
				if ( ! empty( $job['task_args']['sheet_editor_column'][ $index ] ) ) {
					$column_titles[] = sanitize_text_field( $external_column_title );
				}
			}
			$column_titles = implode( ', ', $column_titles );

			$find_items_to_update_by_column_titles = false;
			if ( ! empty( $job['task_args']['existing_check_wp_field'] ) ) {
				$spreadsheet_columns                   = wp_list_pluck( VGSE()->helpers->get_provider_columns( $job['sheet_key'], false ), 'title', 'key' );
				$find_items_to_update_by_column_titles = implode( ', ', array_intersect_key( $spreadsheet_columns, array_flip( $job['task_args']['existing_check_wp_field'] ) ) );
			}
			$data_source_options = vgse_universal_sheet()->get_import_sources_options();
			?>
			<div class="job-details hidden">
				<h2><?php esc_html_e( 'Details', 'vg_sheet_editor_automations' ); ?></h2>
				<p><b><?php esc_html_e( 'Data source', 'vg_sheet_editor_automations' ); ?>:</b> 
										<?php
										echo isset( $data_source_options[ $job['task_args']['source'] ] ) ? esc_html( $data_source_options[ $job['task_args']['source'] ] ) : esc_html__( 'N/A', 'vg_sheet_editor_automations' );
										?>
				</p>
				<p><b><?php esc_html_e( 'File', 'vg_sheet_editor_automations' ); ?>:</b> 
										<?php
										if ( $job['task_args']['source'] === 'csv_url' ) {
											echo esc_url( $job['task_args']['file_url'] );
										} elseif ( $job['task_args']['source'] === 'server_file' ) {
											echo esc_html( $job['task_args']['server_file'] );
										}
										do_action( 'vg_sheet_editor/automations/job_details/import-rows/after_file_data', $job );
										?>
				</p>
				
				<p><b><?php esc_html_e( 'Columns that will be saved', 'vg_sheet_editor_automations' ); ?>:</b> <?php echo esc_html( $column_titles ); ?></p>
				<p><b><?php esc_html_e( 'Type of update', 'vg_sheet_editor_automations' ); ?>:</b> 
										<?php
										if ( $job['task_args']['writing_type'] === 'both' ) {
											esc_html_e( 'Create new items and update existing items', 'vg_sheet_editor' );
										} elseif ( $job['task_args']['writing_type'] === 'all_new' ) {
											esc_html_e( 'Import all rows as new', 'vg_sheet_editor' );
										} elseif ( $job['task_args']['writing_type'] === 'only_new' ) {
											esc_html_e( 'Only create new items, ignore existing items', 'vg_sheet_editor' );
										} elseif ( $job['task_args']['writing_type'] === 'only_update' ) {
											esc_html_e( 'Update existing items, ignore new items', 'vg_sheet_editor' );
										}
										?>
				</p>

				<?php if ( $job['task_args']['writing_type'] !== 'all_new' && $find_items_to_update_by_column_titles ) { ?>
					<p><b><?php esc_html_e( 'How do we find existing items to update?', 'vg_sheet_editor_automations' ); ?>:</b> 
											<?php
											echo esc_html( $find_items_to_update_by_column_titles );
											?>
				</p>
				<p><b><?php esc_html_e( 'Separator', 'vg_sheet_editor_automations' ); ?>:</b> <?php echo esc_html( $job['task_args']['separator'] ); ?></p>
					<?php } ?>
				<?php do_action( 'vg_sheet_editor/automations/job_details/import-rows/after_job_details', $job, $post_type ); ?>
				<button type="button" class="button advanced-edit-import"><?php esc_html_e( 'Edit details', 'vg_sheet_editor_automations' ); ?></button>
			</div>
			<?php
		}

		public function init_rest_api() {
			$GLOBALS['wpse_automated_imports_rest'] = $this->get_job_api();
			$GLOBALS['wpse_automated_imports_rest']->register_hooks();
			$GLOBALS['wpse_automated_imports_rest']->register_routes();

			return $GLOBALS['wpse_automated_imports_rest'];
		}
		function register_toolbar_items( $editor ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}

			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				$editor->args['toolbars']->register_item(
					'scheduled_imports',
					array(
						'type'                  => 'button',
						'content'               => __( 'View scheduled imports', 'vg_sheet_editor_automations' ),
						'id'                    => 'scheduled_imports',
						'allow_in_frontend'     => true,
						'toolbar_key'           => 'secondary',
						'extra_html_attributes' => 'data-remodal-target="vgse_scheduled_imports_modal"',
						'footer_callback'       => array( $this, 'render_scheduled_imports_popup' ),
						'parent'                => 'import_csv',
						'required_capability'   => 'manage_options',
						'icon'                  => 'fa fa-calendar',
					),
					$post_type
				);
			}
		}
		function get_scheduled_imports_modal() {
			$job_api   = $this->get_job_api();
			$rows      = $job_api->get_scheduled_jobs_for_modal();
			$post_type = VGSE()->helpers->sanitize_table_key( $_GET['post_type'] );
			ob_start();
			include __DIR__ . '/views/scheduled-imports-modal.php';
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}
		function render_scheduled_imports_popup( $post_type ) {
			WP_Sheet_Editor_Toolbar::render_base_modal_html( 60, 'vgse_scheduled_imports_modal', true, 'vgse-scheduled-actions-popup custom-ajax-insert' );
		}

		public function get_job_api() {
			if ( is_null( self::$job_api ) ) {
				self::$job_api = new WPSE_Automated_Job_REST_Base( '/sheet/import-rows', WP_REST_Server::CREATABLE, __( 'Scheduled import', 'vg_sheet_editor_automations' ), __( 'Import completed', 'vg_sheet_editor_automations' ), null );
			}
			return self::$job_api;
		}

		public function run_scheduled_import( $job_id ) {
			$job_api = $this->get_job_api();
			$job_api->run_scheduled_task( $job_id );
		}

		public function render_result_step( $post_type ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}
			?>
			<div class="hidden scheduled-import-response">
				<div class="response"></div>
				<button data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', 'vg_sheet_editor_automations' ); ?></button>
			</div>
			<?php
		}
		public function render_form_fields( $post_type ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}
			WPSE_Automations_Core_Obj()->render_form_fields( $post_type, 'import', true );
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automated_Imports();
				self::$instance->init();
			}
			return self::$instance;
		}

		public function __set( $name, $value ) {
			$this->$name = $value;
		}

		public function __get( $name ) {
			return $this->$name;
		}
	}
}

if ( ! function_exists( 'WPSE_Automated_Imports_Obj' ) ) {
	/**
	 * @return WPSE_Automated_Imports
	 */
	function WPSE_Automated_Imports_Obj() {
		return WPSE_Automated_Imports::get_instance();
	}
}
WPSE_Automated_Imports_Obj();


