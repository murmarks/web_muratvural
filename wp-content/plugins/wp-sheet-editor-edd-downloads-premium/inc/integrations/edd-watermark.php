<?php

if (!class_exists('WPSE_EDD_Watermark')) {

	class WPSE_EDD_Watermark {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			add_filter('edd_img_wtm_is_download', array($this, 'is_file_for_watermark'));
		}

		function is_file_for_watermark($allowed) {
			$referer = wp_get_referer();
			if (strpos($referer, 'vgse-bulk-edit-download') !== false) {
				$allowed = true;
			}
			return $allowed;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPSE_EDD_Watermark::$instance) {
				WPSE_EDD_Watermark::$instance = new WPSE_EDD_Watermark();
				WPSE_EDD_Watermark::$instance->init();
			}
			return WPSE_EDD_Watermark::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPSE_EDD_Watermark_Obj')) {

	function WPSE_EDD_Watermark_Obj() {
		return WPSE_EDD_Watermark::get_instance();
	}

}
WPSE_EDD_Watermark_Obj();
