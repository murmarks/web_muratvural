<?php

if ( ! class_exists( 'WPSE_Rename_Media_Files' ) ) {

	class WPSE_Rename_Media_Files {

		private static $instance = false;
		var $post_type           = 'attachment';

		private function __construct() {

		}

		function init() {
			add_action( 'vg_sheet_editor/editor/register_columns', array( $this, 'register_columns' ) );
		}

		/**
		 * Register toolbar items
		 */
		function register_columns( $editor ) {
			$post_type = $this->post_type;
			if ( ! in_array( $editor->args['provider'], array( $post_type ) ) ) {
				return;
			}
			$editor->args['columns']->register_item(
				'phoenix_new_filename',
				$post_type,
				array(
					'data_type'                => 'post_data',
					'column_width'             => 150,
					'title'                    => __( 'File name', vgse_media_library()->textname ),
					'supports_formulas'        => true,
					'forced_supports_formulas' => true,
					'allow_to_hide'            => true,
					'allow_to_save'            => true,
					'allow_to_rename'          => true,
					'is_locked'                => true,
					'lock_template_key'        => 'enable_lock_cell_template',
					'save_value_callback'      => array( $this, 'rename_file' ),
					'get_value_callback'       => array( $this, 'get_file_name' ),
				)
			);
		}

		function get_file_name( $post, $cell_key = null, $cell_args = null ) {
			$post_id = is_object( $post ) ? $post->ID : $post;
			$url     = wp_get_attachment_url( $post_id );
			return basename( $url );
		}

		function _get_file_name_without_extension( $file_name ) {
			if ( strpos( $file_name, '.' ) !== false ) {
				$file_name_parts = explode( '.', $file_name );
				array_pop( $file_name_parts );
				$file_name = implode( '.', $file_name_parts );
			}
			return $file_name;
		}

		function _rename_file( $file_id, $new_file_name, $update_in_post_content = true ) {
			$old_name            = $this->_get_file_name_without_extension( $this->get_file_name( $file_id ) );
			$sanitized_file_name = sanitize_file_name( str_replace('.', ' ', $this->_get_file_name_without_extension( $new_file_name ) ) );
			if ( ! empty( $sanitized_file_name ) && $old_name !== $sanitized_file_name ) {
				$current_file_path = get_attached_file( $file_id );
				if ( ! file_exists( $current_file_path ) ) {
					// Skip the file silently, it's too annoying to stop the import/bulk edit/saving completely just for a few files missing
					return;
					// throw new Exception( sprintf( __( 'The file ID %d can not be renamed because it does not exist in the server. A missing file can not be renamed.', vgse_media_library()->textname ), $file_id ), E_USER_ERROR );
				}
				$result = WPSE_Rename_Media_Files_Lite::do_rename_faster( $file_id, $sanitized_file_name, 0, $update_in_post_content );

				if ( $result === __( 'A file with that name already exists in the containing folder!', 'phoenix-media-rename' ) ) {
					$parts            = explode( '.', $sanitized_file_name );
					$parts[0]        .= '-' . $file_id;
					$unique_file_name = implode( '.', $parts );
					$result           = WPSE_Rename_Media_Files_Lite::do_rename_faster( $file_id, $unique_file_name, 0, $update_in_post_content );
				}
			}
		}

		function rename_file( $post_id, $cell_key, $data_to_save, $post_type = null, $cell_args = null, $spreadsheet_columns = null ) {
			$this->_rename_file( $post_id, $data_to_save );
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WPSE_Rename_Media_Files();
				self::$instance->init();
			}
			return self::$instance;
		}

		function __set( $name, $value ) {
			$this->$name = $value;
		}

		function __get( $name ) {
			return $this->$name;
		}

	}

}

if ( ! function_exists( 'WPSE_Rename_Media_Files_Obj' ) ) {

	function WPSE_Rename_Media_Files_Obj() {
		return WPSE_Rename_Media_Files::get_instance();
	}
}
WPSE_Rename_Media_Files_Obj();
