<?php
if ( ! class_exists( 'WPSE_Enable_Media_Replace' ) ) {

	class WPSE_Enable_Media_Replace {

		private static $instance = false;
		var $post_type           = 'attachment';

		private function __construct() {

		}

		function init() {
			if ( ! defined( 'EMR_VERSION' ) ) {
				return;
			}
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
				'enable_media_replace_new_url',
				$post_type,
				array(
					'data_type'             => 'post_data',
					'column_width'          => 150,
					'title'                 => __( 'Replace with this file', vgse_media_library()->textname ),
					'supports_formulas'     => true,
					'supports_sql_formulas' => false,
					'allow_to_hide'         => true,
					'allow_to_save'         => true,
					'allow_to_rename'       => true,
					'save_value_callback'   => array( $this, 'replace_file' ),
					'get_value_callback'    => array( $this, 'get_media_replace_file' ),
					'type'                  => 'boton_gallery',
				)
			);
			$editor->args['columns']->register_item(
				'enable_media_replace_use_new_url',
				$post_type,
				array(
					'data_type'             => 'post_data',
					'column_width'          => 150,
					'title'                 => __( 'Replace with this file and use new file name', vgse_media_library()->textname ),
					'supports_formulas'     => true,
					'supports_sql_formulas' => false,
					'allow_to_hide'         => true,
					'allow_to_save'         => true,
					'allow_to_rename'       => true,
					'save_value_callback'   => array( $this, 'replace_file_new_url' ),
					'get_value_callback'    => array( $this, 'get_media_replace_file' ),
					'type'                  => 'boton_gallery',
				)
			);
		}

		function get_media_replace_file( $post, $cell_key = null, $cell_args = null ) {
			return '';
		}

		function replace_file_new_url( $post_id, $cell_key, $data_to_save, $post_type = null, $cell_args = null, $spreadsheet_columns = null ) {
			$this->_replace_file( $data_to_save, $post_id, 2 );
		}

		function replace_file( $post_id, $cell_key, $data_to_save, $post_type = null, $cell_args = null, $spreadsheet_columns = null ) {
			$this->_replace_file( $data_to_save, $post_id );
		}

		function _replace_file( $data_to_save, $post_id, $replacement_mode = 1 ) {
			if ( empty( $data_to_save ) ) {
				return;
			}

			$new_file_id   = current( VGSE()->helpers->maybe_replace_urls_with_file_ids( $data_to_save ) );
			$new_file_path = get_attached_file( $new_file_id );
			if ( ! $new_file_path || is_wp_error( $new_file_path ) || ! file_exists( $new_file_path ) ) {
				return;
			}

			$replace_controller = new \EnableMediaReplace\Controller\ReplaceController( $post_id );
			$params             = array(
				'replace_type'      => $replacement_mode,
				'timestamp_replace' => \EnableMediaReplace\Controller\ReplaceController::TIME_UPDATEMODIFIED,
				'new_date'          => current_time( 'mysql' ),
				'new_location'      => false,
				'new_location_dir'  => false,
				'new_filename'      => basename( $new_file_path ),
				'uploadFile'        => $new_file_path,
			);
			$replace_controller->setupParams( $params );

			try {
				$result = $replace_controller->run();
			} catch ( \RunTimeException $e ) {
				var_dump( $e->getMessage() );
			}

			wp_delete_attachment( $new_file_id );
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WPSE_Enable_Media_Replace();
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

if ( ! function_exists( 'WPSE_Enable_Media_Replace_Obj' ) ) {

	function WPSE_Enable_Media_Replace_Obj() {
		return WPSE_Enable_Media_Replace::get_instance();
	}
}
add_action( 'vg_sheet_editor/initialized', 'WPSE_Enable_Media_Replace_Obj' );
