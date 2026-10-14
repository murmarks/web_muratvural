<?php

if ( ! class_exists( 'WPSE_Automations_External_Import' ) ) {
	abstract class WPSE_Automations_External_Import {
		public $source_key;
		public $source_label;
		public $file_field_key;
		public function __construct( $args = array() ) {
			$defaults             = array(
				'source_key'     => null,
				'source_label'   => null,
				'file_field_key' => null,
				'is_watchable'   => false,
			);
			$args                 = wp_parse_args( $args, $defaults );
			$this->source_key     = $args['source_key'];
			$this->source_label   = $args['source_label'];
			$this->file_field_key = $args['file_field_key'];

			add_filter( 'vg_sheet_editor/universal_sheet/import_sources_options', array( $this, 'register_import_source' ) );
			add_action( 'vg_sheet_editor/import/after_data_sources_fields', array( $this, 'render_import_fields_wrapper' ) );
			add_action( 'vg_sheet_editor/import/prepare_data_for_import', array( $this, 'fetch_file_for_manual_import' ), 10, 4 );
			add_filter( 'vg_sheet_editor/scheduled/import-rows/request_params_for_task', array( $this, 'refresh_csv_before_new_scheduled_import' ), 15, 2 );
			add_action( 'vg_sheet_editor/automations/job_details/import-rows/after_file_data', array( $this, 'render_file_link_for_import_details' ) );
			add_filter( 'vg_sheet_editor/scheduled/import-rows/rows_for_table', array( $this, 'link_import_job_names_to_external_site' ), 10, 2 );
			if ( $args['is_watchable'] ) {
				add_action( 'vg_sheet_editor/automations/job_status_changed/import-rows/paused', array( $this, 'stop_watching_external_sheet' ) );
				add_action( 'vg_sheet_editor/automations/job_status_changed/import-rows/archived', array( $this, 'stop_watching_external_sheet' ) );
				add_action( 'vg_sheet_editor/automations/job_status_changed/import-rows/cancelled', array( $this, 'stop_watching_external_sheet' ) );
				add_action( 'vg_sheet_editor/automations/job_status_changed/import-rows/failed', array( $this, 'stop_watching_external_sheet' ) );
				add_action( 'vg_sheet_editor/automations/job_status_changed/import-rows/pre_delete', array( $this, 'stop_watching_external_sheet' ) );
				add_action( 'vg_sheet_editor/scheduled/import-rows/after_one_time_job_finished', array( $this, 'start_watching_external_changes' ) );
				add_action( 'vg_sheet_editor/automations/job_status_changed/import-rows/completed', array( $this, 'start_watching_external_changes' ) );
			}
		}
		abstract public function get_file_url_in_external_site( $external_file );
		abstract public function start_watching_file( $job );
		abstract public function stop_watching_file( $job );
		/**
		 * Get the CSV content from the external location
		 *
		 * @param  string $external_file
		 * @return string|WP_Error Valid CSV string on success or WP_Error on failure. Failure is considered even if the file was retrieved successfully but the content is empty
		 */
		abstract public function get_external_file_contents( $external_file );
		abstract public function render_external_file_link( $job );
		abstract public function render_import_fields( $post_type );

		public function link_import_job_names_to_external_site( $rows, $runner ) {
			foreach ( $rows as $index => $row ) {
				if ( $row['task_args']['source'] === $this->source_key && ! empty( $row['task_args'][ $this->file_field_key ] ) ) {
					$url = $this->get_file_url_in_external_site( $row['task_args'][ $this->file_field_key ] );
					if ( empty( $url ) ) {
						continue;
					}
					$rows[ $index ]['job_name'] = '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $row['job_name'] ) . ' <i class="fa fa-external-link"></i></a>';
				}
			}
			return $rows;
		}
		public function start_watching_external_changes( $job_id ) {
			global $wpdb;
			$job = WPSE_Automations_Core::get_job( $job_id );

			// Exit if this is not a Google Sheet import, if this is not a real time import, or we're already watching the Google Sheet file
			if ( empty( $job['task_args'][ $this->file_field_key ] ) || empty( $job['task_args']['real_time_import'] ) || ! empty( $job['task_args']['watching_google_sheets'] ) ) {
				return;
			}

			$current_user_id = WP_Sheet_Editor_Helpers::get_current_user_id();
			WP_Sheet_Editor_Helpers::set_current_user( (int) $job['author'] );

			$this->start_watching_file( $job );

			WP_Sheet_Editor_Helpers::set_current_user( (int) $current_user_id );
			WPSE_Automations_Core::update_task_args(
				array(
					'watching_google_sheets' => true,
				),
				$job
			);
		}

		public function stop_watching_external_sheet( $job_id ) {
			global $wpdb;
			$job = WPSE_Automations_Core::get_job( $job_id );
			if ( empty( $job['task_args']['watching_google_sheets'] ) ) {
				return;
			}
			$this->stop_watching_file( $job );

			WPSE_Automations_Core::update_task_args(
				array(
					'watching_google_sheets' => false,
				),
				$job
			);
		}
		public function render_file_link_for_import_details( $job ) {
			if ( $this->source_key !== $job['task_args']['source'] ) {
				return;
			}
			$this->render_external_file_link( $job );
		}
		public function render_import_fields_wrapper( $post_type ) {
			?>
			<div class="data-input <?php echo esc_attr( $this->source_key ); ?>">
				<?php $this->render_import_fields( $post_type ); ?>
				<button class="button button-primary button-primario vgse-upload-csv-file next-step step-nav" data-type="<?php echo esc_attr( $this->source_key ); ?>"><?php esc_html_e( 'Next', 'vg_sheet_editor_automations' ); ?> <i class="fa fa-chevron-right"></i></button>
			</div>
			<?php
		}
		public function register_import_source( $options ) {
			$options[ $this->source_key ] = esc_html( $this->source_label );
			return $options;
		}
		public function fetch_file_for_manual_import( $file_path, $data, $data_type, $post_type ) {
			if ( $this->source_key !== $data_type || empty( $data ) ) {
				return;
			}

			$result = $this->fetch_external_file_contents_for_import( $file_path, $data );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}
		}


		public function fetch_external_file_contents_for_import( $local_csv_file_path, $external_file ) {
			$csv_contents = $this->get_external_file_contents( $external_file );

			if ( is_wp_error( $csv_contents ) ) {
				return $csv_contents;
			} elseif ( ! empty( $csv_contents ) ) {
				file_put_contents( $local_csv_file_path, $csv_contents );
			}
		}

		public function handle_error_retrieving_external_data( $wp_error, $job_id, $runner ) {
			$retry_minutes = (int) VGSE()->get_option( 'automations_failed_api_import_retry', 360 );
			WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: We were unable to retrieve new data from the external source (%2$s) for the next import. We\'ll retry every %3$d minutes. Error: %4$s.', 'vg_sheet_editor_automations' ), $runner->job_label, $this->source_label, $retry_minutes, $wp_error->get_error_message() ), $job_id );

			WPSE_Automations_Core::set_new_job_settings(
				$job_id,
				array(
					'reschedule_in_x_seconds' => MINUTE_IN_SECONDS * $retry_minutes,
				)
			);
		}

		public function remove_unchanged_rows_from_csv_file( $file_path, $job, $total_rows = null ) {
			$job_id = $job['job_id'];
			if ( empty( $job['last_imported_hashes_file_name'] ) ) {
				$job['last_imported_hashes_file_name'] = 'row-hashes-' . $job_id . '.txt';
				WPSE_Automations_Core::set_new_job_settings(
					$job_id,
					array(
						'last_imported_hashes_file_name' => $job['last_imported_hashes_file_name'],
					)
				);
			}

			if ( is_null( $total_rows ) ) {
				$total_rows = WPSE_CSV_API_Obj()->count_rows_in_file( $file_path, $job['task_args']['separator'] );
			}
			$hashes_file_path = wp_normalize_path( WPSE_CSV_API_Obj()->long_lived_dir ) . $job['last_imported_hashes_file_name'];
			if ( file_exists( $hashes_file_path ) ) {
				$existing_hashes = array_flip( file( $hashes_file_path, FILE_IGNORE_NEW_LINES ) );
			} else {
				$existing_hashes = array();
			}
			$new_file_path  = str_replace( '.csv', '-purged.csv', $file_path );
			$new_rows_count = 0;
			$new_hashes     = array();

			// Loop in batches of 100 rows to prevent memory leaks
			$position = 0;
			$csv_data = WPSE_CSV_API_Obj()->get_rows( $file_path, ',', false, 100, $position );
			while ( $csv_data['rows'] ) {
				if ( empty( $csv_data['rows'] ) ) {
					break;
				}
				foreach ( $csv_data['rows'] as $index => $row ) {
					ksort( $row );
					$row_hash = md5( wp_json_encode( $row ) );
					if ( isset( $existing_hashes[ $row_hash ] ) ) {
						$new_hashes[] = $row_hash;
						unset( $csv_data['rows'][ $index ] );
						continue;
					}
					++$new_rows_count;
					$existing_hashes[ $row_hash ] = $row_hash;
					$new_hashes[]                 = $row_hash;
				}
				if ( ! empty( $csv_data['rows'] ) ) {
					WPSE_CSV_API_Obj()->_array_to_csv( $csv_data['rows'], $new_file_path );
				}
				$position = $csv_data['file_position'];
				$csv_data = WPSE_CSV_API_Obj()->get_rows( $file_path, ',', false, 100, $position );
			}
			// Replace the old file with the new file
			unlink( $file_path );
			if ( $new_rows_count > 0 ) {
				rename( $new_file_path, $file_path );
			}
			file_put_contents( $hashes_file_path, implode( PHP_EOL, $new_hashes ) );

			// Recount the file rows for the batch progress messages
			$total_rows = $new_rows_count;
			return $total_rows;
		}

		public function _get_row_ids_from_csv( $file_path, $identifier_column ) {
			$existing_ids = array();

			// Loop in batches of 100 rows to prevent memory leaks
			$position = 0;
			$csv_data = WPSE_CSV_API_Obj()->get_rows( $file_path, ',', false, 100, $position );
			while ( $csv_data['rows'] ) {
				if ( empty( $csv_data['rows'] ) ) {
					break;
				}
				foreach ( $csv_data['rows'] as $index => $row ) {
					if ( empty( $row[ $identifier_column ] ) ) {
						continue;
					}

					$existing_ids[] = $row[ $identifier_column ];
				}
				$position = $csv_data['file_position'];
				$csv_data = WPSE_CSV_API_Obj()->get_rows( $file_path, ',', false, 100, $position );
			}

			return $existing_ids;
		}
		/**
		 * Detect which rows from the previous external import are missing from the new external import, and generate an array of arrays with status=trash, only when the import job has the option trash_wp_rows_missing_from_gs enabled
		 *
		 * @param  string $file_path Import CSV path
		 * @param  array $job
		 * @return void|array null if the feature is not enabled in the import or array of new rows with trash status
		 */
		public function detect_removed_rows_from_external_file( $file_path, $job ) {
			global $wpdb;
			$job_id = $job['job_id'];

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' ' . var_export( compact( 'job', 'file_path' ), true ), $job_id );
			}

			if ( empty( $job['task_args']['trash_wp_rows_missing_from_gs'] ) ) {
				return;
			}
			if ( empty( $job['task_args']['existing_check_csv_field'] ) || empty( $job['task_args']['existing_check_csv_field'][0] ) ) {
				return;
			}
			if ( empty( $job['last_imported_identifiers_file_name'] ) ) {
				$job['last_imported_identifiers_file_name'] = 'row-ids-' . $job_id . '.txt';
				WPSE_Automations_Core::set_new_job_settings(
					$job_id,
					array(
						'last_imported_identifiers_file_name' => $job['last_imported_identifiers_file_name'],
					)
				);
			}
			$identifier_column     = $job['task_args']['existing_check_csv_field'][0];
			$identifiers_file_path = wp_normalize_path( WPSE_CSV_API_Obj()->long_lived_dir ) . $job['last_imported_identifiers_file_name'];
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' ' . var_export( compact( 'identifier_column', 'identifiers_file_path' ), true ), $job_id );
			}

			$new_rows    = array();
			$current_ids = $this->_get_row_ids_from_csv( $file_path, $identifier_column );
			if ( file_exists( $identifiers_file_path ) ) {
				$decrypted_ids = WPSE_Automations_Core::decrypt( file_get_contents( $identifiers_file_path ), wpsea_fs()->get_site()->secret_key );
				if ( ! empty( $decrypted_ids ) ) {
					$previous_ids                   = json_decode( $decrypted_ids, true );
					$removed_ids_from_external_file = array_diff( $previous_ids, $current_ids );
					$original_csv_data              = WPSE_CSV_API_Obj()->get_rows( $file_path, $job['task_args']['separator'], false, 1 );
					$sample_row                     = array_fill_keys( array_keys( $original_csv_data['rows'][0] ), '' );
					foreach ( $removed_ids_from_external_file as $removed_id ) {
						$new_rows[ $removed_id ] = array_merge(
							$sample_row,
							array(
								$identifier_column => $removed_id,
								'wpse_set_post_status'      => 'trash',
							)
						);
					}
					if ( ! empty( $job['verbose_log'] ) ) {
						WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' ' . var_export( compact( 'previous_ids', 'current_ids', 'removed_ids_from_external_file', 'new_rows' ), true ), $job_id );
					}
					if ( ! empty( $new_rows ) ) {
						WPSE_Logger_Obj()->entry( sprintf( __( 'This import has been configured to move the items to the trash in WordPress when they have been deleted in the external source (%1$s). We detected these items that were removed from your external source and will be moved to the trash in WordPress: %2$s.', 'vg_sheet_editor_automations' ), $this->source_label, implode( ', ', $removed_ids_from_external_file ) ), $job_id );
					}
				}
			}

			$encrypted_ids = WPSE_Automations_Core::encrypt( wp_json_encode( array_filter( array_unique( $current_ids ) ) ), wpsea_fs()->get_site()->secret_key );
			file_put_contents( $identifiers_file_path, $encrypted_ids );
			return apply_filters( 'vg_sheet_editor/automations/removed_rows_from_external_file/rows_to_save', $new_rows, $job );
		}
		public function prepare_csv_rows_for_new_scheduled_import( $file_path, $job, $runner ) {
			$job_id = $job['job_id'];

			if ( ! empty( $job['task_args']['auto_column_names'] ) ) {
				$added_column_names = WPSE_CSV_API_Obj()->maybe_add_auto_column_names_to_file( $file_path, $job['task_args']['separator'] );
			}

			WPSE_Automated_Imports_Obj()->_remove_unmapped_columns_from_csv( $job );

			// This function must be called BEFORE remove_unchanged_rows_from_csv_file(), otherwise the unchanged rows will be considered as deleted rows
			$trash_rows = apply_filters( 'vg_sheet_editor/automations/external_import/rows_to_append', $this->detect_removed_rows_from_external_file( $file_path, $job ), $file_path, $job, $runner, $this );

			// Recount the file rows for the batch progress messages
			$total_rows = WPSE_CSV_API_Obj()->count_rows_in_file( $file_path, $job['task_args']['separator'] );

			// Exclude all the rows that haven't changed compared to the previous import, only if we're allowed to and this is not an import that's only going to create items
			if ( ! VGSE()->get_option( 'automations_gs_import_all_rows_always' ) && $job['task_args']['writing_type'] !== 'all_new' && ! empty( $total_rows ) ) {
				$new_rows_count = $this->remove_unchanged_rows_from_csv_file( $file_path, $job, $total_rows );

				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: The data from the external source (%2$s) has been refreshed. We found %3$d row(s) initially, we excluded %4$d row(s) that haven\'t changed since the previous imports, and %5$d row(s) remaining will be imported.', 'vg_sheet_editor_automations' ), $runner->job_label, $this->source_label, $total_rows, $total_rows - $new_rows_count, $new_rows_count ), $job_id );

				// Recount the file rows for the batch progress messages
				$total_rows = $new_rows_count;

			} else {
				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: The data from the external source (%2$s) has been refreshed. We found %3$d rows in the external source.', 'vg_sheet_editor_automations' ), $runner->job_label, $this->source_label, (int) $total_rows ), $job_id );
			}
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' ' . var_export( compact( 'total_rows' ), true ), $job_id );
			}

			if ( ! empty( $trash_rows ) ) {
				WPSE_CSV_API_Obj()->_array_to_csv( array_values( $trash_rows ), $file_path );
				$total_rows += count( $trash_rows );
			}

			$job['task_args']['total_rows'] = $total_rows;
			WPSE_Automations_Core::update_task_args(
				array(
					'total_rows' => $total_rows,
				),
				$job
			);
			return $total_rows;
		}

		public function handle_empty_csv_for_scheduled_import( $job, $runner ) {
			$job_id = $job['job_id'];
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// Send the email only if it's not a real time import, because real time imports can trigger too many notifications
			if ( empty( $job['task_args']['real_time_import'] ) ) {
				$runner->_send_email_notification_after_job_completed( $job, array() );
			}
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// Schedule the next repetition
			$runner->_schedule_next_batch( $job, array( 'file_position' => 0 ), true );

			WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: The process has finished successfully early because the external source (%2$s) contains 0 rows that need to be imported.', 'vg_sheet_editor_automations' ), $runner->job_label, $this->source_label ), $job_id );
		}

		/**
		 * Download the external file into the local CSV before the import
		 *
		 * @param  array $job
		 * @param  WPSE_Automated_Job_REST_Base $runner
		 * @return array|false $job on success, false to stop the execution of the task
		 */
		public function refresh_csv_before_new_scheduled_import( $job, $runner ) {
			if ( ! $job || ! empty( $job['task_args']['file_position'] ) || $this->source_key !== $job['task_args']['source'] ) {
				return $job;
			}

			$job_id    = $job['job_id'];
			$base_dir  = wp_normalize_path( WPSE_CSV_API_Obj()->imports_dir );
			$file_path = $base_dir . $job['task_args']['import_file'];

			$result = $this->fetch_external_file_contents_for_import( $file_path, $job['task_args'][ $this->file_field_key ] );
			if ( is_wp_error( $result ) ) {
				$this->handle_error_retrieving_external_data( $result, $job_id, $runner );
				return false;
			}

			$total_rows = $this->prepare_csv_rows_for_new_scheduled_import( $file_path, $job, $runner );

			// If there are no rows to import, return false to stop the job execution, log it, and graciously mark the job as finished
			if ( $total_rows === 0 ) {
				$this->handle_empty_csv_for_scheduled_import( $job, $runner );
				return false;
			}

			return $job;
		}

		public static function render_what_happens_to_deleted_rows_field( $post_type ) {
			if ( ! VGSE()->helpers->get_current_provider()->is_post_type ) {
				return;
			}
			?>
			<div class="field-wrap trash-rows-missing-from-gs if-sync-bidirectional">
				<label><input value="yes" type="checkbox" name="trash_wp_rows_missing_from_gs"> <?php esc_html_e( 'Move items to the trash in WordPress when they are deleted in the external source?', 'vg_sheet_editor_automations' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php esc_html_e( 'By default, if a row has been removed in your external file, the item won\'t be deleted or moved to the trash in WordPress. We delete from WordPress only when you set the "Status" column with a value "delete" in the external file or move to the trash when you set "Status" with a value "trash". You can activate this option if you want to move to the trash in WordPress all the rows that were deleted in the external source file. We move them to the trash instead of fully deleting to give you the chance to restore if you make a mistake.', 'vg_sheet_editor_automations' ); ?>">( ? )</a></label>
				<?php do_action( 'vg_sheet_editor/automations/after_what_happens_when_deleted_rows', $post_type ); ?>
			</div>
			<?php
		}
	}
}
