<?php
if ( ! class_exists( 'WPSE_Automations_External_Import_URL' ) ) {
	class WPSE_Automations_External_Import_URL extends WPSE_Automations_External_Import {

		public function __construct( $args ) {
			parent::__construct( $args );
		}
		public function get_external_file_contents( $url ) {
			$temp_file_path = WPSE_CSV_API_Obj()->get_remote_file( $url );

			if ( is_wp_error( $temp_file_path ) ) {
				return $temp_file_path;
			}
			$csv_contents = file_get_contents( $temp_file_path );
			if ( ! is_string( $csv_contents ) ) {
				return new WP_Error( 'wpse', __( 'Please verify that you\'ve entered the complete file path and that your server connection details are accurate. We encountered an issue retrieving the file contents.', 'vg_sheet_editor_automations' ) );
			} elseif ( $csv_contents === '' ) {
				return new WP_Error( 'wpse', __( 'The file seems to be empty.', 'vg_sheet_editor_automations' ) );
			} else {
				return $csv_contents;
			}
		}
		public function get_file_url_in_external_site( $external_file ) {
			return $external_file;
		}
		public function stop_watching_file( $job ) {
		}
		public function start_watching_file( $job ) {
		}
		public function render_external_file_link( $job ) {
			$url = $this->get_file_url_in_external_site( $job['task_args'][ $this->file_field_key ] );
			?>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php esc_html_e( 'Download', 'vg_sheet_editor_automations' ); ?></a>
			<?php
		}
		public function render_import_fields_wrapper( $post_type ) {
			if ( ! VGSE()->helpers->get_current_provider()->is_post_type ) {
				return;
			}
			?>
			<div class="data-input <?php echo esc_attr( $this->source_key ); ?>">
				<div class="if-scheduled-for-later">
					<?php self::render_what_happens_to_deleted_rows_field( $post_type ); ?>
				</div>
			</div>
			<?php
		}
		public function render_import_fields( $post_type ) {
		}
	}
}
