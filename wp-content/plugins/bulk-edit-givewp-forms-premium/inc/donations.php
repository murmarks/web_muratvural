<?php defined( 'ABSPATH' ) || exit;

if (!class_exists('WPSE_GiveWP_Donations')) {

	class WPSE_GiveWP_Donations extends WPSE_Sheet_Factory {

		function __construct() {
			parent::__construct(array(
				'fs_object' => wpsegwf_fs(),
				'post_type' => array('give_payment'),
				'post_type_label' => array('Donations'),
				'serialized_columns' => array(), // column keys
				'allowed_columns' => array(),
				'remove_columns' => array(
					'post_title',
					'post_name',
					'view_post',
					'open_wp_editor',
					'menu_order',
					'_give_payment_donor_id'
				), // column keys
			));

			add_action('vg_sheet_editor/add_new_posts/after_all_posts_created', array($this, 'add_required_meta'), 10, 2);
			add_filter('vgse_sheet_editor/provider/post/meta_table_name', array($this, 'use_different_meta_table'), 10, 2);
			add_filter('vgse_sheet_editor/provider/post/meta_table_post_id_key', array($this, 'use_different_meta_id_column'), 10, 2);
			add_action('vg_sheet_editor/editor/register_columns', array($this, 'register_columns'), 60);
			add_filter('vg_sheet_editor/provider/post/statuses', array($this, 'filter_donation_statuses'), 10, 2);
		}

		function register_columns($editor) {
			$post_types = $this->get_prop('post_type');
			if (!in_array($editor->args['provider'], $post_types)) {
				return;
			}
			$columns = array();
			$columns['_give_anonymous_donation'] = array(
				'title' => __('Anonymous Donation:', 'give'),
				'formatted' => array(
					'type' => 'checkbox',
					'checkedTemplate' => '1',
					'uncheckedTemplate' => '0',
				),
				'default_value' => '0',
			);
			$columns['_give_payment_currency'] = array(
				'title' => __('Payment Currency:', 'give'),
				'formatted' => array('editor' => 'select', 'selectOptions' => give_get_currencies()),
			);
			$columns['_give_payment_gateway'] = array(
				'formatted' => array('editor' => 'select', 'selectOptions' => array_keys(give_get_enabled_payment_gateways())),
			);
			$columns['_give_payment_donor_email'] = array(
				'title' => __('Donor email', 'give'),
				'save_value_callback' => array($this, 'save_donor_email'),
				'value_type' => 'email',
			);
			$columns['_give_payment_form_title'] = array(
				'title' => __('Form title', 'give'),
				'save_value_callback' => array($this, 'save_form_title'),
			);

			foreach ($columns as $column_key => $column) {
				$editor->args['columns']->register_item($column_key, $editor->args['provider'], $column, true);
			}
		}

		function save_form_title($post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns) {
			global $wpdb;
			$form_id = '';
			if (!empty($data_to_save)) {
				$form = VGSE()->helpers->get_page_by_title($data_to_save, 'give_forms');
				if (is_object($form)) {
					$form_id = $form->ID;
				}
			}
			update_post_meta($post_id, $cell_key, $data_to_save);
			update_post_meta($post_id, '_give_payment_form_id', $form_id);
		}

		function save_donor_email($post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns) {
			global $wpdb;
			$donor_id = '';
			if (!empty($data_to_save)) {
				$donor_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}give_donors WHERE email = %s ", $data_to_save));
			}
			update_post_meta($post_id, $cell_key, $data_to_save);
			update_post_meta($post_id, '_give_payment_donor_id', $donor_id ? $donor_id : '');
		}

		function filter_donation_statuses($post_statuses, $post_type) {

			if (!in_array($post_type, $this->post_type)) {
				return $post_statuses;
			}

			$post_statuses = array(
				'publish' => __('Completed', vgse_givewp()->textname),
				'processing' => __('Processing', 'give'),
				'abandoned' => __('Abandoned', 'give'),
				'cancelled' => __('Cancelled', 'give'),
				'revoked' => __('Revoked', 'give'),
				'failed' => __('Failed', 'give'),
				'refunded' => __('Refunded', 'give'),
			);

			return $post_statuses;
		}

		function use_different_meta_id_column($id_column, $post_type) {
			if (!in_array($post_type, $this->post_type)) {
				return $id_column;
			}

			return 'donation_id';
		}

		function use_different_meta_table($table_name, $post_type) {
			global $wpdb;
			if (!in_array($post_type, $this->post_type)) {
				return $table_name;
			}

			return $wpdb->prefix . 'give_donationmeta';
		}

		function add_required_meta($new_posts_ids, $post_type) {
			if (!in_array($post_type, $this->post_type)) {
				return $new_posts_ids;
			}
			if (!empty($new_posts_ids)) {
				$required_meta = array(
					"_give_donation_company",
					"_give_payment_total",
					"_give_payment_currency",
					"_give_donor_billing_first_name",
					"_give_donor_billing_last_name",
					"_give_payment_gateway",
					"_give_payment_form_title",
					"_give_payment_form_id",
					"_give_payment_price_id",
					"_give_payment_donor_email",
					"_give_payment_donor_title_prefix",
					"_give_payment_donor_ip",
					"_give_payment_purchase_key",
					"_give_payment_mode",
					"_give_payment_donor_id",
					"_give_current_url",
					"_give_anonymous_donation",
					"_give_completed_date",
				);
				foreach ($new_posts_ids as $post_id) {
					foreach ($required_meta as $meta_key) {
						update_post_meta($post_id, $meta_key, '');
					}
				}
			}
			return $new_posts_ids;
		}

	}

	new WPSE_GiveWP_Donations();
}