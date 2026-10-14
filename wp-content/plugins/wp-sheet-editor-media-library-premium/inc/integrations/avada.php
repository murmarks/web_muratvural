<?php

if ( ! class_exists( 'WPSE_Media_Avada' ) ) {

	class WPSE_Media_Avada {

		private static $instance = false;
		var $post_type           = 'attachment';

		private function __construct() {

		}

		function init() {
			if ( ! defined( 'FUSION_BUILDER_VERSION' ) ) {
				return;
			}
			add_filter( 'vg_sheet_editor/provider/post/update_item_meta', array( $this, 'sync_alt_text_on_post_content' ), 10, 3 );
		}

		function sync_alt_text_on_post_content( $value, $id, $key ) {
			if ( get_post_type( $id ) !== $this->post_type || $key !== '_wp_attachment_image_alt' ) {
				return $value;
			}

			$this->_sync_alt_text_avada( $id, $value );
			return $value;
		}

		function _find_posts_containing_image( $image_id ) {
			global $wpdb;
			$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content FROM $wpdb->posts WHERE post_content LIKE %s", '%[fusion_imageframe image_id="' . (int) $image_id . '|%' ), ARRAY_A );
			return $posts;
		}

		function _sync_alt_text_avada( $id, $alt_text ) {
			$posts    = $this->_find_posts_containing_image( $id );
			$alt_text = str_replace( '"', '', $alt_text );
			foreach ( $posts as $post ) {
				$original_content     = $post['post_content'];
				$regex                = '/(image_id="' . (int) $id . '\|[^"]*" max_width="[^"]*" sticky_max_width="[^"]*" style_type="[^"]*" blur="[^"]*" stylecolor="[^"]*" hover_type="[^"]*" bordersize="[^"]*" bordercolor="[^"]*" borderradius="[^"]*" align_medium="[^"]*" align_small="[^"]*" align="[^"]*" margin_top="[^"]*" margin_right="[^"]*" margin_bottom="[^"]*" margin_left="[^"]*" lightbox="[^"]*" gallery_id="[^"]*" lightbox_image="[^"]*" lightbox_image_id="[^"]*") alt="([^"]+)?"/';
				$replace              = '$1 alt="' . $alt_text . '"';
				$post['post_content'] = preg_replace( $regex, $replace, $post['post_content'] );
				if ( $post['post_content'] !== $original_content && ! empty( $post['post_content'] ) ) {
					wp_update_post( $post );
				}
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WPSE_Media_Avada();
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

if ( ! function_exists( 'WPSE_Media_Avada_Obj' ) ) {

	function WPSE_Media_Avada_Obj() {
		return WPSE_Media_Avada::get_instance();
	}
}
add_action( 'vg_sheet_editor/initialized', 'WPSE_Media_Avada_Obj' );
