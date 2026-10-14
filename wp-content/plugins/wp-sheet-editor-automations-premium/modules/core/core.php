<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSE_Automations_Core' ) ) {

	class WPSE_Automations_Core {

		private static $instance   = null;
		public static $table_name  = null;
		public static $current_job = null;

		private function __construct() {
		}

		public function init() {
			global $wpdb;
			self::$table_name = $wpdb->prefix . 'wpse_scheduled_tasks';
			add_action( 'action_scheduler_failed_action', array( __CLASS__, 'scheduled_action_timedout' ), 10, 2 );
			add_action( 'action_scheduler_failed_execution', array( __CLASS__, 'scheduled_action_failed' ), 10, 3 );
			add_action( 'action_scheduler_failed_validation', array( __CLASS__, 'scheduled_action_failed' ), 10, 3 );
			add_action( 'vg_sheet_editor/after_enqueue_assets', array( $this, 'register_assets' ) );
			add_action( 'action_scheduler_after_execute', array( $this, 'maybe_reschedule_batch' ), 10, 2 );
			add_filter( 'vg_sheet_editor/options_page/options', array( $this, 'add_settings_page_options' ) );
			add_filter( 'vg_sheet_editor/js_data', array( $this, 'add_js_settings' ), 10, 2 );
		}
		/**
		* Retry failed batch by rescheduling the stuck action.
		*
		* This function is responsible for retrying a failed batch job by finding the stuck
		* action associated with it and deleting it. Then, it schedules a new action for the
		* same job using the ActionScheduler plugin. The function also updates the scheduled task's
		* status in the database to 'scheduled'.
		*
		* @param string $job_id The ID of the batch job to retry.
		*/
		public static function retry_failed_batch( $job_id ) {
			global $wpdb;
			$job = WPSE_Automations_Core::get_job( $job_id );

			$stuck_action = WPSE_Automations_Core::get_stuck_batch( $job );
			if ( $stuck_action ) {
				// Delete the action because we'll reschedule when the job is resumed.
				ActionScheduler_Store::instance()->delete_action( $stuck_action->action_id );
			}

			$utc_date   = current_time( 'mysql', true );
			$group_slug = 'sheet-editor-' . $job['category'] . '-' . $job_id;
			// Cancel all pending actions related to the same group because we can only have one action scheduled per job
			ActionScheduler_Store::instance()->cancel_actions_by_group( $group_slug );
			$result = as_schedule_single_action( strtotime( $utc_date ), 'vg_sheet_editor/scheduled/' . $job['category'], array( $job_id ), $group_slug );

			$wpdb->update(
				$wpdb->prefix . 'wpse_scheduled_tasks',
				array(
					'status'  => 'scheduled',
					'date'    => $utc_date,
					'updated' => $utc_date,
				),
				array(
					'job_id' => $job_id,
				),
				array(
					'%s',
					'%s',
					'%s',
				),
				array( '%s' )
			);
		}
		/**
		 * Check if the job has a batch that's been in progress for more than 30 minutes and return the stuck scheduled action.
		 *
		 * @param  array $job
		 * @return bool|object
		 */
		public static function get_stuck_batch( $job, $minimum_minutes_late = 15 ) {
			global $wpdb;
			$job_id = $job['job_id'];
			if ( $job['status'] !== 'in-progress' ) {
				return false;
			}
			$stuck_action = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}actionscheduler_actions a
			LEFT JOIN {$wpdb->prefix}actionscheduler_groups g 
			ON a.group_id = g.group_id 
			WHERE g.slug LIKE %s AND a.status = 'in-progress' AND a.scheduled_date_gmt < %s LIMIT 1",
					'%' . $wpdb->esc_like( $job_id ) . '%',
					date( 'Y-m-d H:i:s', strtotime( '-' . $minimum_minutes_late . ' minutes', time() ) )
				)
			);
			if ( ! $stuck_action ) {
				return false;
			}
			return $stuck_action;
		}

		function add_js_settings( $data, $post_type ) {
			$data['automations_import_batch_size']     = VGSE()->get_option( 'automations_import_batch_size', VGSE()->get_option( 'be_posts_per_page_save', 10 ) );
			$data['automations_export_batch_size']     = VGSE()->get_option( 'automations_export_batch_size', VGSE()->get_option( 'export_page_size', 100 ) );
			$data['automations_bulk_edit_batch_size']  = VGSE()->get_option( 'automations_bulk_edit_batch_size', VGSE()->get_option( 'be_posts_per_page_save', 10 ) );
			$data['automations_save_cells_batch_size'] = VGSE()->get_option( 'automations_save_cells_batch_size', VGSE()->get_option( 'be_posts_per_page_save', 8 ) );
			$data['automations_verbose_logs']          = (bool) VGSE()->get_option( 'automations_verbose_logs', VGSE_DEBUG );
			$data['automations_log_reload_time']       = (int) VGSE()->get_option( 'automations_log_reload_time', 30 );

			$data['texts'] = array_merge(
				$data['texts'],
				array(
					'export_scheduled_in_popup'            => __( '<p>The export has been scheduled. You can close this popup. <a data-wpse-remodal-target="vgse_scheduled_exports_modal">View scheduled exports</a>.</p>', 'vg_sheet_editor_automations' ),
					'import_scheduled_in_popup'            => __( '<p>The import has been scheduled. You can close this popup. <a data-wpse-remodal-target="vgse_scheduled_imports_modal">View scheduled imports</a>.</p>', 'vg_sheet_editor_automations' ),
					'save_rows_scheduled_in_popup'         => __( '<p>The changes have been scheduled. You can close this popup. <a data-wpse-remodal-target="vgse_scheduled_save_rows_modal">View scheduled changes</a>.</p>', 'vg_sheet_editor_automations' ),
					'ai_add_new_scheduled_in_popup'         => __( '<p>The creation of rows has been scheduled. You can close this popup. <a data-wpse-remodal-target="vgse_scheduled_ai_add_new_modal">View scheduled changes</a>.</p>', 'vg_sheet_editor_automations' ),
					'bulk_edit_scheduled_in_popup'         => __( '<p>The bulk edit has been scheduled. You can close this popup. <a data-wpse-remodal-target="vgse_scheduled_bulk_edits_modal">View scheduled bulk edits</a>.</p>', 'vg_sheet_editor_automations' ),
					'scheduled_changes_popup_modified_cells' => __( 'Modified cells', 'vg_sheet_editor_automations' ),
					'scheduled_export_edited_successfully' => __( 'The export has been edited successfully', 'vg_sheet_editor_automations' ),
					'scheduled_bulk_edit_edited_successfully' => __( 'The bulk edit has been edited successfully', 'vg_sheet_editor_automations' ),
					'scheduled_import_edited_successfully' => __( 'The import has been edited successfully', 'vg_sheet_editor_automations' ),
					'scheduled_ai_add_new_edited_successfully' => __( 'The scheduled rows have been edited successfully', 'vg_sheet_editor_automations' ),
					'confirm_delete_scheduled_job'         => __( 'Are you sure you want to delete this scheduled job and all its related files?', 'vg_sheet_editor_automations' ),
				)
			);
			return $data;
		}
		/**
		 * Add fields to options page
		 * @param array $sections
		 * @return array
		 */
		function add_settings_page_options( $sections ) {
			$sections['automations'] = array(
				'icon'   => 'el-icon-cogs',
				'title'  => __( 'Automations', 'vg_sheet_editor_automations' ),
				'fields' => array(
					array(
						'id'       => 'automations_import_batch_size',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Imports: How many rows to process per batch?', 'vg_sheet_editor_automations' ),
						'desc'     => __( 'If you leave this empty, we\'ll use the values defined in the "speed and performance" tab. Use this option if you want to have different settings for the automation/background jobs vs the manual operations performed in the spreadsheet interface.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'       => 'automations_export_batch_size',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Exports: How many rows to process per batch?', 'vg_sheet_editor_automations' ),
						'desc'     => __( 'If you leave this empty, we\'ll use the values defined in the "speed and performance" tab. Use this option if you want to have different settings for the automation/background jobs vs the manual operations performed in the spreadsheet interface.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'       => 'automations_bulk_edit_batch_size',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Bulk edits: How many rows to process per batch?', 'vg_sheet_editor_automations' ),
						'desc'     => __( 'If you leave this empty, we\'ll use the values defined in the "speed and performance" tab. Use this option if you want to have different settings for the automation/background jobs vs the manual operations performed in the spreadsheet interface.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'       => 'automations_save_cells_batch_size',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Save cells: How many rows to process per batch?', 'vg_sheet_editor_automations' ),
						'desc'     => __( 'If you leave this empty, we\'ll use the values defined in the "speed and performance" tab. Use this option if you want to have different settings for the automation/background jobs vs the manual operations performed in the spreadsheet interface.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'    => 'automations_verbose_logs',
						'type'  => 'switch',
						'title' => __( 'Enable the debugging mode for the log?', 'vg_sheet_editor_automations' ),
						'desc'  => __( 'By default, we create a log file containing information in a friendly format, so anyone can read it. If you activate this option, we\'ll add more information that can be used by developers or WP Sheet Editor\'s support team to troubleshoot issues.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'       => 'automations_log_reload_time',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'How often should we update the log in the scheduled jobs table?', 'vg_sheet_editor_automations' ),
						'desc'     => __( 'If you leave this empty, we\'ll update the log every 30 seconds.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'    => 'automations_gs_import_all_rows_always',
						'type'  => 'switch',
						'title' => __( 'Should we import all the rows from external sources always?', 'vg_sheet_editor_automations' ),
						'desc'  => __( 'By default, when you configure an ongoing import from Google Sheets or from a FTP server, we import all the rows from the external location the first time, but the next imports will only include the modified rows which provides multiple benefits: the imports will finish much faster and you will use fewer server resources on your WordPress site. But you can enable this option if you want to process all the rows always, maybe because you\'re going to add duplicate rows in Google Sheets/FTP and you don\'t want them to be excluded.', 'vg_sheet_editor_automations' ),
					),
					array(
						'id'       => 'automations_import_auto_retry_delay',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Imports: How long can an import batch run before we consider it as stuck?', 'vg_sheet_editor_automations' ),
						'desc'     => __( 'If you leave this empty, we\'ll consider the batch that\'s been in progress for more than 15 minutes as "stuck" and consider it eligible for a retry if it\'s possible to retry. But if your server is configured to allow batches to run for longer, the batch might be running correctly during our check. You should enter a number higher than the max execution time set in the PHP settings. Enter 0 to disable the retries.', 'vg_sheet_editor_automations' ),
						'default'  => 15,
					),
				),
			);
			return $sections;
		}



		public function maybe_reschedule_batch( $action_id, $action ) {
			global $wpdb;
			$args = $action->get_args();
			$hook = $action->get_hook();

			if ( ! $hook || strpos( $hook, 'vg_sheet_editor' ) !== 0 ) {
				return;
			}

			if ( ! is_array( $args ) || ! isset( $args[0] ) || ! is_string( $args[0] ) ) {
				return;
			}
			$job = self::get_job( $args[0] );

			if ( ! $job || empty( $job['reschedule_in_x_seconds'] ) ) {
				return;
			}

			$scheduled_date = $action->get_schedule()->get_date();
			$due_date       = (int) $scheduled_date->format( 'U' ) + $job['reschedule_in_x_seconds'];

			// Cancel all pending actions related to the same group because we can only have one action scheduled per job+content ids
			ActionScheduler_Store::instance()->cancel_actions_by_group( $action->get_group() );
			$result = as_schedule_single_action(
				$due_date,
				$action->get_hook(),
				$action->get_args(),
				$action->get_group()
			);

			$wpdb->update(
				self::$table_name,
				array(
					'date' => date( 'Y-m-d H:i:s', $due_date ),
				),
				array(
					'job_id' => $job['job_id'],
				)
			);
			self::set_new_job_settings(
				$job['job_id'],
				array(
					'reschedule_in_x_seconds' => null,
				)
			);
		}

		function render_form_fields( $post_type, $hook, $render_name_field = false, $hide_activation_checkbox = false ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}
			?>
				<div class="field-wrap schedule-for-later-container 
				<?php
				if ( $hide_activation_checkbox ) {
					echo 'visible-internal-fields';
				}
				echo ' ' . sanitize_html_class( $hook );
				?>
				">
						<label class="
						<?php
						if ( $hide_activation_checkbox ) {
							echo 'hidden';}
						?>
						"><input type="checkbox" name="schedule_for_later" <?php checked( $hide_activation_checkbox ); ?>> <?php esc_html_e( 'Run this in the future?', 'vg_sheet_editor_automations' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php esc_html_e( 'We will save the configuration, run it in the date and time that you select, and send you an email when the scheduled process finishes.', 'vg_sheet_editor_automations' ); ?>">( ? )</a></label>
						<div class="fields-scheduler">
						<div class="field-wrap">
							<label><?php esc_html_e( 'Select the date and time', 'vg_sheet_editor_automations' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php esc_html_e( 'We\'ll schedule the process to start in the select date and time. Please ensure that your WP Cron runs every minute to improve the accuracy of the schedules. Ask your hosting provider if you don\'t know how', 'vg_sheet_editor_automations' ); ?>">( ? )</a></label>
							<input type="datetime-local" name="schedule_for_later_local_date" step="1">
							<input type="hidden" name="schedule_for_later_date">
						</div>
						<div class="field-wrap">
							<label><?php esc_html_e( 'Notify this email address when the process finishes', 'vg_sheet_editor_automations' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php esc_html_e( 'Enter multiple addresses separated with commas. We\'ll send an email notification to those addreses with the results. The email will be sent from your WordPress site. If you don\'t receive the email, please configure a SMTP plugin on your site to improve the email deliverability. The email deliverability depends on your hosting provider or SMTP server that you\'re using', 'vg_sheet_editor_automations' ); ?>">( ? )</a></label>
							<input type="text" name="schedule_for_later_email_address" value="<?php
							$user = get_userdata( get_current_user_id() );
							echo esc_attr( $user->user_email );
							?>">
						</div>
						<div class="field-wrap schedule-recurring">							
							<label><input type="radio" name="schedule_is_recurring" value="no"> <?php esc_html_e( 'Run once', 'vg_sheet_editor_automations' ); ?></label>						
							<label><input type="radio" name="schedule_is_recurring" value="yes"> <?php esc_html_e( 'Run in recurring intervals', 'vg_sheet_editor_automations' ); ?></label>
							<?php do_action( 'vg_sheet_editor/automations/' . $hook . '/after_recurring_options_fields', $post_type, $render_name_field, $hide_activation_checkbox ); ?>
						</div>
						<div class="if-recurring-no">
							<?php do_action( 'vg_sheet_editor/automations/' . $hook . '/after_one_time_job_fields', $post_type, $render_name_field, $hide_activation_checkbox ); ?>
						</div>
						<div class="if-recurring-yes">
							<div class="field-wrap schedule-recurring-interval">							
								<label><?php esc_html_e( 'Run every', 'vg_sheet_editor_automations' ); ?> 
								<input type="number" name="schedule_repeat_every" placeholder="<?php echo esc_attr_e( 'Enter a number', 'vg_sheet_editor_automations' ); ?>"> 
								<select name="schedule_repeat_unit">
									<option value="minute"><?php esc_html_e( 'Minute(s)', 'vg_sheet_editor_automations' ); ?></option>
									<option value="hour"><?php esc_html_e( 'Hour(s)', 'vg_sheet_editor_automations' ); ?></option>
									<option value="day"><?php esc_html_e( 'Days(s)', 'vg_sheet_editor_automations' ); ?></option>
									<option value="week"><?php esc_html_e( 'Weeks(s)', 'vg_sheet_editor_automations' ); ?></option>
									<option value="month"><?php esc_html_e( 'Months(s)', 'vg_sheet_editor_automations' ); ?></option>
								</select></label>
							</div>
							<?php do_action( 'vg_sheet_editor/automations/' . $hook . '/after_recurring_job_fields', $post_type, $render_name_field, $hide_activation_checkbox ); ?>
						</div>
						<?php if ( $render_name_field ) { ?>
						<div class="field-wrap">
							<label><?php esc_html_e( 'Name', 'vg_sheet_editor_automations' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php esc_html_e( 'This will help you identify the scheduled jobs later', 'vg_sheet_editor_automations' ); ?>">( ? )</a></label>
							<input type="text" name="scheduled_job_name" value="">
						</div>
							<?php } ?>
							<?php do_action( 'vg_sheet_editor/automations/' . $hook . '/after_schedule_fields', $post_type, $render_name_field, $hide_activation_checkbox ); ?>
							</div>
					</div>
					<?php
		}

		/**
		 * Register frontend assets
		 */
		function register_assets() {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}
			wp_enqueue_style( 'wpse-automations-styles', plugins_url( '/assets/styles.css', __FILE__ ), array(), VGSE()->version );
			wp_enqueue_script( 'wpse-automations-exports-script', plugins_url( '/assets/init.js', __FILE__ ), array( 'jquery' ), VGSE()->version, false );
			wp_localize_script(
				'wpse-automations-exports-script',
				'wpse_automations_data',
				array(
					'extra_settings_fields' => WPSE_Automations_Init_Obj()->extra_fields_for_sync_settings,
				)
			);
		}


		public static function action_id_to_job_id( $action_id ) {
			global $wpdb;
			$action_data = $wpdb->get_var( $wpdb->prepare( "SELECT args FROM {$wpdb->prefix}actionscheduler_actions WHERE action_id = %d AND hook LIKE '%vg_sheet_editor%'", $action_id ) );

			if ( ! $action_data ) {
				return false;
			}

			$args = json_decode( $action_data );
			if ( ! is_array( $args ) ) {
				return false;
			}
			$job_id = current( $args );

			if ( ! $job_id ) {
				return false;
			}
			return $job_id;
		}

		public static function update_task_args( $new_task_args, $job ) {
			global $wpdb;
			if ( ! $job ) {
				return;
			}
			$job['task_args'] = wp_parse_args( $new_task_args, $job['task_args'] );
			$wpdb->update(
				$wpdb->prefix . 'wpse_scheduled_tasks',
				array(
					'task_args' => wp_json_encode( $job['task_args'] ),
				),
				array(
					'job_id'   => $job['job_id'],
					'category' => $job['category'],
				)
			);
		}

		public static function decrypt( $encrypted_text, $key ) {
			$data      = base64_decode( $encrypted_text );
			$iv        = substr( $data, 0, openssl_cipher_iv_length( 'aes-256-cbc' ) );
			$decrypted = openssl_decrypt( substr( $data, openssl_cipher_iv_length( 'aes-256-cbc' ) ), 'aes-256-cbc', $key, 0, $iv );
			return $decrypted;
		}

		public static function encrypt( $text, $key ) {
			$iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
			$encrypted = openssl_encrypt( $text, 'aes-256-cbc', $key, 0, $iv );
			return base64_encode( $iv . $encrypted );
		}

		/**
		 * Get FTP servers registered in the wp-config.php file
		 * $server_sample = array(
		 *  'name' => 'My supplier 1', // this must never change
		 *  'id' => 'my-supplier-1', // auto generated based on the name
		 *  'hostname' => 'site.com', // domain name or server ip
		 *  'port' => 22, // int
		 *  'username' => 'asdf',
		 *  'password' => 'asdf',
		 *  'secure' => false,
		 *);
		 *
		 * @return array
		 */
		public static function get_ftp_servers() {
			$servers = defined( 'WPSE_AUTOMATIONS_FTP_SERVERS' ) && is_array( WPSE_AUTOMATIONS_FTP_SERVERS ) ? WPSE_AUTOMATIONS_FTP_SERVERS : array();
			foreach ( $servers as $index => $server ) {
				$servers[ $index ]['id'] = sanitize_title( $server['name'] );
			}
			return $servers;
		}

		public static function get_ftp_server( $key ) {
			$servers = self::get_ftp_servers();
			$server  = wp_list_filter( $servers, array( 'id' => $key ) );
			return ! empty( $server ) ? current( $server ) : false;
		}
		public static function get_ftp_file( $user, $pass, $host, $port, $secure, $file_path, $allowed_file_extension = 'csv' ) {
			$out = null;
			if ( empty( $file_path ) || empty( $host ) || empty( $user ) ) {
				return $out;
			}

			// Only allow specific type of files
			if ( ! str_ends_with( strtolower( $file_path ), '.' . $allowed_file_extension ) ) {
				return $out;
			}

			if ( $secure ) {
				$conn_id = ftp_ssl_connect( $host, $port ? $port : 21, 5 );
			} else {
				$conn_id = ftp_connect( $host, $port ? $port : 21, 5 );
			}
			if ( ! $conn_id ) {
				return new WP_Error( 'wpse', __( 'Unable to connect to FTP server', 'vg_sheet_editor_automations' ) );
			}
			$login_status = ftp_login( $conn_id, $user, $pass );
			if ( ! $login_status ) {
				return new WP_Error( 'wpse', __( 'Unable to log in to FTP server', 'vg_sheet_editor_automations' ) );
			}
			if ( ! function_exists( 'wp_tempnam' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$url_filename  = basename( parse_url( $file_path, PHP_URL_PATH ) );
			$tmp_file_path = wp_tempnam( $url_filename );
			$handle        = fopen( $tmp_file_path, 'w' );
			$mode          = ftp_pasv( $conn_id, true );
			ftp_fget( $conn_id, $handle, $file_path, FTP_BINARY, 0 );
			ftp_close( $conn_id );

			return file_get_contents( $tmp_file_path );
		}
		public static function scheduled_action_failed( $action_id, Exception $exception, $context = '' ) {
			$job_id = self::action_id_to_job_id( $action_id );
			if ( ! $job_id ) {
				return;
			}
			if ( ! empty( $context ) ) {
				/* translators: 1: context 2: exception message */
				$message = sprintf( __( 'Process failed via %1$s: %2$s', 'vg_sheet_editor_automations' ), $context, $exception->getMessage() );
			} else {
				/* translators: %s: exception message */
				$message = sprintf( __( 'Process failed: %s', 'vg_sheet_editor_automations' ), $exception->getMessage() );
			}
			self::mark_job_as_failed( $job_id );
			WPSE_Logger_Obj()->entry( $message, $job_id );
		}
		public static function scheduled_action_timedout( $action_id, $timeout ) {
			$job_id = self::action_id_to_job_id( $action_id );
			if ( ! $job_id ) {
				return;
			}
			self::mark_job_as_failed( $job_id );

			/* translators: %s: amount of time */
			WPSE_Logger_Obj()->entry( sprintf( __( 'Scheduled exports: Process marked as failed after %s seconds. Unknown error occurred. Check server, PHP and database error logs to diagnose cause.', 'vg_sheet_editor_automations' ), $timeout ), $job_id );
		}

		public static function user_can_edit_job_sheet( $job_id ) {
			global $wpdb;
			$sheet_key = $wpdb->get_var( $wpdb->prepare( 'SELECT sheet_key FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ) );
			return VGSE()->helpers->user_can_edit_post_type( $sheet_key );
		}

		public static function user_can_edit_its_own_job( $job_id ) {
			global $wpdb;
			$is_author = (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::$table_name . ' WHERE job_id = %s AND author = %d', $job_id, get_current_user_id() ) );
			return $is_author && self::user_can_edit_job_sheet( $job_id );
		}
		public static function get_job( $job_id ) {
			global $wpdb;
			if ( empty( $job_id ) ) {
				return false;
			}
			if ( is_string( $job_id ) ) {
				$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			} elseif ( is_array( $job_id ) ) {
				$action = $job_id;
			}
			if ( empty( $action ) ) {
				return false;
			}

			if ( strpos( $action['task_args'], '{' ) === 0 ) {
				$action['task_args'] = json_decode( $action['task_args'], true );
			} else {
				$action['task_args'] = array();
			}
			$action['task_args']['page'] = (int) $action['page'];
			if ( strpos( $action['settings'], '{' ) === 0 ) {
				$action = array_merge( $action, json_decode( $action['settings'], true ) );
				unset( $action['settings'] );
			}
			$action['author']       = (int) $action['author'];
			$action['runs_count']   = (int) $action['runs_count'];
			$action['page']         = (int) $action['page'];
			$action['repeat_every'] = (int) $action['repeat_every'];
			$action['id']           = (int) $action['id'];

			if ( (bool) VGSE()->get_option( 'automations_verbose_logs' ) ) {
				$action['verbose_log'] = true;
			}
			return $action;
		}

		/**
		 * Update the job configuration.
		 *
		 * @param  string $job_id
		 * @param  array $data
		 * @return boolean True if the job date changed, false if the date didn't change
		 */
		public static function update_job( $job_id, $data ) {
			global $wpdb;

			$job_raw        = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %d', $job_id ), ARRAY_A );
			$date_changed   = ! empty( $data['date'] ) && $job_raw['date'] !== $data['date'];
			$data_to_update = array();
			if ( isset( $data['name'] ) ) {
				$data_to_update['name'] = $data['name'];
			}
			if ( isset( $data['repeat_every'] ) ) {
				$data_to_update['repeat_every'] = $data['repeat_every'];
			}
			if ( isset( $data['repeat_unit'] ) ) {
				$data_to_update['repeat_unit'] = $data['repeat_unit'];
			}
			if ( isset( $data['email_notification_address'] ) ) {
				$job_raw['settings']                               = json_decode( $job_raw['settings'], true );
				$job_raw['settings']['email_notification_address'] = $data['email_notification_address'];
				$data_to_update['settings']                        = wp_json_encode( $job_raw['settings'] );
			}

			if ( ! empty( $data_to_update ) ) {
				$date_changed['updated'] = current_time( 'mysql', true );
				$wpdb->update(
					self::$table_name,
					$data_to_update,
					array(
						'job_id' => $job_id,
					)
				);
			}

			if ( ! $date_changed ) {
				return false;
			}

			// Remove the reschedule parameter if the job has been edited manually by the user
			self::set_new_job_settings(
				$job_id,
				array(
					'reschedule_in_x_seconds' => null,
				)
			);
			$new_params = array(
				'is_a_restart' => true,
				'date'         => $data['date'],
				'previous_run' => $job_raw['previous_run'],
			);
			$result     = self::schedule_next_action( $job_raw['category'], $job_id, $new_params );
			return true;
		}

		public static function job_exists( $job_id ) {
			global $wpdb;

			$job_exists = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE job_id = %s', self::$table_name, $job_id ) );
			return $job_exists > 0;
		}

		public static function schedule_single_action( $hook, $args ) {
			/**
			 * @var wpdb
			 */
			global $wpdb;

			if ( isset( $args['task_args']['nonce'] ) ) {
				unset( $args['task_args']['nonce'] );
			}
			if ( isset( $args['task_args']['page'] ) ) {
				unset( $args['task_args']['page'] );
			}
			if ( isset( $args['_wpnonce'] ) ) {
				unset( $args['_wpnonce'] );
			}
			if ( isset( $args['wpse_job_id'] ) ) {
				$args['job_id'] = $args['wpse_job_id'];
				unset( $args['wpse_job_id'] );
			}
			$existing_job_raw                 = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE job_id = %s AND category = %s', self::$table_name, $args['job_id'], $hook ) );
			$args['original_date']            = $args['date'];
			$args['task_args']['sheet_key']   = $args['sheet_key'];
			$args['task_args']['wpse_job_id'] = $args['job_id'];
			$args['page']                     = 1;

			foreach ( $args['task_args'] as $key => $value ) {
				if ( is_string( $value ) && in_array( $value, array( 'true', 'false' ), true ) ) {
					$args['task_args'][ $key ] = VGSE()->helpers->do_booleable( $value );
				}
			}

			$utc_date = $args['date'];

			$status = ! empty( $args['status'] ) ? $args['status'] : 'scheduled';
			$data   = array(
				'job_id'        => $args['job_id'],
				'sheet_key'     => $args['sheet_key'],
				'status'        => $status,
				'runs_count'    => ! empty( $args['runs_count'] ) ? (int) $args['runs_count'] : 0,
				'repeat_every'  => $args['repeat_every'],
				'repeat_unit'   => $args['repeat_unit'],
				'date'          => $args['date'],
				'original_date' => $args['original_date'],
				'name'          => isset( $args['name'] ) ? $args['name'] : '',
				'task_args'     => wp_json_encode( $args['task_args'] ),
				'settings'      => array(),
				'category'      => $hook,
				'author'        => ! empty( $args['author'] ) ? (int) $args['author'] : get_current_user_id(),
				'page'          => 1,
				'created'       => current_time( 'mysql', true ),
			);

			if ( $existing_job_raw ) {
				$data['settings'] = wp_json_encode( array_merge( json_decode( $existing_job_raw->settings, true ), array_diff_key( $args, $data ) ) );
				$old_job_data     = self::get_job( $args['job_id'] );
				$wpdb->update(
					self::$table_name,
					$data,
					array(
						'job_id'   => $args['job_id'],
						'category' => $hook,
					),
					array(
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%s',
					),
					array( '%s', '%s' ),
				);
				do_action( 'vg_sheet_editor/automations/core/after_job_edited', self::get_job( $args['job_id'] ), $old_job_data, $hook, $args );
			} else {
				$data['settings'] = wp_json_encode( array_diff_key( $args, $data ) );
				$wpdb->insert(
					self::$table_name,
					$data,
					array(
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%s',
					)
				);
			}
			do_action( 'vg_sheet_editor/automations/core/after_job_saved', self::get_job( $args['job_id'] ), $hook, $args );
			
			$group_slug = 'sheet-editor-' . $hook . '-' . $args['job_id'];
			if ( $status === 'scheduled' ) {
				// Cancel all pending actions related to the same group because we can only have one action scheduled per job
				ActionScheduler_Store::instance()->cancel_actions_by_group( $group_slug );
				$result = as_schedule_single_action( strtotime( $utc_date ), 'vg_sheet_editor/scheduled/' . $hook, array( $args['job_id'] ), $group_slug );
			}
		}

		public static function get_status_description( $status ) {
			$labels = array(
				'scheduled'   => __( 'This means the job is scheduled for a future date and it will be executed automatically in the future.', 'vg_sheet_editor_automations' ),
				'completed'   => __( 'This means the job was successfully executed and it won\'t run again. If this is a real-time sync, the first full run has been completed and it\'s listening and monitoring for changes right now.', 'vg_sheet_editor_automations' ),
				'archived'    => __( 'This means the job will no longer be used for anything, it\'s archived for historical purposes only and it can be deleted once you no longer need to review its log.', 'vg_sheet_editor_automations' ),
				'in-progress' => __( 'This means that the job is running at this moment, it\'s processing all its related batches.', 'vg_sheet_editor_automations' ),
				'cancelled'   => __( 'This means that the job has been cancelled and removed from the schedule, it won\'t run again and it won\'t be used for anything', 'vg_sheet_editor_automations' ),
				'failed'      => __( 'This means that there was an error when executing this job and it was marked as failed so you review the log to find out what happened, you can edit the job and manually schedule it to run again when you fix the issue.', 'vg_sheet_editor_automations' ),
				'paused'      => __( 'The job was temporarily paused and removed from the schedule. It will not run on its originally scheduled date but can be resumed later. If the original date hasn\'t passed, it will execute on the original date when resumed. If the original date has already passed, it will be rescheduled to run at the time of resumption. If the job was paused during a batch, the current batch will continue to be processed. However, the next batch will not be processed until the job is resumed.', 'vg_sheet_editor_automations' ),
				'restored'    => __( 'The job will be returned to its previous state before it was archived. If the previous state was scheduled and the date has not passed yet, it will be automatically rescheduled to run on the same date. However, if the previously scheduled date has already passed, it will run immediately. If the job was archived during execution, it will resume from where it left off. If the job was a real-time sync, it will resume its function of listening and monitoring for changes.', 'vg_sheet_editor_automations' ),
			);

			return isset( $labels[ $status ] ) ? $labels[ $status ] : '';
		}

		public static function get_formatted_status( $status ) {
			$labels = array(
				'scheduled'   => __( 'Scheduled', 'vg_sheet_editor_automations' ),
				'completed'   => __( 'Completed', 'vg_sheet_editor_automations' ),
				'archived'    => __( 'Archived', 'vg_sheet_editor_automations' ),
				'in-progress' => __( 'In progress', 'vg_sheet_editor_automations' ),
				'cancelled'   => __( 'Cancelled', 'vg_sheet_editor_automations' ),
				'failed'      => __( 'Failed', 'vg_sheet_editor_automations' ),
				'paused'      => __( 'Paused', 'vg_sheet_editor_automations' ),
			);

			return isset( $labels[ $status ] ) ? $labels[ $status ] : '';
		}

		public static function get_formatted_repeat( $repeat_every, $repeat_unit ) {
			$labels = array(
				'minute' => __( 'Minute(s)', 'vg_sheet_editor_automations' ),
				'hour'   => __( 'Hour(s)', 'vg_sheet_editor_automations' ),
				'day'    => __( 'Day(s)', 'vg_sheet_editor_automations' ),
				'week'   => __( 'Week(s)', 'vg_sheet_editor_automations' ),
				'month'  => __( 'Month(s)', 'vg_sheet_editor_automations' ),
			);

			if ( ! isset( $labels[ $repeat_unit ] ) ) {
				return '';
			}

			return (int) $repeat_every . ' ' . $labels[ $repeat_unit ];
		}

		public static function get_next_action( $job_id ) {
			global $wpdb;
			$action = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}actionscheduler_actions a
			LEFT JOIN {$wpdb->prefix}actionscheduler_groups g 
			ON a.group_id = g.group_id 
			WHERE g.slug LIKE %s AND a.status = 'pending' LIMIT 1",
					'%' . $wpdb->esc_like( $job_id ) . '%'
				),
				ARRAY_A
			);

			return $action;
		}

		public static function is_background_request() {
			$is_cli = defined( 'WP_CLI' ) && WP_CLI;

			$is_async_action_runner = wp_doing_ajax() && ! empty( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'as_async_request_queue_runner', 'ashp_create_additional_runners', 'mailpoet-cron-action-scheduler-run' ), true );

			return ! is_user_logged_in() && ( wp_doing_cron() || $is_cli || $is_async_action_runner );
		}

		/**
		 * Can initialize on the current request?
		 */
		public static function can_initialize_on_request() {
			$out                    = wp_doing_ajax() || self::is_background_request() || is_admin() || VGSE()->helpers->is_rest_request();
			$is_acf_form_submission = ! empty( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_POST['_acf_screen'] ) && ! empty( $_POST['_acf_nonce'] ) && wp_verify_nonce( $_POST['_acf_nonce'], 'acf_form' );

			$is_my_listing_form_submission = function_exists( 'mylisting' ) && is_user_logged_in() && ! empty( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_REQUEST['job_manager_form'] ) && $_REQUEST['job_manager_form'] === 'edit-listing' && ! empty( $_REQUEST['job_id'] );
			return $out || $is_acf_form_submission || $is_my_listing_form_submission;
		}

		public static function set_new_job_settings( $job_id, $new_settings ) {
			global $wpdb;
			$job = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $job ) {
				return;
			}
			if ( strpos( $job['settings'], '{' ) === 0 ) {
				$job['settings'] = json_decode( $job['settings'], true );
			}
			$wpdb->update(
				self::$table_name,
				array(
					'settings' => wp_json_encode( wp_parse_args( $new_settings, $job['settings'] ) ),
					'updated'  => current_time( 'mysql', true ),
				),
				array(
					'job_id' => $job['job_id'],
				)
			);
		}

		public static function pause_job( $job_id, $reason = '', $new_settings = array() ) {
			global $wpdb;
			$action = self::get_next_action( $job_id );

			if ( $action ) {
				// Delete the action because we'll reschedule when the job is resumed.
				ActionScheduler_Store::instance()->delete_action( $action['action_id'] );
			}
			self::change_job_status( $job_id, 'paused', 'scheduled' );
			self::change_job_status( $job_id, 'paused', 'in-progress' );

			if ( $reason ) {
				WPSE_Logger_Obj()->entry( sprintf( esc_html__( 'This job has been paused. Reason: %s', 'vg_sheet_editor_automations' ), esc_html( $reason ) ), $job_id );
			}
			if ( ! empty( $new_settings ) ) {
				self::set_new_job_settings( $job_id, $new_settings );
			}
		}

		public static function delete_job( $job_id ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action ) {
				return;
			}
			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/pre_delete', $job_id );

			$pattern = untrailingslashit( WPSE_CSV_API_Obj()->uploads_dir ) . '/*/*' . $job_id . '*';
			$files   = glob( $pattern );
			foreach ( $files as $file_path ) {
				if ( file_exists( $file_path ) ) {
					unlink( $file_path );
				}
			}
			$wpdb->delete( self::$table_name, array( 'job_id' => $job_id ) );

			$action = self::get_next_action( $job_id );

			if ( $action ) {
				ActionScheduler_Store::instance()->delete_action( $action['action_id'] );
			}
		}
		public static function mark_job_cancelled( $job_id ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action || $action['status'] === 'cancelled' ) {
				return;
			}
			$wpdb->update(
				self::$table_name,
				array(
					'status'       => 'cancelled',
					'date'         => null,
					'previous_run' => $action['date'],
					'updated'      => current_time( 'mysql', true ),
				),
				array(
					'job_id' => $job_id,
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
				),
				array( '%s' )
			);

			$next_action = self::get_next_action( $job_id );

			if ( $next_action ) {
				ActionScheduler_Store::instance()->cancel_action( $next_action['action_id'] );
			}
			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/cancelled', $job_id );
		}

		public static function restore_job( $job_id ) {
			global $wpdb;
			$job = self::get_job( $job_id );
			if ( $job['status'] !== 'archived' ) {
				return;
			}
			$new_job_data = array(
				'status'  => $job['previous_status'],
				'updated' => current_time( 'mysql', true ),
			);
			if ( $job['previous_status'] === 'scheduled' && $job['date'] < current_time( 'mysql', true ) ) {
				$new_job_data['date'] = current_time( 'mysql', true );
				$job['date']          = $new_job_data['date'];
			}
			$wpdb->update(
				self::$table_name,
				$new_job_data,
				array(
					'job_id' => $job_id,
				),
			);

			if ( $job['previous_status'] === 'scheduled' ) {

				$utc_date = $job['date'] >= current_time( 'mysql', true ) ? $job['date'] : current_time( 'mysql', true );

				// Reschedule action
				$group_slug = 'sheet-editor-' . $job['category'] . '-' . $job_id;
				// Cancel all pending actions related to the same group because we can only have one action scheduled per job
				ActionScheduler_Store::instance()->cancel_actions_by_group( $group_slug );
				$result = as_schedule_single_action( strtotime( $utc_date ), 'vg_sheet_editor/scheduled/' . $job['category'], array( $job_id ), $group_slug );
			}

			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $job['category'] . '/restored', $job_id );
		}
		public static function archive_job( $job_id ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action || $action['status'] === 'archived' ) {
				return;
			}
			self::change_job_status( $job_id, 'archived' );

			$action = self::get_next_action( $job_id );
			if ( $action ) {
				ActionScheduler_Store::instance()->delete_action( $action['action_id'] );
			}
			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/archived', $job_id );
		}
		public static function mark_job_as_failed( $job_id ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action || $action['status'] === 'failed' ) {
				return;
			}
			$wpdb->update(
				self::$table_name,
				array(
					'status'       => 'failed',
					'date'         => null,
					'previous_run' => $action['date'],
					'updated'      => current_time( 'mysql', true ),
				),
				array(
					'job_id' => $job_id,
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
				),
				array( '%s' )
			);
			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/failed', $job_id );
		}

		public static function change_job_status( $job_id, $new_status, $prev_status = null ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action || $prev_status && $action['status'] !== $prev_status ) {
				return;
			}
			if ( $action['status'] === $new_status ) {
				return;
			}
			$wpdb->update(
				self::$table_name,
				array(
					'status'  => $new_status,
					'updated' => current_time( 'mysql', true ),
				),
				array(
					'job_id' => $job_id,
				),
				array(
					'%s',
					'%s',
				),
				array( '%s' )
			);

			self::set_new_job_settings(
				$job_id,
				array(
					'previous_status' => $action['status'],
				)
			);
			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/' . $new_status, $job_id );
		}

		public static function resume_job( $job_id, $reason = '', $new_settings = array() ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action ) {
				return;
			}

			if ( $reason ) {
				WPSE_Logger_Obj()->entry( sprintf( esc_html__( 'This job has been resumed. Reason: %s', 'vg_sheet_editor_automations' ), esc_html( $reason ) ), $job_id );
			}

			if ( ! empty( $new_settings ) ) {
				self::set_new_job_settings( $job_id, $new_settings );
			}
			if ( $action['status'] !== 'paused' ) {
				return;
			}

			$job = self::get_job( $job_id );
			if ( $action['date'] < current_time( 'mysql', true ) ) {
				$action['date'] = current_time( 'mysql', true );
			}
			$wpdb->update(
				self::$table_name,
				array(
					'status'  => $job['previous_status'],
					'date'    => $action['date'],
					'updated' => current_time( 'mysql', true ),
				),
				array(
					'job_id' => $job_id,
				),
				array(
					'%s',
					'%s',
					'%s',
				),
				array( '%s' )
			);

			$new_date = $job['date'] >= current_time( 'mysql', true ) ? $job['date'] : current_time( 'mysql', true );
			$utc_date = $new_date;

			// Reschedule action
			$group_slug = 'sheet-editor-' . $job['category'] . '-' . $job_id;
			// Cancel all pending actions related to the same group because we can only have one action scheduled per job
			ActionScheduler_Store::instance()->cancel_actions_by_group( $group_slug );
			$result = as_schedule_single_action( strtotime( $utc_date ), 'vg_sheet_editor/scheduled/' . $job['category'], array( $job_id ), $group_slug );

			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/' . $job['previous_status'], $job_id );
		}
		public static function mark_job_as_finished( $job_id ) {
			global $wpdb;
			$action = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . ' WHERE job_id = %s', $job_id ), ARRAY_A );
			if ( ! $action || $action['status'] === 'completed' ) {
				return;
			}
			$wpdb->update(
				self::$table_name,
				array(
					'status'       => 'completed',
					'date'         => null,
					'previous_run' => $action['date'],
					'updated'      => current_time( 'mysql', true ),
				),
				array(
					'job_id' => $job_id,
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
				),
				array( '%s' )
			);
			do_action( 'vg_sheet_editor/automations/job_status_changed/' . $action['category'] . '/finished', $job_id );
		}

		/**
		 * Check if there is an ongoing real-time export that already completed its first full run.
		 *
		 * @return boolean
		 */
		public static function uses_real_time_exports() {
			global $wpdb;
			$real_time_jobs = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::$table_name . " WHERE task_args LIKE '%\"real_time_export\":true%' AND status = 'completed' " );
			return (bool) $real_time_jobs;
		}

		public static function sheet_has_real_time_exports( $sheet_key ) {
			global $wpdb;
			$real_time_jobs = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::$table_name . " WHERE task_args LIKE '%\"real_time_export\":true%' AND status = 'completed' AND sheet_key = %s", $sheet_key ) );
			return (bool) $real_time_jobs;
		}

		public static function find_real_time_exports_for_content_ids( $ids, $sheet_key ) {
			global $wpdb;
			$real_time_jobs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::$table_name . " WHERE task_args LIKE '%\"real_time_export\":true%' AND status = 'completed' AND sheet_key = %s", $sheet_key ), ARRAY_A );
			$out            = array();

			foreach ( $real_time_jobs as $raw_job ) {
				$job = self::get_job( $raw_job );
				if ( ! $job ) {
					continue;
				}

				if ( empty( $job['task_args']['filters'] ) ) {
					$job['task_args']['filters'] = '{}';
				}
				$filters = json_decode( $job['task_args']['filters'], true );
				// If the export doesn't have filters, this should include all the posts
				if ( empty( $filters ) ) {
					$job['content_ids'] = $ids;
					$out[]              = $job;
					continue;
				}

				// If the export has filters by ID, only include the matching IDs or skip
				if ( ! empty( $filters['post__in'] ) ) {
					$filter_ids   = VGSE()->helpers->get_ids_from_text_list( $filters['post__in'] );
					$matching_ids = array_intersect( $ids, $filter_ids );
					if ( $matching_ids ) {
						$job['content_ids'] = $matching_ids;
						$out[]              = $job;
					}
					continue;
				}

				$filters['post__in'] = implode( ',', $ids );

				$get_rows_args       = apply_filters(
					'vg_sheet_editor/gs_real_time_export/search_query/get_rows_args',
					array(
						'nonce'       => wp_create_nonce( 'bep-nonce' ),
						'post_type'   => $sheet_key,
						'filters'     => $filters,
						'wpse_source' => 'gs_real_time_export',
					)
				);
				$_REQUEST['filters'] = $filters;

				// We want to get the $base_query using the permissions of the job author
				$current_user_id = WP_Sheet_Editor_Helpers::get_current_user_id();
				WP_Sheet_Editor_Helpers::set_current_user( (int) $job['author'] );

				$base_query = VGSE()->helpers->prepare_query_params_for_retrieving_rows( $get_rows_args );
				WP_Sheet_Editor_Helpers::set_current_user( $current_user_id );

				if ( is_wp_error( $base_query ) ) {
					if ( ! empty( $job['verbose_log'] ) ) {
						$http_request_data = array(
							'Running from'          => PHP_SAPI,
							'is_background_request' => WPSE_Automations_Core::is_background_request(),
							'$_SERVER'              => $_SERVER,
							'$_POST'                => $_POST,
							'$_GET'                 => $_GET,
							'referrer'              => wp_get_referer(),
						);

						WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $base_query : ' . var_export( compact( 'base_query', 'get_rows_args', 'job', 'ids', 'sheet_key', 'current_user_id', 'http_request_data' ), true ) );
					}
					continue;
				}
				$base_query['posts_per_page'] = -1;
				$base_query['paged']          = 1;
				$base_query['fields']         = 'ids';
				$editor                       = VGSE()->helpers->get_provider_editor( $sheet_key );
				$query                        = $editor->provider->get_items( $base_query );
				$matching_ids                 = $query->posts;

				if ( $matching_ids ) {
					$job['content_ids'] = $matching_ids;
					$out[]              = $job;
				}
				continue;
			}

			return $out;
		}

		public static function increase_run_count( $job_id ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( 'UPDATE ' . self::$table_name . ' SET `runs_count` = `runs_count`+1 WHERE `job_id` = %s', $job_id ) );
		}

		public static function update_job_task_args( $job_id, $new_task_args ) {
			global $wpdb;
			if ( empty( $new_task_args ) ) {
				return;
			}

			// If the job doesn't exist, exit
			$job = self::get_job( $job_id );
			if ( ! $job ) {
				return;
			}
			// If this is the job owner, skip the check because the owner can edit all the job configuration
			// If this is not the job owner (i.e. cron process), only allow to edit the whitelisted keys for safety reasons
			if ( self::user_can_edit_its_own_job( $job_id ) ) {
				$filtered_task_args = $new_task_args;
			} else {
				$whitelisted_keys   = apply_filters(
					'vg_sheet_editor/automations/whitelisted_task_args_for_update',
					array(
						'existing_check_csv_field',
						'existing_check_wp_field',
						'sheet_editor_column',
						'source_column',
					)
				);
				$filtered_task_args = array_intersect_key( $new_task_args, array_flip( $whitelisted_keys ) );
			}
			if ( empty( $filtered_task_args ) ) {
				return;
			}

			$job['task_args'] = array_merge( $job['task_args'], $filtered_task_args );
			$wpdb->update(
				$wpdb->prefix . 'wpse_scheduled_tasks',
				array(
					'task_args' => wp_json_encode( $job['task_args'] ),
				),
				array(
					'job_id' => $job['job_id'],
				),
				array( '%s' ),
				array( '%s' )
			);
		}

		public static function schedule_next_action( $hook, $job_id, $new_params ) {
			global $wpdb;

			$job = self::get_job( $job_id );

			$editable_keys = array(
				'date',
				'is_a_restart',
				'file_position',
				'previous_run',
			);
			$new_params    = array_intersect_key( $new_params, array_flip( $editable_keys ) );

			$is_a_restart         = ! empty( $new_params['is_a_restart'] );
			$default_previous_run = $job['date'] > current_time( 'mysql', true ) ? current_time( 'mysql', true ) : $job['date'];
			$previous_run         = ! empty( $new_params['previous_run'] ) && $new_params['previous_run'] <= current_time( 'mysql', true ) ? $new_params['previous_run'] : $default_previous_run;
			$data                 = array(
				'status'       => $is_a_restart ? 'scheduled' : 'in-progress',
				'date'         => $new_params['date'],
				'previous_run' => $previous_run,
				'updated'      => current_time( 'mysql', true ),
			);
			if ( $is_a_restart ) {
				// Save the new original date as the base for future restarts
				$data['original_date'] = $job['date'];
				$data['page']          = 1;

				$original_job_id                 = preg_replace( '/-r-.+$/', '', $job_id );
				$job['task_args']['wpse_job_id'] = $original_job_id . '-r-' . wp_generate_password( 4, false, false );
				if ( isset( $job['task_args']['file_position'] ) ) {
					$job['task_args']['file_position'] = 0;
				}
			} else {
				if ( isset( $job['page'] ) ) {
					$data['page'] = $job['page'] + 1;
				}
				if ( isset( $new_params['file_position'] ) ) {
					$job['task_args']['file_position'] = $new_params['file_position'];
				}
			}
			$data['task_args'] = wp_json_encode( $job['task_args'] );

			if ( ! empty( $job['verbose_log'] ) ) {
				WPSE_Logger_Obj()->entry( __CLASS__ . ':' . __FUNCTION__ . ': Line ' . __LINE__ . ' $data : ' . var_export( compact( 'data', 'new_params' ), true ), $job_id );
			}

			$wpdb->update(
				self::$table_name,
				$data,
				array(
					'job_id' => $job_id,
				)
			);
			$utc_date   = $new_params['date'];
			$group_slug = 'sheet-editor-' . $hook . '-' . $job_id;
			// Cancel all pending actions related to the same group because we can only have one action scheduled per job
			ActionScheduler_Store::instance()->cancel_actions_by_group( $group_slug );
			$result = as_schedule_single_action( strtotime( $utc_date ), 'vg_sheet_editor/scheduled/' . $hook, array( $job_id ), $group_slug );
			return $result;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automations_Core();
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

if ( ! function_exists( 'WPSE_Automations_Core_Obj' ) ) {
	function WPSE_Automations_Core_Obj() {
		return WPSE_Automations_Core::get_instance();
	}
}
WPSE_Automations_Core_Obj();
