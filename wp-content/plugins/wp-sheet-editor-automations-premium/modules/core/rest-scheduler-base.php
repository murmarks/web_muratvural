<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSE_Automated_Job_REST_Base' ) ) {

	class WPSE_Automated_Job_REST_Base extends WP_REST_Controller {
		public $job_route;
		public $job_route_method;
		public $job_label;
		public $job_key;
		public $scheduled_job_route;
		public $completed_label;
		public $download_file_response_key;
		public $file_name_response_key;

		public function __construct( $job_route, $job_route_method, $job_label, $completed_label, $download_file_response_key, $file_name_response_key = null ) {
			$this->job_route                  = $job_route;
			$this->job_route_method           = $job_route_method;
			$this->job_label                  = $job_label;
			$this->completed_label            = $completed_label;
			$this->job_key                    = basename( $this->job_route );
			$this->download_file_response_key = $download_file_response_key;
			$this->file_name_response_key     = $file_name_response_key;

			$base_dir                  = dirname( $this->job_route );
			$this->scheduled_job_route = str_replace( $base_dir . '/', $base_dir . '/schedule/', $this->job_route );
		}


		public function run_scheduled_task( $job_id, $rest_method = 'POST' ) {
			WPSE_Logger_Obj()->set_current_job_id( $job_id );
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}
			$job = WPSE_Automations_Core::get_job( $job_id );
			if ( ! $job ) {
				return $job;
			}
			if ( $job['category'] !== $this->job_key ) {
				if ( ! empty( $job['verbose_log'] ) ) {
					WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' - Exit because the runner is not related to the job ' . var_export( compact( 'job', 'this' ), true ), $job_id );
				}
				return;
			}
			WPSE_Automations_Core::$current_job = $job;
			if ( ! empty( $job['verbose_log'] ) ) {
				$log_data = array(
					'Running from'          => PHP_SAPI,
					'is_background_request' => WPSE_Automations_Core::is_background_request(),
					'$_SERVER'              => $_SERVER,
					'$_POST'                => $_POST,
					'$_GET'                 => $_GET,
					'referrer'              => wp_get_referer(),
				);
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' - ' . var_export( $log_data, true ), $job_id );
			}

			// Allow plugins to stop this task early
			if ( ! apply_filters( 'vg_sheet_editor/scheduled/' . $this->job_key . '/can_run_task_early_check', true, $job ) ) {
				return $job;
			}

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			WPSE_Automations_Core::change_job_status( $job_id, 'in-progress' );

			WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: Starting to process batch %2$d', 'vg_sheet_editor_automations' ), $this->job_label, (int) $job['task_args']['page'] ), $job_id );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			if ( isset( $job['task_args']['custom_enabled_columns'] ) ) {
				$_REQUEST['custom_enabled_columns'] = $job['task_args']['custom_enabled_columns'];
			}

			// This filter is required for the proper initialization of columns, otherwise some needed columns might be deactivated
			add_filter( 'vg_sheet_editor/is_editor_page', '__return_true' );

			$_REQUEST['wpse_job_id'] = $job_id;
			$namespace               = WPSE_REST_API::get_route_namespace();

			// This is safe for these reasons:
			// Only users who pass these checks can schedule background tasks: capability manage_options,
			// Has the capability required by the data source (post type, taxonomy, user, custom table),
			// And has a valid nonce.
			//
			// So the allowed user schedules a task and we save the task parameters in a custom db table
			// After the initial saving, our code makes it impossible to edit the task parameters and author
			// ever again using our UI or PHP code. All our update functions explicitly exclude those fields.
			// The only way to edit the author/task parameters would be if someone has direct db/php access.
			//
			// So we set the WPSE user to the task author id, but this is just for the internal WPSE checks,
			// without changing the global WP user, and we restore it after the REST request. So we're not
			// giving anyone extra access to other sections of wp or things related to other plugins. It's just for
			// internal WPSE functionality.
			//
			// If someone manages to change the task parameters using our UI, our controllers would save their
			// user ID too and run the task with their user and capabilities, therefore they
			// wouldn't be able to escalate permissions.
			// And if someone manages to change the author id for role escalation, it means they passed
			// the manage_options capability, post type/user/taxonomy capability, and nonce checks so they're
			// administrators and they already have access to make those tasks in the regular wp screens.
			WP_Sheet_Editor_Helpers::set_current_user( (int) $job['author'] );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// Allow plugins to modify the job parameters right before the task is executed, or return false to stop the task completely
			$job = apply_filters( 'vg_sheet_editor/scheduled/' . $this->job_key . '/request_params_for_task', $job, $this );
			if ( ! $job ) {
				WP_Sheet_Editor_Helpers::set_current_user( 0 );
				return;
			}
			// Refresh the job in case it was edited through the filters
			$job                                = WPSE_Automations_Core::get_job( $job_id );
			WPSE_Automations_Core::$current_job = $job;

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			do_action( 'vg_sheet_editor/scheduled/' . $this->job_key . '/before_task_execution', $job_id, $job, $this );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $job : ' . var_export( $job, true ), $job_id );
			}

			if ( ! empty( $job['verbose_log'] ) ) {
				$route = '/' . $namespace . $this->job_route;
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $job : ' . var_export( compact( 'rest_method', 'route' ), true ), $job_id );
			}

			// Initialize the WPSE core
			$_REQUEST['sheet_key'] = $job['sheet_key'];
			// Don't auto enable columns for new fields, only use the columns configured for the task
			if ( ! defined( 'WPSE_ONLY_EXPLICITLY_ENABLED_COLUMNS' ) ) {
				define( 'WPSE_ONLY_EXPLICITLY_ENABLED_COLUMNS', true );
			}
			vgse_init();

			$response = VGSE()->helpers->create_rest_request( $rest_method, '/' . $namespace . $this->job_route, $job['task_args'] );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry(
					__CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $response : ' . var_export(
						array(
							'status' => $response->get_status(),
							'body'   => $response->get_data(),
						),
						true
					),
					$job_id
				);
			}

			WP_Sheet_Editor_Helpers::set_current_user( 0 );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// Exit if the request failed
			if ( $response->get_status() !== 200 ) {
				$retry_minutes = (int) VGSE()->get_option( 'automations_failed_api_realtime_retry', 720 );
				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: Process failed. The request returned an error. We\'ll retry every %2$d minutes. %3$s.', 'vg_sheet_editor_automations' ), $this->job_label, $retry_minutes, var_export( $response->get_data(), true ) ), $job_id );

				WPSE_Automations_Core::set_new_job_settings(
					$job_id,
					array(
						'reschedule_in_x_seconds' => MINUTE_IN_SECONDS * $retry_minutes,
					)
				);

				return;
			}
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// Exit if the job finished
			$response_data = $response->get_data();

			do_action( 'vg_sheet_editor/scheduled/' . $this->job_key . '/task_response_data', $response_data, $job_id, $this );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			if ( isset( $response_data['rows'] ) ) {
				unset( $response_data['rows'] );
			}
			$processed_rows = null;
			if ( isset( $response_data['processed_rows'] ) ) {
				$processed_rows = $response_data['processed_rows'];
			} elseif ( isset( $job['task_args']['processed_rows'] ) ) {
				$processed_rows = $job['task_args']['processed_rows'];
			}
			$job['processed_rows'] = $processed_rows;
			$total                 = null;
			if ( isset( $response_data['total'] ) ) {
				$total = $response_data['total'];
			} elseif ( isset( $job['task_args']['total'] ) ) {
				$total = $job['task_args']['total'];
			}
			$job['total'] = $total;
			WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: Batch %2$d processed successfully. %3$d of %4$d rows have been processed so far.', 'vg_sheet_editor_automations' ), $this->job_label, (int) $job['task_args']['page'], $processed_rows, $total ), $job_id );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// If the API response was empty, but it didn't throw an error, assume that the job is finished
			if ( empty( $response_data ) || ! empty( $response_data['rows_not_found'] ) ) {
				$response_data = array(
					'force_complete' => true,
				);
			}

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			if ( ! empty( $response_data['force_complete'] ) || ( ! empty( $job['task_args']['max_pages'] ) && $job['task_args']['max_pages'] === $job['task_args']['page'] ) ) {

				if ( ! empty( $job['verbose_log'] ) ) {
					WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
				}

				WPSE_Automations_Core::increase_run_count( $job_id );
				$this->_send_email_notification_after_job_completed( $job, $response_data );
				// Schedule the next repetition
				$this->_schedule_next_batch( $job, $response_data, true );

				WPSE_Logger_Obj()->entry( sprintf( __( '%s: The process has finished successfully', 'vg_sheet_editor_automations' ), $this->job_label ), $job_id );

				if ( ! empty( $job['verbose_log'] ) ) {
					WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
				}

				return;
			}

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}

			// Schedule next batch
			$this->_schedule_next_batch( $job, $response_data );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__, $job_id );
			}
		}

		public function get_scheduled_jobs_for_modal() {
			global $wpdb;

			if ( empty( $_GET['post_type'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Missing parameters.', 'vg_sheet_editor_automations' ) ) );
			}
			if ( ! VGSE()->helpers->verify_nonce_from_request() || ! VGSE()->helpers->user_can_manage_options() ) {
				$message = array( 'message' => __( 'You dont have enough permissions to load rows.', 'vg_sheet_editor_automations' ) );
				wp_send_json_error( $message );
			}
			// This filter is required for the proper initialization of columns
			if ( ! empty( $_GET['is_editor_page'] ) ) {
				add_filter( 'vg_sheet_editor/is_editor_page', '__return_true' );
			}

			$post_type = VGSE()->helpers->sanitize_table_key( $_GET['post_type'] );

			$jobs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM ' . WPSE_Automations_Core::$table_name . ' WHERE category = %s AND sheet_key = %s ORDER BY id DESC',
					$this->job_key,
					$post_type
				),
				ARRAY_A
			);
			$rows = array();
			foreach ( $jobs as $job ) {
				$job = WPSE_Automations_Core::get_job( $job );
				if ( empty( $job['task_args'] ) ) {
					continue;
				}
				$task_args = $job['task_args'];
				$job_name  = $job['name'] ? $job['name'] : __( 'Untitled', 'vg_sheet_editor_automations' );

				$created      = $job['created'];
				$category     = $job['category'];
				$status       = $job['status'];
				$job_id       = $job['job_id'];
				$previous_run = $job['previous_run'];
				$next_run     = $job['date'] ? $job['date'] : '';
				$log_url      = WPSE_Logger_Obj()->get_log_download_url( $job['job_id'] );
				$recurring    = $job['repeat_every'] ? sprintf( esc_html__( 'Every %s', 'vg_sheet_editor_automations' ), WPSE_Automations_Core::get_formatted_repeat( $job['repeat_every'], $job['repeat_unit'] ) ) : 'No';

				$formatted_created  = get_date_from_gmt( $created );
				$formatted_prev_run = $previous_run ? get_date_from_gmt( $previous_run ) : '';
				$formatted_next_run = $next_run ? get_date_from_gmt( $next_run ) : '';
				$formatted_status   = WPSE_Automations_Core::get_formatted_status( $status );
				$editable_data      = array(
					'schedule_for_later_date'          => $job['date'] ? $job['date'] : $job['original_date'],
					'schedule_for_later_email_address' => $job['email_notification_address'],
					'schedule_is_recurring'            => $job['repeat_every'] ? 'yes' : 'no',
					'schedule_repeat_every'            => $job['repeat_every'],
					'schedule_repeat_unit'             => $job['repeat_unit'],
					'scheduled_job_name'               => $job['name'],
				);

				$rows[] = compact( 'job_name', 'formatted_created', 'formatted_status', 'status', 'formatted_prev_run', 'formatted_next_run', 'log_url', 'job_id', 'recurring', 'category', 'editable_data', 'task_args' );
			}
			return apply_filters( 'vg_sheet_editor/scheduled/' . $this->job_key . '/rows_for_table', $rows, $this );
		}

		public function _schedule_next_batch( $job, $response_data, $restarting = false ) {
			$new_params = array(
				'is_a_restart' => false,
				'date'         => null,
			);
			do_action( 'vg_sheet_editor/scheduled/' . $this->job_key . '/after_batch_processed', $job, $response_data, $this );
			if ( $restarting ) {
				if ( self::is_one_time_job( $job ) ) {
					WPSE_Automations_Core::mark_job_as_finished( $job['job_id'] );
					WPSE_Logger_Obj()->entry( sprintf( __( '%s: This is a one-time job. So we will not schedule another run.', 'vg_sheet_editor_automations' ), $this->job_label ), $job['job_id'] );

					do_action( 'vg_sheet_editor/scheduled/' . $this->job_key . '/after_one_time_job_finished', $job['job_id'], $job, $this );
					return;
				}
				$next_date = date( 'Y-m-d H:i:s', strtotime( $job['original_date'] . ' + ' . $job['repeat_every'] . ' ' . $job['repeat_unit'] ) );

				if ( $next_date < current_time( 'mysql', true ) ) {
					$next_date = date( 'Y-m-d H:i:s', strtotime( $job['date'] . ' + ' . $job['repeat_every'] . ' ' . $job['repeat_unit'] ) );
				}

				$new_params['is_a_restart'] = true;

				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: This is a recurring job and it is scheduled to start again on : %2$s', 'vg_sheet_editor_automations' ), $this->job_label, get_date_from_gmt( $next_date ) ), $job['job_id'] );
			} else {
				$seconds_between_batches    = ! empty( VGSE()->options['be_timeout_between_batches'] ) ? (int) VGSE()->options['be_timeout_between_batches'] : 6;
				$next_date                  = date( 'Y-m-d H:i:s', time() + $seconds_between_batches );
				$new_params['is_a_restart'] = false;

				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: The next batch will be scheduled on %2$s.', 'vg_sheet_editor_automations' ), $this->job_label, get_date_from_gmt( $next_date ) ), $job['job_id'] );
			}
			$new_params['date'] = $next_date;

			if ( isset( $response_data['file_position'] ) ) {
				$new_params['file_position'] = $response_data['file_position'];
			}
			$result = WPSE_Automations_Core::schedule_next_action( $this->job_key, $job['job_id'], $new_params );
		}
		public function _send_email_notification_after_job_completed( $job, $response_data ) {
			if ( empty( $job['email_notification_address'] ) ) {
				WPSE_Logger_Obj()->entry( sprintf( __( '%s: We will not notify any user because the email notification is not enabled in the scheduled job settings.', 'vg_sheet_editor_automations' ), $this->job_label ), $job['job_id'] );
				if ( ! empty( $job['verbose_log'] ) ) {
					WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $job : ' . var_export( $job, true ), $job['job_id'] );
				}
				return;
			}
			if ( ! apply_filters( 'vg_sheet_editor/scheduled/' . $this->job_key . '/can_send_email_notification', true, $job, $response_data ) ) {
				WPSE_Logger_Obj()->entry( sprintf( __( '%s: We will not notify any user because this job doesn\'t require an email notification.', 'vg_sheet_editor_automations' ), $this->job_label ), $job['job_id'] );
				if ( ! empty( $job['verbose_log'] ) ) {
					WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $job : ' . var_export( $job, true ), $job['job_id'] );
				}
				return;
			}

			$to              = array_map( 'sanitize_email', explode( ',', $job['email_notification_address'] ) );
			$subject_parts   = array( $this->completed_label );
			$subject_parts[] = VGSE()->helpers->get_post_type_label( $job['sheet_key'] );
			$subject_parts[] = $job['name'];

			$subject         = implode( ' - ', array_filter( $subject_parts ) );
			$job_name        = $job['name'];
			$sheet_name      = VGSE()->helpers->get_post_type_label( $job['sheet_key'] );
			$wp_url          = home_url();
			$completion_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
			$download_url    = null;
			$file_path       = null;
			if ( $this->file_name_response_key && ! empty( $response_data[ $this->file_name_response_key ] ) ) {
				$raw_file_path = wp_normalize_path( WPSE_CSV_API_Obj()->exports_dir . $response_data[ $this->file_name_response_key ] . '.csv' );

				if ( file_exists( $raw_file_path ) ) {
					$file_path = preg_replace( '/^.+\/wp-content\//', '/wp-content/', $raw_file_path );
				}
			}
			if ( $this->download_file_response_key && isset( $response_data[ $this->download_file_response_key ] ) ) {
				$download_url = $response_data[ $this->download_file_response_key ];
			}
			$sheet_key        = $job['sheet_key'];
			$total_rows       = empty( $job['total'] ) ? 0 : $job['total'];
			$log_url          = WPSE_Logger_Obj()->get_log_download_url( $job['job_id'] );
			$expiration_hours = WPSE_CSV_API_Obj()->file_expiration_hours();
			$repeat_every     = self::is_one_time_job( $job ) ? __( 'No', 'vg_sheet_editor_automations' ) : sprintf( esc_html__( 'It will run every: %s', 'vg_sheet_editor_automations' ), $job['repeat_every'] . ' ' . $job['repeat_unit'] );
			ob_start();
			include __DIR__ . '/views/job-completed-email.php';
			$content = ob_get_clean();

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: Variables prepared for the notification %2$s', 'vg_sheet_editor_automations' ), $this->job_label, var_export( compact( 'to', 'subject', 'job_name', 'sheet_name', 'wp_url', 'completion_date', 'download_url', 'sheet_key', 'file_path', 'log_url', 'content' ), true ) ), $job['job_id'] );
			}

			wp_mail( $to, $subject, $content, array( 'Content-Type: text/html; charset=UTF-8' ) );

			WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: Email notification sent to %2$s', 'vg_sheet_editor_automations' ), $this->job_label, $job['email_notification_address'] ), $job['job_id'] );
		}
		public function get_endpoint_args( $route, $method = 'GET' ) {
			$rest_server = rest_get_server();
			$namespace   = WPSE_REST_API::get_route_namespace();
			$endpoints   = $rest_server->get_routes( $namespace );
			$args        = false;
			if ( empty( $endpoints ) || empty( $endpoints[ '/' . $namespace . $route ] ) ) {
				return $args;
			}
			foreach ( $endpoints[ '/' . $namespace . $route ] as $sub_route ) {
				if ( ! empty( $sub_route['methods'][ $method ] ) ) {
					$args = $sub_route['args'];
				}
			}
			return $args;
		}
		public function register_hooks() {
		}
		/**
		 * Register the routes for the objects of the controller.
		 */
		public function register_routes() {
			if ( ! class_exists( 'WP_Sheet_Editor' ) ) {
				return;
			}
			$namespace = WPSE_REST_API::get_route_namespace();

			$job_args = $this->get_endpoint_args( $this->job_route, $this->job_route_method );
			unset( $job_args['wpse_job_id'] );
			unset( $job_args['sheet_key'] );

			register_rest_route(
				$namespace,
				$this->scheduled_job_route,
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'schedule_job' ),
						'permission_callback' => function () {
							return VGSE()->helpers->user_can_manage_options();
						},
						'args'                => array(
							'sheet_key'                  => array(
								'type'              => 'string',
								'sanitize_callback' => function ( $param ) {
									return VGSE()->helpers->sanitize_table_key( $param );
								},
								'validate_callback' => array( $GLOBALS['wpse_rest_api'], '_validate_string' ),
								'required'          => true,
							),
							'wpse_job_id'                => array(
								'sanitize_callback' => 'sanitize_text_field',
								'validate_callback' => array( $GLOBALS['wpse_rest_api'], '_validate_string' ),
								'required'          => true,
								'type'              => 'string',
							),
							'name'                       => array(
								'sanitize_callback' => 'sanitize_text_field',
								'validate_callback' => array( $GLOBALS['wpse_rest_api'], '_validate_string' ),
								'required'          => false,
								'type'              => 'string',
							),
							'date'                       => array(
								'type'     => 'string',
								'format'   => 'date-time',
								'required' => true,
							),
							'repeat_every'               => array(
								'type'     => 'integer',
								'required' => true,
								'minimum'  => 0,
							),
							'repeat_unit'                => array(
								'sanitize_callback' => 'sanitize_text_field',
								'type'              => 'string',
								'enum'              => array( 'minute', 'hour', 'day', 'week', 'month' ),
								'required'          => true,
							),
							'email_notification_address' => array(
								'sanitize_callback' => 'sanitize_text_field',
								'type'              => 'string',
								'required'          => true,
							),
							'verbose_log'                => array(
								'type'     => 'boolean',
								'required' => true,
							),
							'task_args'                  => array(
								'type'       => 'object',
								'required'   => true,
								'properties' => $job_args,
							),
						),
					),
				)
			);
		}
		public static function is_one_time_job( $job ) {
			return empty( $job['repeat_every'] ) || empty( $job['repeat_unit'] );
		}

		public function schedule_job( $request ) {
			$job = is_object( $request ) ? $request->get_params() : $request;
			$job = array_intersect_key(
				$job,
				array_flip(
					array(
						'sheet_key',
						'wpse_job_id',
						'name',
						'date',
						'repeat_every',
						'repeat_unit',
						'email_notification_address',
						'verbose_log',
						'task_args',
					)
				)
			);
			WPSE_Automations_Core::schedule_single_action( $this->job_key, $job );

			if ( self::is_one_time_job( $job ) ) {
				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: A one-time job has been scheduled and it will start on %2$s', 'vg_sheet_editor_automations' ), $this->job_label, get_date_from_gmt( $request['date'] ) ), $job['wpse_job_id'] );
			} else {
				WPSE_Logger_Obj()->entry( sprintf( __( '%1$s: A recurring job has been scheduled and it will start on %2$s and it will repeat every %3$d %4$s', 'vg_sheet_editor_automations' ), $this->job_label, get_date_from_gmt( $request['date'] ), $job['repeat_every'], $job['repeat_unit'] ), $job['wpse_job_id'] );
			}
			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $job : ' . var_export( $job, true ), $job['wpse_job_id'] );
			}
			$response = array(
				// If the message is null, it will show a default message on the JS side, this parameter is used if we want to set a custom success message through the filter below
				'message' => null,
				'success' => true,
			);
			return apply_filters( 'vg_sheet_editor/scheduled/' . $this->job_key . '/after_job_scheduled_response', $response, $job, $this );
		}
	}
}
