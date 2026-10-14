<?php

if (!class_exists('WPSE_EDD_All_Access')) {

	class WPSE_EDD_All_Access {

		static private $instance = false;
		var $post_type = 'download';

		private function __construct() {
			
		}

		function init() {
			add_action('vg_sheet_editor/initialized', array($this, 'after_core_init'));
		}

		function after_core_init() {
			if (!class_exists('EDD_All_Access')) {
				return;
			}
			add_action('vg_sheet_editor/editor/register_columns', array($this, 'register_columns'));
			add_filter('vg_sheet_editor/provider/post/update_item_meta', array($this, 'filter_cell_data_for_saving'), 10, 3);
			add_filter('vg_sheet_editor/provider/post/get_item_meta', array($this, 'filter_cell_data_for_readings'), 10, 5);
			add_filter('vg_sheet_editor/serialized_addon/column_settings', array($this, 'filter_serialized_column_settings'), 10, 5);
		}

		function filter_serialized_column_settings($settings, $first_set_keys, $field, $key, $post_type) {

			if ($post_type !== $this->post_type) {
				return $settings;
			}
			if (strpos($key, 'all_access_duration_unit') !== false) {
				$settings['formatted']['editor'] = 'select';
				$settings['formatted']['selectOptions'] = array('never', 'year', 'month', 'week', 'day', 'edd_recurring');
			}
			if (strpos($key, '_edd_all_access_receipt_settings_show_link') !== false) {
				$settings['formatted']['editor'] = 'select';
				$settings['formatted']['selectOptions'] = array('show_link', 'hide_link');
			}
			if (strpos($key, 'all_access_download_limit_time_period') !== false) {
				$settings['formatted']['editor'] = 'select';
				$settings['formatted']['selectOptions'] = array('per_day', 'per_week', 'per_month', 'per_year', 'per_period');
			}
			return $settings;
		}

		function filter_cell_data_for_readings($value, $id, $key, $single, $context) {
			if ($context !== 'read' || get_post_type($id) !== $this->post_type) {
				return $value;
			}
			if ((strpos($key, 'all_access_categories') !== false || strpos($key, 'all_access_included_price_ids') !== false) && $value) {
				$value = implode(', ', $value);
			}

			return $value;
		}

		function filter_cell_data_for_saving($new_value, $id, $key) {
			if (get_post_type($id) !== $this->post_type) {
				return $new_value;
			}

			if ((strpos($key, 'all_access_categories') !== false || strpos($key, 'all_access_included_price_ids') !== false) && $new_value) {
				$value = array_map('trim', explode(',', $value));
			}

			return $new_value;
		}

		/**
		 * Register spreadsheet columns
		 */
		function register_columns($editor) {
			$post_type = $this->post_type;

			if ($editor->provider->key === 'user') {
				return;
			}

			new WP_Sheet_Editor_Serialized_Field(array(
				'sample_field_key' => '_edd_all_access_settings',
				'sample_field' => array(
					'all_access_categories' => '',
					'all_access_duration_number' => '',
					'all_access_duration_unit' => '',
					'all_access_download_limit' => '',
					'all_access_download_limit_time_period' => '',
					'all_access_number_of_price_ids' => '',
					'all_access_included_price_ids' => ''
				),
				'column_width' => 175,
				'column_title_prefix' => __('EDD', vgse_edd_downloads()->textname), // to remove the field key from the column title
				'level' => 7,
				'allowed_post_types' => array($post_type),
				'is_single_level' => true,
				'allow_in_wc_product_variations' => false,
			));
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == WPSE_EDD_All_Access::$instance) {
				WPSE_EDD_All_Access::$instance = new WPSE_EDD_All_Access();
				WPSE_EDD_All_Access::$instance->init();
			}
			return WPSE_EDD_All_Access::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPSE_EDD_All_Access_Obj')) {

	function WPSE_EDD_All_Access_Obj() {
		return WPSE_EDD_All_Access::get_instance();
	}

	WPSE_EDD_All_Access_Obj();
}