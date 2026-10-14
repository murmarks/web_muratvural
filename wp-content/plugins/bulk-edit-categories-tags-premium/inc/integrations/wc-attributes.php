<?php

if ( ! class_exists( 'WPSE_WC_Attributes_Sheet' ) ) {

	class WPSE_WC_Attributes_Sheet extends WPSE_Sheet_Factory {
		public $full_table_key = null;

		public function __construct() {
			global $wpdb;
			$this->full_table_key = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			add_filter( 'vg_sheet_editor/provider/custom_table/table_schema', array( $this, 'modify_table_schema' ) );
			parent::__construct(
				array(
					'fs_object'                         => wpsett_fs(),
					'post_type'                         => array( $this, 'get_taxonomies_and_labels' ),
					'register_default_taxonomy_columns' => false,
					'bootstrap_class'                   => 'WPSE_WC_Attributes_Spreadsheet_Bootstrap',
					'columns'                           => array( $this, 'get_columns' ),
				)
			);
			add_filter( 'vg_sheet_editor/provider/default_provider_key', array( $this, 'set_default_provider' ), 10, 2 );
			add_filter( 'vg_sheet_editor/acf/fields', array( $this, 'deactivate_acf_fields' ), 10, 2 );
			add_action( 'vg_sheet_editor/terms/before_merge', array( $this, 'transfer_variations_after_attribute_merge' ), 10, 3 );
			add_filter( 'vg_sheet_editor/advanced_filters/all_fields_groups', array( $this, 'add_fields_to_advanced_filters' ), 10, 2 );
			add_filter( 'vg_sheet_editor/provider/custom_table/get_rows_sql', array( $this, 'filter_rows_query_post_data' ), 10, 3 );
			add_action(
				'vg_sheet_editor/editor/before_init',
				array(
					$this,
					'register_toolbar_items__premium_only',
				)
			);
			add_action( 'wp_ajax_vgse_search_attribute_taxonomies', array( $this, 'search_attribute_taxonomies' ) );
			add_action( 'wp_ajax_vgse_merge_attributes', array( $this, 'merge_attributes__premium_only' ) );
		}

		public function _replace_product_attribute_array_keys( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep, $meta_key ) {
			global $wpdb;

			// Update products to use the new taxonomy key
			$product_attributes = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s", $meta_key, '%"' . $wpdb->esc_like( $attribute_taxonomy_to_remove ) . '"%' ), ARRAY_A );
			foreach ( $product_attributes as $product_attribute ) {
				$meta_value          = maybe_unserialize( $product_attribute['meta_value'] );
				$original_meta_value = $meta_value;
				if ( is_array( $meta_value ) && isset( $meta_value[ $attribute_taxonomy_to_remove ] ) ) {

					// Add the final attribute only if it doesn't exist already
					if ( ! isset( $meta_value[ $attribute_taxonomy_to_keep ] ) ) {
						$meta_value[ $attribute_taxonomy_to_keep ] = $meta_value[ $attribute_taxonomy_to_remove ];
						if ( isset( $meta_value[ $attribute_taxonomy_to_keep ]['name'] ) ) {
							$meta_value[ $attribute_taxonomy_to_keep ]['name'] = $attribute_taxonomy_to_keep;
						}
					}

					unset( $meta_value[ $attribute_taxonomy_to_remove ] );
				}

				if ( $meta_value !== $original_meta_value ) {
					$wpdb->update(
						$wpdb->postmeta,
						array(
							'meta_value' => serialize( $meta_value ),
						),
						array(
							'post_id'  => $product_attribute['post_id'],
							'meta_key' => $meta_key,
						)
					);
				}
			}
		}
		public function _replace_attribute_in_variations( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->postmeta,
				array(
					'meta_key' => 'attribute_' . $attribute_taxonomy_to_keep,
				),
				array(
					'meta_key' => 'attribute_' . $attribute_taxonomy_to_remove,
				)
			);
		}
		public function _replace_term_taxonomies( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->term_taxonomy,
				array(
					'taxonomy' => $attribute_taxonomy_to_keep,
				),
				array(
					'taxonomy' => $attribute_taxonomy_to_remove,
				)
			);
		}
		public function _merge_attributes__premium_only( $attributes_to_remove, $attribute_id_to_keep ) {
			global $wpdb;

			if ( ! $attribute_id_to_keep || is_wp_error( $attribute_id_to_keep ) ) {
				return false;
			}

			do_action( 'vg_sheet_editor/attributes/before_merge', $attributes_to_remove, $attribute_id_to_keep );

			$successfully_merged_ids    = array();
			$attribute_to_keep          = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d", $attribute_id_to_keep ), ARRAY_A );
			$attribute_name_to_keep     = $attribute_to_keep['attribute_name'];
			$attribute_taxonomy_to_keep = 'pa_' . $attribute_name_to_keep;
			foreach ( $attributes_to_remove as $attribute_to_remove ) {
				$attribute_id = $attribute_to_remove;
				if ( ! $attribute_id || $attribute_id === $attribute_id_to_keep ) {
					continue;
				}
				$attribute_name_to_remove     = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d", $attribute_id ) );
				$attribute_taxonomy_to_remove = 'pa_' . $attribute_name_to_remove;

				// Transfer terms to the final taxonomy
				$this->_replace_term_taxonomies( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep );

				// Update products to use the new taxonomy key
				$this->_replace_product_attribute_array_keys( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep, '_product_attributes' );

				// Update variations to use the new taxonomy key
				$this->_replace_attribute_in_variations( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep );

				// Update default attributes to use the new taxonomy key
				$this->_replace_product_attribute_array_keys( $attribute_taxonomy_to_remove, $attribute_taxonomy_to_keep, '_default_attributes' );

				// Merge terms
				$has_duplicate_terms = true;
				while ( $has_duplicate_terms ) {
					$results = $GLOBALS['wpse_taxonomy_terms_sheet']->merge_duplicate_terms( $attribute_taxonomy_to_keep );
					if ( empty( $results['all_duplicate_terms'] ) ) {
						$has_duplicate_terms = false;
					}
				}

				$wpdb->delete( $wpdb->prefix . 'woocommerce_attribute_taxonomies', array( 'attribute_id' => (int) $attribute_id ) );

				$successfully_merged_ids[] = $attribute_id;
			}
			self::clear_caches();
			do_action( 'vg_sheet_editor/attributes/after_merge', $successfully_merged_ids, $attribute_id_to_keep );

			return true;
		}

		public function merge_attributes__premium_only() {
			global $wpdb;
			if ( empty( $_REQUEST['nonce'] ) || empty( $_REQUEST['post_type'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'bep-nonce' ) || ! VGSE()->helpers->user_can_edit_post_type( $this->full_table_key ) || empty( $_REQUEST['vgse_attributes_source'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Request not allowed. Try again later.', VGSE()->textname ) ) );
			}
			if ( $_REQUEST['vgse_attributes_source'] !== 'duplicates' && ( empty( $_REQUEST['final_attribute'] ) || ! is_string( $_REQUEST['final_attribute'] ) ) ) {
				wp_send_json_error( array( 'message' => __( 'Please select the attribute that you want to keep.', VGSE()->textname ) ) );
			}
			$post_type          = VGSE()->helpers->sanitize_table_key( $_REQUEST['post_type'] );
			$attributes_source  = sanitize_text_field( $_REQUEST['vgse_attributes_source'] );
			$final_attribute_id = ! empty( $_REQUEST['final_attribute'] ) ? intval( $_REQUEST['final_attribute'] ) : 0;
			$page               = isset( $_REQUEST['page'] ) ? (int) $_REQUEST['page'] : 1;

			// Disable post actions to prevent conflicts with other plugins
			VGSE()->helpers->remove_all_post_actions( $post_type );

			if ( $attributes_source === 'individual' ) {

				$attributes_to_remove = array_map( 'intval', $_REQUEST['attributes_to_remove'] );
				if ( empty( $attributes_to_remove ) ) {
					wp_send_json_error( array( 'message' => __( 'Please select the attributes to remove.', VGSE()->textname ) ) );
				}
			} elseif ( $attributes_source === 'duplicates' ) {
				global $wpdb;

				$max_per_page = 20;
				$sql          = $wpdb->prepare(
					"SELECT attribute_label, COUNT(*) count, GROUP_CONCAT(attribute_id SEPARATOR ',') as attribute_ids FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
GROUP BY attribute_label 
HAVING count > 1 LIMIT %d",
					$max_per_page
				);

				$all_duplicate_attributes = $wpdb->get_results( $sql, ARRAY_A );
				$deleted                  = array();
				foreach ( $all_duplicate_attributes as $duplicate_attributes ) {
					$attribute_ids = array_map( 'intval', explode( ',', $duplicate_attributes['attribute_ids'] ) );

					$first_attribute_id = $attribute_ids[0];
					unset( $attribute_ids[0] );
					$deleted = array_merge( $deleted, $attribute_ids );
					$this->_merge_attributes__premium_only( $attribute_ids, $first_attribute_id );
				}

				wp_send_json_success(
					array(
						'message'                    => sprintf( __( '%s attributes merged.', VGSE()->textname ), count( $deleted ) ),
						'deleted'                    => $deleted,
						'query'                      => current_user_can( 'manage_options' ) ? $sql : null,
						'force_complete'             => empty( $all_duplicate_attributes ),
						'duplicate_attributes_found' => $all_duplicate_attributes,
					)
				);
			} elseif ( $attributes_source === 'search' ) {

				$get_rows_args = apply_filters(
					'vg_sheet_editor/attributes/merge/search_query/get_rows_args',
					array(
						'nonce'       => wp_create_nonce( 'bep-nonce' ),
						'post_type'   => $post_type,
						'filters'     => $_REQUEST['filters'],
						'paged'       => 1,
						'wpse_source' => 'merge_attributes',
					)
				);
				$base_query    = VGSE()->helpers->prepare_query_params_for_retrieving_rows( $get_rows_args );
				$base_query    = apply_filters( 'vg_sheet_editor/attributes/merge/posts_query', $base_query );

				$base_query['fields']                      = 'ids';
				$base_query['wpse_force_not_hierarchical'] = true;
				// When we search by keyword we use the post__in query arg, and
				// the provider returns all the posts from the post__in ignoring the pagination,
				// so we pass this flag to force the use of pagination
				$base_query['wpse_force_pagination'] = true;
				$per_page                            = ( ! empty( VGSE()->options ) && ! empty( VGSE()->options['be_posts_per_page_save'] ) ) ? (int) VGSE()->options['be_posts_per_page_save'] / 2 : 3;
				$base_query['posts_per_page']        = ( $per_page < 3 ) ? 3 : (int) $per_page;
				$editor                              = VGSE()->helpers->get_provider_editor( $post_type );
				VGSE()->current_provider             = $editor->provider;
				$query                               = $editor->provider->get_items( $base_query );
				$attributes_to_remove                = $query->posts;
			}
			if ( (int) $page === 1 && empty( $attributes_to_remove ) ) {
				wp_send_json_error( array( 'message' => __( 'attributes to remove not found.', VGSE()->textname ) ) );
			}

			if ( ! empty( $attributes_to_remove ) ) {
				$this->_merge_attributes__premium_only( $attributes_to_remove, $final_attribute_id );
			}
			wp_send_json_success(
				array(
					'message' => sprintf( __( '%s attributes merged.', VGSE()->textname ), count( $attributes_to_remove ) ),
					'deleted' => array_values( array_diff( array_unique( $attributes_to_remove ), array( $final_attribute_id ) ) ),
					'query'   => current_user_can( 'manage_options' ) && ! empty( $query ) ? $query : null,
				)
			);
		}

		public function search_attribute_taxonomies() {
			global $wpdb;
			if ( empty( $_REQUEST['post_type'] ) || empty( $_REQUEST['nonce'] ) || empty( $_REQUEST['search'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'bep-nonce' ) || ! VGSE()->helpers->user_can_edit_post_type( $this->full_table_key ) ) {
				wp_send_json_error( array( 'message' => __( 'Request not allowed. Try again later.', VGSE()->textname ) ) );
			}

			$search_term = sanitize_text_field( $_REQUEST['search'] );
			if ( ! empty( $search_term ) ) {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT attribute_id,attribute_label,attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label LIKE %s OR attribute_name LIKE %s", '%' . $wpdb->esc_like( $search_term ) . '%', '%' . $wpdb->esc_like( $search_term ) . '%' ), ARRAY_A );
			}
			$out = array();
			foreach ( $results as $result ) {
				$out[] = array(
					'id'   => (int) $result['attribute_id'],
					'text' => sanitize_text_field( $result['attribute_label'] ) . ' ( ' . sanitize_text_field( $result['attribute_name'] ) . ' )',
				);
			}

			wp_send_json_success( array( 'data' => $out ) );
		}

		/**
		 * Register toolbar item
		 */
		public function register_toolbar_items__premium_only( $editor ) {

			if ( strpos( $editor->args['provider'], 'woocommerce_attribute_taxonomies' ) === false ) {
				return;
			}
			$editor->args['toolbars']->register_item(
				'merge_attributes',
				array(
					'type'                  => 'button', // html | switch | button
					'content'               => __( 'Merge attributes', VGSE()->textname ),
					'id'                    => 'merge-attributes',
					'help_tooltip'          => __( 'Combine attribute taxonomies into one and automatically reassign the products and variations to use the final attribute.', VGSE()->textname ),
					'extra_html_attributes' => 'data-remodal-target="merge-attributes-modal"',
					'footer_callback'       => array( $this, 'render_merge_attributes_modal__premium_only' ),
				),
				$editor->args['provider']
			);
		}

		/**
		 * Render modal for merging terms
		 * @param str $post_type
		 * @return null
		 */
		public function render_merge_attributes_modal__premium_only( $post_type ) {
			$nonce = wp_create_nonce( 'bep-nonce' );
			include vgse_taxonomy_terms()->plugin_dir . '/views/merge-attributes-modal.php';
		}
		public function filter_rows_query_post_data( $sql, $args, $settings ) {
			$post_type = $settings['table_name'];
			if ( strpos( $post_type, 'woocommerce_attribute_taxonomies' ) === false ) {
				return $sql;
			}

			if ( empty( $args['wpse_original_filters'] ) || empty( $args['wpse_original_filters']['meta_query'] ) ) {
				return $sql;
			}
			$table_data_filters = wp_list_filter(
				$args['wpse_original_filters']['meta_query'],
				array(
					'source' => 'attribute_data',
				)
			);
			if ( empty( $table_data_filters ) ) {
				return $sql;
			}

			// Replace the ID field key with the real primary key for the search
			$primary_column_key = VGSE()->helpers->get_current_provider()->get_post_data_table_id_key( $post_type );
			foreach ( $table_data_filters as $index => $table_data_filter ) {
				if ( $table_data_filter['key'] === 'ID' ) {
					$table_data_filters[ $index ]['key'] = $primary_column_key;
				}
			}

			$raw_where = WP_Sheet_Editor_Advanced_Filters::get_instance()->_build_sql_wheres_for_data_table( $table_data_filters, 't' );
			if ( empty( $raw_where ) ) {
				return $sql;
			}

			$where = implode( ' AND ', $raw_where );
			if ( strpos( $sql, ' WHERE ' ) === false ) {
				$where = ' WHERE ' . $where;
			} else {
				$where = ' AND ' . $where;
			}
			$sql = str_replace( ' ORDER ', $where . ' ORDER ', $sql );
			return $sql;
		}

		public function add_fields_to_advanced_filters( $all_fields, $post_type ) {
			global $wpdb;
			if ( strpos( $post_type, 'woocommerce_attribute_taxonomies' ) === false ) {
				return $all_fields;
			}

			$data_fields                  = wp_list_pluck( $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_attribute_taxonomies;" ), 'Field' );
			$all_fields['attribute_data'] = $data_fields;

			return $all_fields;
		}

		public function transfer_variations_after_attribute_merge( $successfully_merged_ids, $term_id_to_keep, $taxonomy ) {
			global $wpdb;
			if ( empty( $successfully_merged_ids ) || ! taxonomy_exists( $taxonomy ) || strpos( $taxonomy, 'pa_' ) === false ) {
				return;
			}

			$final_term = get_term_by( 'term_id', $term_id_to_keep, $taxonomy );
			foreach ( $successfully_merged_ids as $deleted_term_id ) {
				$deleted_term = get_term_by( 'term_id', $deleted_term_id, $taxonomy );
				if ( empty( $deleted_term ) ) {
					continue;
				}
				$wpdb->update(
					$wpdb->postmeta,
					array(
						'meta_value' => $final_term->slug,
					),
					array(
						'meta_key'   => 'attribute_' . $taxonomy,
						'meta_value' => $deleted_term->slug,
					)
				);
			}
		}

		public function modify_table_schema( $schema ) {
			if ( strpos( $schema['table_name'], 'woocommerce_attribute_taxonomies' ) !== false ) {
				$schema['columns']['attribute_name']['type'] = 'slug';
			}
			return $schema;
		}

		public function after_full_core_init() {
			if ( ! $this->is_woocommerce_activated() ) {
				return;
			}
			parent::after_full_core_init();
			add_action( 'vg_sheet_editor/save_rows/after_saving_post', array( $this, 'row_updated_on_spreadsheet' ), 10, 4 );
			add_action( 'vg_sheet_editor/formulas/execute_formula/after_execution_on_field', array( $this, 'row_updated_with_formula' ), 10, 8 );
			add_action( 'vg_sheet_editor/formulas/execute_formula/after_sql_execution', array( $this, 'after_sql_formula_execution' ), 10, 3 );
			add_filter( 'vg_sheet_editor/terms/welcome_sheets_all', array( $this, 'show_sheet_in_welcome_page' ) );
			add_filter( 'vg_sheet_editor/terms/welcome_sheets', array( $this, 'show_sheet_in_welcome_page_as_enabled' ) );
		}

		public function is_woocommerce_activated() {
			return class_exists( 'WooCommerce' );
		}

		public function show_sheet_in_welcome_page_as_enabled( $sheets ) {
			global $wpdb;
			$sheets[] = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			return $sheets;
		}

		public function show_sheet_in_welcome_page( $sheets ) {
			global $wpdb;
			$sheets['post_types'][] = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			$sheets['labels'][]     = __( 'WooCommerce Attributes', vgse_taxonomy_terms()->textname );
			return $sheets;
		}

		public function after_sql_formula_execution( $column, $formula, $post_type ) {
			if ( strpos( $post_type, 'woocommerce_attribute_taxonomies' ) !== false ) {
				self::clear_caches();
			}
		}

		public static function clear_caches() {
			wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );
			delete_transient( 'wc_attribute_taxonomies' );
			if ( class_exists( 'WC_Cache_Helper' ) ) {
				WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
			}
		}

		public function row_updated_with_formula( $post_id, $initial_data, $modified_data, $column, $formula, $post_type, $cell_args, $spreadsheet_columns ) {
			if ( strpos( $post_type, 'woocommerce_attribute_taxonomies' ) !== false ) {
				self::clear_caches();
			}
		}

		public function row_updated_on_spreadsheet( $product_id, $item, $data, $post_type ) {
			if ( strpos( $post_type, 'woocommerce_attribute_taxonomies' ) !== false ) {
				self::clear_caches();
			}
		}

		public function deactivate_acf_fields( $fields, $post_type ) {
			if ( strpos( $post_type, 'woocommerce_attribute_taxonomies' ) !== false ) {
				$fields = array();
			}
			return $fields;
		}

		public function set_default_provider( $provider_class_key, $provider ) {
			if ( strpos( $provider, 'woocommerce_attribute_taxonomies' ) !== false ) {
				$provider_class_key = 'wc_attributes';
			}
			return $provider_class_key;
		}

		public function get_columns() {

		}

		public function get_taxonomies_and_labels() {
			global $wpdb;
			$out = array(
				'post_types' => array( $wpdb->prefix . 'woocommerce_attribute_taxonomies' ),
				'labels'     => array( __( 'WooCommerce Attributes', vgse_taxonomy_terms()->textname ) ),
			);

			return $out;
		}

	}

	$GLOBALS['wpse_wc_attributes_sheet'] = new WPSE_WC_Attributes_Sheet();
}
