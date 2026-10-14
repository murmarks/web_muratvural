<?php

if (!class_exists('WP_Sheet_Editor_Comments_WooCommerce')) {

	class WP_Sheet_Editor_Comments_WooCommerce {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!class_exists('WooCommerce')) {
				return;
			}
			add_action('vg_sheet_editor/editor/register_columns', array($this, 'register_columns'));
		}

		function register_columns($editor) {
			$post_type = 'comments';

			if (!in_array($post_type, $editor->args['enabled_post_types'])) {
				return;
			}

			$editor->args['columns']->register_item('is_customer_note', $post_type, array(
				'data_type' => 'meta_data',
				'column_width' => 150,
				'title' => __('Is customer note?', vgse_comments()->textname),
				'supports_formulas' => true,
				'supports_sql_formulas' => true,
				'formatted' => array(
					'type' => 'checkbox',
					'checkedTemplate' => '1',
					'uncheckedTemplate' => '0',
				),
				'default_value' => '0',
			));
			$editor->args['columns']->register_item('verified', $post_type, array(
				'data_type' => 'meta_data',
				'column_width' => 150,
				'title' => __('Is verified customer?', vgse_comments()->textname),
				'supports_formulas' => true,
				'supports_sql_formulas' => true,
				'formatted' => array(
					'type' => 'checkbox',
					'checkedTemplate' => '1',
					'uncheckedTemplate' => '0',
				),
				'default_value' => '0',
			));
			$editor->args['columns']->register_item('rating', $post_type, array(
				'data_type' => 'meta_data',
				'column_width' => 150,
				'title' => __('Rating', 'woocommerce'),
				'supports_formulas' => true,
				'supports_sql_formulas' => true,
			));
			$editor->args['columns']->register_item('wpse_wc_product_sku', $post_type, array(
				'data_type' => 'post_data', 	
				'column_width' => 320, 
				'title' => __('WC Product SKU', vgse_comments()->textname),
				'supports_formulas' => true,
				'supports_sql_formulas' => false,
				'allow_to_save' => true,
				'get_value_callback' => array($this, 'get_product_sku_for_column'),
				'save_value_callback' => array($this, 'save_comment_post_by_sku'),
			));
		}

		function save_comment_post_by_sku($comment_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns) {
			if (empty($data_to_save)) {
				return;
			}

			$post_id = wc_get_product_id_by_sku($data_to_save);
			if (!$post_id) {
				return;
			}

			$commentarr = array();
			$commentarr['comment_ID'] = $comment_id;
			$commentarr['comment_post_ID'] = $post_id;
			wp_update_comment($commentarr);
		}

		function get_product_sku_for_column($post, $cell_key, $cell_args) {
			$parent_post = get_post($post->comment_post_ID);
			$value = $parent_post ? get_post_meta($parent_post->ID, '_sku', true) : '';
			return $value;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Comments_WooCommerce::$instance) {
				WP_Sheet_Editor_Comments_WooCommerce::$instance = new WP_Sheet_Editor_Comments_WooCommerce();
				WP_Sheet_Editor_Comments_WooCommerce::$instance->init();
			}
			return WP_Sheet_Editor_Comments_WooCommerce::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WP_Sheet_Editor_Comments_WooCommerce_Obj')) {

	function WP_Sheet_Editor_Comments_WooCommerce_Obj() {
		return WP_Sheet_Editor_Comments_WooCommerce::get_instance();
	}

}
add_action('vg_sheet_editor/initialized', 'WP_Sheet_Editor_Comments_WooCommerce_Obj');
