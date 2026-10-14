<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSE_Automations_REST' ) ) {

	class WPSE_Automations_REST extends WP_REST_Controller {
		public function register_hooks() {

		}
		/**
		 * Allow to access the REST endpoints if the user has capability manage_options
		 * and the request includes a job_id and the user has the capability required
		 * to edit the post type/taxonomy/user/custom table related to the job id
		 *
		 * @param  object $request
		 * @return boolean
		 */
		public function manage_actions_permissions_check( $request ) {
			return VGSE()->helpers->user_can_manage_options() && ! empty( $request['job_id'] ) && WPSE_Automations_Core::user_can_edit_job_sheet( $request['job_id'] );
		}

		public function retry_failed_batch( $request ) {
			$job_id = $request['job_id'];
			$retry_result = WPSE_Automations_Core::retry_failed_batch( $job_id );

			WPSE_Logger_Obj()->entry( __( 'The user requested to retry the last batch that failed or was stuck processing for more than 30 minutes. So we\'ll schedule another run of the same batch to start in 1 minute.', 'vg_sheet_editor_automations' ), $job_id );

			$out = array(
				'message' => __( 'The job has been scheduled.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}

		public function run_again( $request ) {
			$job_id = $request['job_id'];
			$job    = WPSE_Automations_Core::get_job( $job_id );

			if ( ! empty( $job['repeat_every'] ) ) {
				return new WP_Error( 'wpse', array( 'message' => __( 'Only one-time jobs can be executed again. This is a recurring job.', 'vg_sheet_editor' ) ), array( 'status' => 400 ) );
			}
			if ( $job['status'] !== 'completed' ) {
				return new WP_Error( 'wpse', array( 'message' => __( 'Only jobs that already completed a run can run again.', 'vg_sheet_editor' ) ), array( 'status' => 400 ) );
			}

			WPSE_Logger_Obj()->entry( __( 'The user requested to run this one-time job one more time. So we\'ll schedule another run to start in 1 minute.', 'vg_sheet_editor_automations' ), $job['job_id'] );

			$new_params = array(
				'is_a_restart' => true,
				'date'         => date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql', true ) . ' +1 minute' ) ),
			);
			$result     = WPSE_Automations_Core::schedule_next_action( $job['category'], $job['job_id'], $new_params );

			$out = array(
				'message' => __( 'The job has been scheduled.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}

		public function run_action( $request ) {
			$job_id = $request['job_id'];
			$action = WPSE_Automations_Core::get_next_action( $job_id );

			if ( $action ) {
				WPSE_Automations_Core::change_job_status( $job_id, 'in-progress' );

				WPSE_Logger_Obj()->entry( __( 'Scheduled job: Running ahead of schedule because the user clicked on the button "Execute next run".', 'vg_sheet_editor_automations' ), $job_id );

				ActionScheduler_QueueRunner::instance()->process_action( $action['action_id'], 'WP Sheet Editor - Scheduled Jobs Table' );

				$out = array(
					'message' => __( 'The batch will run in the background for the next few seconds. You can review the log file in a couple of minutes to see what\'s changed', 'vg_sheet_editor_automations' ),
				);
			} else {

				$out = array(
					'message' => __( 'The job is completed.', 'vg_sheet_editor_automations' ),
				);
			}
			return $out;
		}
		public function cancel_action( $request ) {
			$job_id = $request['job_id'];
			WPSE_Automations_Core::mark_job_cancelled( $job_id );

			$user = get_userdata( get_current_user_id() );

			WPSE_Logger_Obj()->entry( sprintf( esc_html__( 'Scheduled job: The process was cancelled by the user %s manually. The next scheduled batch won\'t be executed.', 'vg_sheet_editor_automations' ), $user->user_email ), $job_id );

			$out = array(
				'message' => __( 'The scheduled job has been cancelled.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}
		public function restore_job( $request ) {
			$job_id = $request['job_id'];
			WPSE_Automations_Core::restore_job( $job_id );

			$user = get_userdata( get_current_user_id() );

			WPSE_Logger_Obj()->entry( sprintf( esc_html__( 'The job was restored by the user %s manually.', 'vg_sheet_editor_automations' ), $user->user_email ), $job_id );

			$out = array(
				'message' => __( 'The job has been restored.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}
		public function archive_job( $request ) {
			$job_id = $request['job_id'];
			WPSE_Automations_Core::archive_job( $job_id );

			$user = get_userdata( get_current_user_id() );

			WPSE_Logger_Obj()->entry( sprintf( esc_html__( 'The job was archived by the user %s manually.', 'vg_sheet_editor_automations' ), $user->user_email ), $job_id );

			$out = array(
				'message' => __( 'The job has been restored.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}
		public function resume_job( $request ) {
			$job_id = $request['job_id'];
			$job    = WPSE_Automations_Core::get_job( $job_id );

			$user = get_userdata( get_current_user_id() );

			WPSE_Automations_Core::resume_job(
				$job_id,
				sprintf( esc_html__( 'Manual action by the user %s' ), $user->user_email ),
				array(
					'gs_auto_resume_when_connected' => false,
				)
			);

			$out = array(
				'message' => __( 'The job has been resumed.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}
		public function edit_job( $request ) {
			// Only get the request parameters found in the list of allowed parameters
			$params       = array_intersect_key(
				$request->get_params(),
				array_flip(
					array(
						'name',
						'repeat_every',
						'repeat_unit',
						'email_notification_address',
						'date',
					)
				)
			);
			$date_changed = WPSE_Automations_Core::update_job( $request['job_id'], $params );

			if ( $date_changed ) {
				WPSE_Logger_Obj()->entry( sprintf( __( 'The job date has been edited by the same user who created the job originally, so the job has been stopped and it will start again on the new date: %s', 'vg_sheet_editor_automations' ), get_date_from_gmt( $params['date'] ) ), $request['job_id'] );
			}

			WPSE_Logger_Obj()->entry( sprintf( __( 'The job has been edited by the same user who created the job originally. Modified parameter(s): %s', 'vg_sheet_editor_automations' ), implode( ', ', array_keys( $params ) ) ), $request['job_id'] );

			$job = WPSE_Automations_Core::get_job( $request['job_id'] );
			do_action( 'vg_sheet_editor/automations/after_job_edited', $params, $date_changed, $request );
			do_action( 'vg_sheet_editor/automations/after_job_edited/' . $job['category'], $params, $date_changed, $request );

			$out = array(
				'message' => __( 'The job has been updated.', 'vg_sheet_editor_automations' ),
			);
			return $out;

		}
		public function pause_job( $request ) {
			$job_id = $request['job_id'];
			$user   = get_userdata( get_current_user_id() );
			WPSE_Automations_Core::pause_job( $job_id, sprintf( esc_html__( 'Manual action by the user %s' ), $user->user_email ) );

			$out = array(
				'message' => __( 'The scheduled job has been deleted.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}
		public function get_log_content( $request ) {
			$job_id    = $request['job_id'];
			$file_path = WPSE_Logger_Obj()->get_job_file( $job_id );
			$lines     = array();

			if ( file_exists( $file_path ) ) {
				$max_lines = VGSE()->get_option( 'automations_verbose_logs' ) ? 800 : 150;
				$fp        = fopen( $file_path, 'r' );
				while ( ! feof( $fp ) ) {
					$line = fgets( $fp, 4096 );
					array_push( $lines, $line ); 
					if ( count( $lines ) > $max_lines ) {
						array_shift( $lines );
					}
				}
				fclose( $fp );
			}

			$lines = array_filter( array_map( 'trim', $lines ) );
			$out   = array(
				'lines'   => empty( $lines ) ? __( 'The log is empty.', 'vg_sheet_editor_automations' ) : implode( PHP_EOL, $lines ),
				'log_url' => WPSE_Logger_Obj()->get_log_download_url( $job_id ),
			);
			return $out;
		}
		public function delete_job( $request ) {
			$job_id = $request['job_id'];
			WPSE_Automations_Core::delete_job( $job_id );

			$out = array(
				'message' => __( 'The scheduled job has been deleted.', 'vg_sheet_editor_automations' ),
			);
			return $out;
		}

		/**
		 * Register the routes for the objects of the controller.
		 */
		public function register_routes() {
			$namespace = WPSE_REST_API::get_route_namespace();
			register_rest_route(
				$namespace,
				'/sheet/schedule/run-next-batch',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'run_action' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/run-again',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'run_again' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/retry-failed-batch',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'retry_failed_batch' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/cancel-next-batch',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'cancel_action' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/restore-job',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'restore_job' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/archive-job',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'archive_job' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/delete-job',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'delete_job' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/pause-job',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'pause_job' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/resume-job',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'resume_job' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/log',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_log_content' ),
						'permission_callback' => array( $this, 'manage_actions_permissions_check' ),
						'args'                => array(
							'job_id' => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
							),
						),
					),
				)
			);
			register_rest_route(
				$namespace,
				'/sheet/schedule/edit-job',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'edit_job' ),
						'permission_callback' => function( $request ) {
							return WPSE_Automations_Core::user_can_edit_its_own_job( $request['job_id'] );
						},
						'args'                => array(
							'job_id'                     => array(
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
								'required'          => true,
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
								'required' => false,
							),
							'repeat_every'               => array(
								'type'     => 'integer',
								'required' => false,
								'minimum'  => 0,
							),
							'repeat_unit'                => array(
								'sanitize_callback' => 'sanitize_text_field',
								'type'              => 'string',
								'enum'              => array( 'minute', 'hour', 'day', 'week', 'month' ),
								'required'          => false,
							),
							'email_notification_address' => array(
								'sanitize_callback' => 'sanitize_text_field',
								'type'              => 'string',
								'required'          => false,
							),
						),
					),
				)
			);

		}
	}
}
if ( ! function_exists( 'wpse_automations_rest_init' ) ) {
	//Load this only for REST API requests
	add_action( 'rest_api_init', 'wpse_automations_rest_init', 11 );

	function wpse_automations_rest_init() {
		if ( ! class_exists( 'WP_Sheet_Editor') ) {
			return;
		}
		$GLOBALS['wpse_automations_rest'] = new WPSE_Automations_REST();
		$GLOBALS['wpse_automations_rest']->register_hooks();
		$GLOBALS['wpse_automations_rest']->register_routes();

		return $GLOBALS['wpse_automations_rest'];
	}
}
