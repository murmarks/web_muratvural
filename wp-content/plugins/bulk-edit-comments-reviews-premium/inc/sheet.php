<?php defined( 'ABSPATH' ) || exit;
if (!class_exists('WPSE_Comments_Sheet')) {

	class WPSE_Comments_Sheet extends WPSE_Sheet_Factory {

		var $key = 'comments';

		function __construct() {
			$allowed_columns = array();

			if (!wpsecr_fs()->can_use_premium_code__premium_only()) {
				$allowed_columns = array(
				);
			}

			parent::__construct(array(
				'fs_object' => wpsecr_fs(),
				'post_type' => array('comments'),
				'post_type_label' => array(__('Comments')),
				'serialized_columns' => array(), // column keys
				'register_default_taxonomy_columns' => false,
				'bootstrap_class' => 'WPSE_Comments_Spreadsheet_Bootstrap',
				'columns' => array($this, 'get_columns'),
				'allowed_columns' => $allowed_columns,
				'remove_columns' => array(
				), // column keys
			));

			add_filter('vg_sheet_editor/provider/default_provider_key', array($this, 'set_default_provider_for_comments'), 10, 2);
			add_filter('vg_sheet_editor/import/find_post_id', array($this, 'find_existing_comment_by_slug_for_import'), 10, 6);
			add_action('vg_sheet_editor/import/before_existing_wp_check_message', array($this, 'add_wp_check_message_for_import'));
			add_filter('vg_sheet_editor/import/wp_check/available_columns_options', array($this, 'filter_wp_check_options_for_import'), 10, 2);
			add_action('vg_sheet_editor/filters/after_fields', array($this, 'add_filters_fields'), 10, 2);
			add_filter('vg_sheet_editor/load_rows/wp_query_args', array($this, 'filter_posts'), 10, 2);
			add_filter('vg_sheet_editor/filters/sanitize_request_filters', array($this, 'register_custom_filters'), 10, 2);
			add_filter('vg_sheet_editor/export/final_rows', array($this, 'add_old_platform_id_column'), 10, 3);
			add_filter('vg_sheet_editor/export/final_headers', array($this, 'add_old_platform_id_column_title'), 10, 3);
			add_action('admin_menu', array($this, 'register_menu'));
			add_filter('vg_sheet_editor/save_rows/row_data_before_save', array($this, 'modify_parent_id_for_import'), 10, 3);
			add_filter('vg_sheet_editor/load_rows/full_output', array($this, 'sort_rows_after_export'), 20, 4);
			add_action('comments_clauses', array($this, 'filter_comments_query__premium_only'), 10, 2);
			add_filter('vg_sheet_editor/advanced_filters/all_fields_groups', array($this, 'add_comment_fields_to_advanced_filters__premium_only'), 10, 2);
		}

		function filter_comments_query__premium_only($query_vars, $wp_comments_query) {
			global $wpdb;

			if (empty($wp_comments_query->query_vars['wpse_original_filters']) || empty($wp_comments_query->query_vars['wpse_original_filters']['meta_query'])) {
				return $query_vars;
			}
			$comment_data_filters = wp_list_filter($wp_comments_query->query_vars['wpse_original_filters']['meta_query'], array(
				'source' => 'comment_data'
			));
			if (empty($comment_data_filters)) {
				return $query_vars;
			}

			$where = WP_Sheet_Editor_Advanced_Filters::get_instance()->_build_sql_wheres_for_data_table($comment_data_filters, $wpdb->comments);
			if (empty($where)) {
				return $query_vars;
			}
			$query_vars['where'] .= ' AND ' . implode(' AND ', $where);
			return $query_vars;
		}

		function add_comment_fields_to_advanced_filters__premium_only($all_fields, $post_type) {
			global $wpdb;
			if ($post_type !== 'comments') {
				return $all_fields;
			}

			$data_fields = wp_list_pluck($wpdb->get_results("SHOW COLUMNS FROM $wpdb->comments;"), 'Field');
			$all_fields['comment_data'] = $data_fields;

			return $all_fields;
		}

		// The CSV file must contain the child comments at the end of the file because 
		// Child comments are not imported correctly when they appear before the parent row
		function sort_rows_after_export($out, $wp_query_args, $spreadsheet_columns, $clean_data) {
			if (empty($out['export_complete']) || $wp_query_args['post_type'] !== 'comments' || empty($spreadsheet_columns['comment_parent'])) {
				return $out;
			}


			$file_path = WPSE_CSV_API_Obj()->exports_dir . $out['export_file_name'] . '.csv';
			$rows = WPSE_CSV_API_Obj()->get_rows($file_path, ',', false, PHP_INT_MAX);
			$csv_rows = $rows['rows'];
			$parent_column_title = $spreadsheet_columns['comment_parent']['title'];
			$rows_without_parent = array();
			$rows_with_parent = array();
			
			foreach ($csv_rows as $index => $row) {
				if (empty($row[$parent_column_title])) {
					$rows_without_parent[] = $row;
				} else {
					$rows_with_parent[] = $row;
				}
			}

			// Sort by parent ID to make sure that the required parent is always present before the row
			usort($rows_with_parent, function($a, $b) use($parent_column_title) {
				return $a[$parent_column_title] - $b[$parent_column_title];
			});

			$final_headers = array_keys(current($csv_rows));
			unlink($file_path);
			$final_rows = array_merge($rows_without_parent, $rows_with_parent);
			WPSE_CSV_API_Obj()->_array_to_csv($final_rows, $file_path, implode(',', $final_headers));
			return $out;
		}

		function modify_parent_id_for_import($item, $post_id, $post_type) {
			global $wpdb;
			if (!empty($item['source_site_id']) && !empty($item['comment_parent']) && $post_type === 'comments') {
				$item['comment_parent'] = (int) $wpdb->get_var($wpdb->prepare("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'source_site_id' AND meta_value = %d", $item['comment_parent']));
			}
			return $item;
		}

		function add_old_platform_id_column_title($column_titles, $clean_data, $wp_query_args) {
			if ($wp_query_args['post_type'] === 'comments' && !in_array(__('Source site id', vgse_comments()->textname), $column_titles, true)) {
				$column_titles[] = __('Source site id', vgse_comments()->textname);
			}
			return $column_titles;
		}

		function add_old_platform_id_column($cleaned_rows, $clean_data, $wp_query_args) {
			if ($wp_query_args['post_type'] === 'comments') {
				$first_row = current($cleaned_rows);
				foreach ($cleaned_rows as $index => $row) {
					if (empty($first_row['source_site_id'])) {
						$cleaned_rows[$index]['source_site_id'] = $row['ID'];
					}
				}
			}
			return $cleaned_rows;
		}

		function register_menu() {
			add_comments_page(__('Bulk edit', vgse_comments()->textname), __('Bulk edit comments', vgse_comments()->textname), 'edit_posts', VGSE()->helpers->get_editor_url($this->key), null);
		}

		function register_custom_filters($sanitized_filters, $dirty_filters) {

			if (isset($dirty_filters['comment_status'])) {
				$sanitized_filters['comment_status'] = array_filter(array_map('sanitize_key', $dirty_filters['comment_status']));
			}
			if (isset($dirty_filters['comments_author'])) {
				$sanitized_filters['comments_author'] = array_filter(array_map('intval', $dirty_filters['comments_author']));
			}
			if (isset($dirty_filters['comments_post_type'])) {
				$sanitized_filters['comments_post_type'] = VGSE()->helpers->sanitize_table_key($dirty_filters['comments_post_type']);
			}
			if (isset($dirty_filters['comment_type'])) {
				$sanitized_filters['comment_type'] = sanitize_text_field($dirty_filters['comment_type']);
			}
			return $sanitized_filters;
		}

		/**
		 * Apply filters to wp-query args
		 * @param array $query_args
		 * @param array $data
		 * @return array
		 */
		function filter_posts($query_args, $data) {
			if (!empty($data['filters'])) {
				$filters = WP_Sheet_Editor_Filters::get_instance()->get_raw_filters($data);

				if (!empty($filters['comments_post_type'])) {
					$query_args['comments_post_type'] = VGSE()->helpers->sanitize_table_key($filters['comments_post_type']);
				}
				if (!empty($filters['comment_status'])) {
					$query_args['comment_status'] = array_filter(array_map('sanitize_key', $filters['comment_status']));
				}
				if (!empty($filters['comment_type'])) {
					$query_args['type'] = sanitize_text_field($filters['comment_type']);
				}
				if (!empty($filters['comments_author'])) {
					$query_args['comments_author'] = array_filter(array_map('intval', $filters['comments_author']));
				}
			}

			return $query_args;
		}

		function add_filters_fields($current_post_type, $filters) {
			global $wpdb;
			if ($current_post_type !== $this->key) {
				return;
			}
			$post_types_with_comments = get_post_types_by_support('comments');
			?>
			<li>
				<label><?php _e('Post type', 'vg_sheet_editor' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php _e('Find comments created for all posts in a post type. I.e. Find comments left on posts, or reviews left on products', vgse_comments()->textname); ?>">( ? )</a></label>
				<select name="comments_post_type">
					<option value="">--</option>
					<?php
					foreach ($post_types_with_comments as $post_type) {
						$post_type_label = VGSE()->helpers->get_post_type_label($post_type);
						?>
						<option value="<?php echo esc_attr($post_type); ?>"><?php echo esc_html($post_type_label . ' (' . $post_type . ')'); ?></option>
					<?php }
					?>
				</select>
			</li>
			<li>
				<label><?php _e('Comment status', vgse_comments()->textname); ?></label>
				<select name="comment_status[]" multiple data-placeholder="<?php _e('Select...', vgse_comments()->textname); ?>" class="select2">
					<option value=""><?php _e('All'); ?></option>
					<option value="hold"><?php _e('Pending'); ?></option>
					<option value="approve"><?php _e('Approved'); ?></option>
				</select>
			</li>
			<li>
				<label><?php _e('Post author', vgse_comments()->textname); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php esc_attr_e('Example: Find comments left on posts created by an author or reviews left on the events published by a specific user', vgse_comments()->textname); ?>">( ? )</a></label>
				<select data-placeholder="<?php _e( 'Enter a username or email...', 'vg_sheet_editor' ); ?>" name="comments_author[]" class="select2"  multiple data-remote="true" data-action="vgse_find_users_by_keyword_for_select2" data-min-input-length="4"></select>
			</li>
			<li>
				<label><?php _e('Comment type', vgse_comments()->textname); ?></label>
				<select name="comment_type">
					<option value=""><?php _e('Comment'); ?></option>
					<?php
					$types = $wpdb->get_col("SELECT comment_type FROM $wpdb->comments WHERE comment_type != '' GROUP BY comment_type");
					if (!empty($types) && is_array($types)) {
						$translated_comment_types = array(
							'order_note' => __('Order notes', 'woocommerce'),
							'review' => __('Reviews', 'woocommerce'),
							'edd_payment_note' => __('Payment Notes', 'edd'),
						);
						foreach ($types as $type) {
							$comment_type_label = isset($translated_comment_types[$type]) ? $translated_comment_types[$type] : $type;
							?>
							<option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($comment_type_label); ?></option>
							<?php
						}
					}
					?>
				</select>
			</li>
			<?php
		}

		function filter_wp_check_options_for_import($columns, $post_type_key) {

			if ($post_type_key !== $this->key) {
				return $columns;
			}
			$columns = array(
				'ID' => $columns['ID'],
				'source_site_id' => $columns['source_site_id'],
			);
			return $columns;
		}

		function add_wp_check_message_for_import($post_type_key) {

			if ($post_type_key !== $this->key) {
				return;
			}
			?>
			<style>.field-find-existing-columns .wp-check-message { display: none; }</style>
			<p class="wp-custom-check-message"><?php _e('We find items that have the same ID in the CSV and the WP Field.<br>Please select the CSV column that contains the comment ID.', vgse_comments()->textname); ?></p>
			<?php
		}

		function find_existing_comment_by_slug_for_import($comment_id, $row, $post_type_key, $meta_query, $writing_type, $check_wp_fields) {
			global $wpdb;
			if ($post_type_key === $this->key) {
				if (!empty($row['ID']) && in_array('ID', $check_wp_fields, true)) {
					$comment_id = (int) $row['ID'];
				} elseif (!empty($row['source_site_id']) && in_array('source_site_id', $check_wp_fields, true)) {
					$comment_id = (int) $wpdb->get_var($wpdb->prepare("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'source_site_id' AND meta_value = %d", $row['source_site_id']));
				}
			}
			return $comment_id;
		}

		function set_default_provider_for_comments($provider_class_key, $provider) {
			if ($provider === 'comments') {
				$provider_class_key = 'comments';
			}
			return $provider_class_key;
		}

		function get_columns() {
			
		}

	}

	$GLOBALS['wpse_comments_sheet'] = new WPSE_Comments_Sheet();
}