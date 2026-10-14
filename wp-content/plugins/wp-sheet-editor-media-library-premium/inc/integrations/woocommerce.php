<?php

if ( ! class_exists( 'WPSE_Media_WooCommerce' ) ) {

	class WPSE_Media_WooCommerce {

		private static $instance = false;
		var $post_type           = 'attachment';

		private function __construct() {

		}

		function init() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return;
			}
			if ( version_compare( WC()->version, '3.6.0' ) >= 0 ) {
				add_filter( 'vg_sheet_editor/import/after_available_columns_options', array( $this, 'render_product_columns_for_import' ) );
				add_filter( 'vg_sheet_editor/import/wp_check/available_columns_options', array( $this, 'filter_wp_check_options_for_import' ), 10, 2 );
				add_filter( 'vg_sheet_editor/import/find_post_id', array( $this, 'find_image_id_for_import' ), 10, 6 );
			}
		}

		function find_image_id_for_import( $image_id, $row, $post_type, $meta_query, $writing_type, $check_wp_fields ) {
			global $wpdb;
			if ( $post_type !== $this->post_type ) {
				return $image_id;
			}
			if ( empty( $row['wpse_sku'] ) || ! in_array( 'wpse_sku', $check_wp_fields ) ) {
				return $image_id;
			}
			// Allow to update all images used by a product SKU
			$sku        = sanitize_text_field( $row['wpse_sku'] );
			$product_id = wc_get_product_id_by_sku( $sku );
			if ( ! $product_id ) {
				return $image_id;
			}

			$sql    = $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key IN ('_thumbnail_id', '_product_image_gallery') AND meta_value <> '' AND post_id = %d", (int) $product_id );
			$images = $wpdb->get_results( $sql, ARRAY_A );

			if ( empty( $images ) ) {
				return $image_id;
			}

			$image_ids = array();
			foreach ( $images as $image ) {
				$image_ids = array_merge( $image_ids, array_map( 'intval', array_map( 'trim', explode( ',', $image['meta_value'] ) ) ) );
			}

			$out = array_unique( $image_ids );
			return $out;
		}

		function filter_wp_check_options_for_import( $columns, $post_type ) {

			if ( $post_type === $this->post_type ) {
				// The array elements contain the <option> html, so we use str_replace to change the option key
				$columns['wpse_sku'] = '<option value="wpse_sku">' . __( 'SKU', vgse_media_library()->textname ) . '</option>';
			}
			return $columns;
		}

		function render_product_columns_for_import( $post_type ) {

			if ( $post_type === $this->post_type ) {
				echo '<option value="wpse_sku">' . __( 'SKU', vgse_media_library()->textname ) . '</option>';
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WPSE_Media_WooCommerce();
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

if ( ! function_exists( 'WPSE_Media_WooCommerce_Obj' ) ) {

	function WPSE_Media_WooCommerce_Obj() {
		return WPSE_Media_WooCommerce::get_instance();
	}
}
WPSE_Media_WooCommerce_Obj();
