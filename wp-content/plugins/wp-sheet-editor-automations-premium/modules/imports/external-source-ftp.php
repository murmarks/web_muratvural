<?php
if ( ! class_exists( 'WPSE_Automations_External_Import_FTP' ) ) {
	class WPSE_Automations_External_Import_FTP extends WPSE_Automations_External_Import {

		public function get_external_file_contents( $ftp ) {

			$ftp_server_key = strtok( $ftp, ':' );
			$ftp_file_path  = strtok( '' );

			$ftp_server   = WPSE_Automations_Core::get_ftp_server( $ftp_server_key );
			$csv_contents = WPSE_Automations_Core::get_ftp_file( $ftp_server['username'], $ftp_server['password'], $ftp_server['hostname'], $ftp_server['port'], $ftp_server['secure'], $ftp_file_path );

			if ( is_wp_error( $csv_contents ) ) {
				return $csv_contents;
			} elseif ( ! is_string( $csv_contents ) ) {
				return new WP_Error( 'wpse', __( 'Please verify that you\'ve entered the complete file path and that your server connection details are accurate. We encountered an issue retrieving the FTP file contents.', 'vg_sheet_editor_automations' ) );
			} elseif ( $csv_contents === '' ) {
				return new WP_Error( 'wpse', __( 'The FTP file seems to be empty.', 'vg_sheet_editor_automations' ) );
			} else {
				return $csv_contents;
			}
		}
        public function get_file_url_in_external_site( $external_file ) {
			return null;
        }
		public function stop_watching_file( $job ) {
		}
		public function start_watching_file( $job ) {
		}
		public function render_external_file_link( $job ) {
			$server    = strtok( $job['task_args'][ $this->file_field_key ], ':' );
			$file_path = strtok( '' );

			$ftp_server = WPSE_Automations_Core::get_ftp_server( $server );
			?>
			<?php echo esc_html( $ftp_server['name'] ); ?> > <?php echo esc_html( $file_path ); ?>
			<?php
		}
		public function render_import_fields( $post_type ) {
			$ftp_servers = WPSE_Automations_Core::get_ftp_servers();
			?>
			<label><?php esc_html_e( 'FTP Connection', 'vg_sheet_editor_automations' ); ?></label>
			<?php if ( ! function_exists( 'ftp_connect' ) ) { ?>
<p><?php esc_html_e( 'Error: Your server is missing the PHP extension "ftp", which is required to make FTP connections with other servers. Please ask your hosting provider to install the PHP extension.', 'vg_sheet_editor_automations' ); ?></p>
			<?php } elseif ( empty( $ftp_servers ) ) { ?>
<p><?php esc_html_e( 'You haven\'t registered any FTP servers yet. For security reasons, you must add your FTP server information in your wp-config.php file. Please copy the code below and add it to your wp-config.php file and replace the sample value of every parameter with your real details, and reload this page afterwards.', 'vg_sheet_editor_automations' ); ?></p>
<p><pre style="text-align: left;">define('WPSE_AUTOMATIONS_FTP_SERVERS', array(
				<?php
				echo sanitize_textarea_field(
					var_export(
						array(
							'name'     => 'My FTP server name', // this must never change
							'hostname' => 'my-ftp-server-ip-or-domain.com', // domain name or server ip
							'port'     => 22, // int
							'username' => 'my-ftp-username',
							'password' => 'my-ftp-password',
							'secure' => false,
						),
						true
					)
				);
				?>
			));</pre></p>
				<?php } else { ?>
				<select class="select2" name="ftp_server">
					<option value=""></option>
					<?php
					foreach ( $ftp_servers as $server ) {
						?>
						<option value="<?php echo esc_attr( $server['id'] ); ?>"><?php echo esc_html( $server['name'] ); ?></option>
						<?php
					}
					?>
				</select>

				<div class="ftp-file-wrapper hidden">
					<label><?php esc_html_e( 'FTP file path', 'vg_sheet_editor_automations' ); ?></label>
					<input type="text" name="ftp_file_path">
					
					<?php self::render_what_happens_to_deleted_rows_field( $post_type ); ?>
				</div>
				<?php } ?>

				<input type="hidden" name="<?php echo esc_attr( $this->file_field_key ); ?>" class="data">
			<?php
		}
	}
}
