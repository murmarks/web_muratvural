<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Taxonomy_Terms_Sheet' ) ) {

	class WPSE_Taxonomy_Terms_Sheet extends WPSE_Sheet_Factory {

		var $parent_id_mapping = array();

		public function __construct() {
			$allowed_columns = array();

			if ( ! wpsett_fs()->can_use_premium_code__premium_only() ) {
				$allowed_columns = array();
			}

			parent::__construct(
				array(
					'fs_object'                         => wpsett_fs(),
					'post_type'                         => array( $this, 'get_taxonomies_and_labels' ),
					'post_type_label'                   => '',
					'serialized_columns'                => array(), // column keys
					'register_default_taxonomy_columns' => false,
					'bootstrap_class'                   => 'WPSE_Taxonomy_Terms_Spreadsheet_Bootstrap',
					'columns'                           => array( $this, 'get_columns' ),
					'allowed_columns'                   => $allowed_columns,
					'remove_columns'                    => array(), // column keys
					'allow_to_enable_individual_sheets' => wpsett_fs()->can_use_premium_code__premium_only(),
				)
			);

			add_filter( 'vg_sheet_editor/provider/default_provider_key', array( $this, 'set_default_provider_for_taxonomies' ), 10, 2 );

			add_filter( 'vg_sheet_editor/provider/term/update_item_meta', array( $this, 'filter_cell_data_for_saving' ), 10, 3 );
			add_filter( 'vg_sheet_editor/provider/term/get_item_meta', array( $this, 'filter_cell_data_for_readings' ), 10, 5 );
			add_filter( 'vg_sheet_editor/provider/term/get_item_data', array( $this, 'filter_cell_data_for_readings' ), 10, 6 );
			add_filter( 'vg_sheet_editor/handsontable/custom_args', array( $this, 'enable_row_sorting' ), 10, 2 );
			add_action( 'vg_sheet_editor/after_enqueue_assets', array( $this, 'register_assets' ) );
			add_action( 'wp_ajax_woocommerce_term_ordering', array( $this, 'woocommerce_term_ordering' ), 1 );
			add_filter( 'vg_sheet_editor/columns/blacklisted_columns', array( $this, 'blacklist_private_columns' ), 10, 2 );
			add_filter( 'vg_sheet_editor/import/find_post_id', array( $this, 'find_existing_term_by_slug_for_import' ), 10, 6 );
			add_action( 'vg_sheet_editor/import/before_existing_wp_check_message', array( $this, 'add_wp_check_message_for_import' ) );
			add_filter( 'vg_sheet_editor/import/wp_check/available_columns_options', array( $this, 'filter_wp_check_options_for_import' ), 10, 2 );
			add_filter( 'vg_sheet_editor/welcome_url', array( $this, 'filter_welcome_url' ) );
			add_action( 'vg_sheet_editor/filters/after_fields', array( $this, 'add_filters_fields' ), 10, 2 );
			add_filter( 'vg_sheet_editor/load_rows/wp_query_args', array( $this, 'filter_posts' ), 10, 2 );
			add_filter( 'vg_sheet_editor/filters/sanitize_request_filters', array( $this, 'register_custom_filters' ), 10, 2 );
			add_filter( 'terms_clauses', array( $this, 'search_by_multiple_parents' ), 10, 3 );

			if ( wpsett_fs()->can_use_premium_code__premium_only() ) {
				// Register toolbar button to enable the display of variations and create variations
				add_action(
					'vg_sheet_editor/editor/before_init',
					array(
						$this,
						'register_toolbar_items__premium_only',
					)
				);
				add_action( 'wp_ajax_vgse_merge_terms', array( $this, 'merge_terms__premium_only' ) );

				add_filter( 'vg_sheet_editor/advanced_filters/all_fields_groups', array( $this, 'add_fields_to_advanced_filters__premium_only' ), 10, 2 );
				add_filter( 'terms_clauses', array( $this, 'add_search_by_description_query__premium_only' ), 10, 3 );
				add_filter( 'terms_clauses', array( $this, 'add_search_by_name_slug_query__premium_only' ), 11, 3 );
				add_filter( 'vg_sheet_editor/import/rows_before_find_existing_id', array( $this, 'fix_hierarchy_before_import__premium_only' ), 10, 2 );
				add_filter( 'vg_sheet_editor/import/save_rows_args', array( $this, 'fix_parent_id_before_import__premium_only' ) );
				add_action( 'vg_sheet_editor/after_init', array( $this, 'after_init__premium_only' ) );
				add_filter( 'vg_sheet_editor/options_page/options', array( $this, 'add_settings_page_options__premium_only' ) );
				add_filter( 'vg_sheet_editor/save_rows/row_data_before_save', array( $this, 'save_core_term_data_first' ), 10, 4 );
			}
		}

		/**
		 * When we're creating new terms, we need to save the WP core data first, so we get
		 * the new term ID and use that to save the other columns
		 *
		 * @param  array $item
		 * @param  int $term_id
		 * @param  string $post_type
		 * @param  array $spreadsheet_columns
		 * @return array|WP_Error
		 */
		public function save_core_term_data_first( $item, $term_id, $post_type, $spreadsheet_columns ) {
			if ( VGSE()->helpers->get_current_provider()->key !== 'term' || $term_id !== PHP_INT_MAX ) {
				return $item;
			}

			$core_column_keys = array( 'name', 'slug', 'description', 'parent', 'taxonomy' );
			$term_data        = array_intersect_key( $item, array_flip( $core_column_keys ) );

			if ( empty( $term_data ) ) {
				return $item;
			}

			$term_data['ID'] = $item['ID'];
			$term_id         = VGSE()->helpers->get_current_provider()->update_item_data( $term_data, true );
			if ( is_wp_error( $term_id ) ) {
				return $term_id;
			}
			$item_without_core_data = array_diff_key( $item, array_flip( $core_column_keys ) );

			$item_without_core_data['ID'] = $term_id;
			return $item_without_core_data;
		}

		/**
		 * Add fields to options page
		 * @param array $sections
		 * @return array
		 */
		function add_settings_page_options__premium_only( $sections ) {
			$sections['misc']['fields'][] = array(
				'id'    => 'taxonomy_sheets_keywords',
				'type'  => 'text',
				'title' => __( 'Taxonomies: Only register spreadsheets for taxonomies containing these keywords', 'vg_sheet_editor' ),
				'desc'  => __( 'By default, we register a spreadsheet for every taxonomy, but this can be very slow if you have hundreds of taxonomies, so you can use this option to only register taxonomies containing a prefix or keyword. You can enter multiple keywords separated with commas. ', 'vg_sheet_editor' ),
			);
			return $sections;
		}

		public function after_init__premium_only() {
			global $wpdb;

			if ( ! empty( $_GET['wpse_recount_terms'] ) && VGSE()->helpers->user_can_manage_options() ) {

				$taxonomy_key = VGSE()->helpers->sanitize_table_key( $_GET['wpse_recount_terms'] );
				if ( ! taxonomy_exists( $taxonomy_key ) ) {
					wp_safe_redirect( esc_url( remove_query_arg( 'wpse_recount_terms' ) ) );
					exit();
				}
				if ( class_exists( 'WooCommerce' ) && $taxonomy_key === 'product_cat' ) {
					$product_cats = get_terms(
						'product_cat',
						array(
							'hide_empty'             => false,
							'fields'                 => 'id=>parent',
							'update_term_meta_cache' => false,
						)
					);
					_wc_term_recount( $product_cats, get_taxonomy( 'product_cat' ), true, false );
				} elseif ( class_exists( 'WooCommerce' ) && $taxonomy_key === 'product_tag' ) {
					$product_tags = get_terms(
						'product_tag',
						array(
							'hide_empty'             => false,
							'fields'                 => 'id=>parent',
							'update_term_meta_cache' => false,
						)
					);
					_wc_term_recount( $product_tags, get_taxonomy( 'product_tag' ), true, false );
				} else {
					$term_taxonomy_ids = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s", $taxonomy_key ) ) );
					if ( $term_taxonomy_ids ) {
						_update_post_term_count( $term_taxonomy_ids, get_taxonomy( $taxonomy_key ) );
					}
				}
				wp_safe_redirect( esc_url( remove_query_arg( 'wpse_recount_terms' ) ) );
				exit();
			}
		}

		public function fix_parent_id_before_import__premium_only( $save_settings ) {
			$post_type = $save_settings['post_type'];

			if ( ! empty( $this->parent_id_mapping[ $post_type ] ) ) {
				$rows             = $save_settings['data'];
				$terms_with_names = array_map( 'html_entity_decode', wp_list_pluck( $rows, 'original_standarized_name' ) );
				foreach ( $this->parent_id_mapping[ $post_type ] as $hierarchy => $term_id ) {
					$parent_will_be_imported = array_search( $hierarchy, $terms_with_names );
					if ( $parent_will_be_imported !== false ) {
						$rows[ $parent_will_be_imported ]['ID'] = $term_id;
					}
				}
				$save_settings['data'] = $rows;
			}

			return $save_settings;
		}

		public function fix_hierarchy_before_import__premium_only( $rows, $post_type ) {
			$first_row = current( $rows );
			if ( taxonomy_exists( $post_type ) && isset( $first_row['name'] ) ) {
				$this->parent_id_mapping[ $post_type ] = array();

				foreach ( $rows as $row_index => $row ) {
					$name = html_entity_decode( $row['name'] );
					if ( preg_match( '/(\:|>)/', $name ) ) {
						$hierarchy_terms                                 = array_map( 'trim', explode( '>', str_replace( ':', '>', $name ) ) );
						$rows[ $row_index ]['original_standarized_name'] = implode( '>', $hierarchy_terms );
						if ( count( $hierarchy_terms ) < 2 ) {
							continue;
						}
						$child_name = array_pop( $hierarchy_terms );
						$hierarchy  = implode( '>', $hierarchy_terms );
						$ids        = VGSE()->data_helpers->prepare_post_terms_for_saving( $hierarchy, $post_type );

						if ( empty( $ids ) ) {
							continue;
						}

						$parent_term_id                                      = end( $ids );
						$this->parent_id_mapping[ $post_type ][ $hierarchy ] = $parent_term_id;

						$term_exists_raw = get_terms(
							array(
								'taxonomy'               => $post_type,
								'name'                   => $child_name,
								'parent'                 => $parent_term_id,
								'fields'                 => 'ids',
								'hide_empty'             => false,
								'update_term_meta_cache' => false,
							)
						);
						if ( ! is_wp_error( $term_exists_raw ) && ! empty( $term_exists_raw ) ) {
							// Commented out because it was removing the parent rows and some columns of the parent weren't being saved
							// unset( $rows[ $row_index ] );
							// continue;
							$rows[ $row_index ]['ID'] = $term_exists_raw[0];
						}
						$rows[ $row_index ]['name']   = $child_name;
						$rows[ $row_index ]['parent'] = $parent_term_id;
						$rows[ $row_index ]['slug']   = '';
					} else {
						$rows[ $row_index ]['original_standarized_name'] = $name;
					}
				}
			}
			return $rows;
		}

		public function add_search_by_description_query__premium_only( $clauses, $taxonomies, $args ) {
			if ( empty( $args['wpse_original_filters'] ) || empty( $args['wpse_original_filters']['meta_query'] ) || ! is_array( $args['wpse_original_filters']['meta_query'] ) ) {
				return $clauses;
			}
			$line_meta_query = WP_Sheet_Editor_Advanced_Filters::get_instance()->_parse_meta_query_args( $args['wpse_original_filters']['meta_query'], 'term_taxonomy_fields' );
			if ( empty( $line_meta_query ) ) {
				return $clauses;
			}

			$wheres = WP_Sheet_Editor_Advanced_Filters::get_instance()->_build_sql_wheres_for_data_table( $line_meta_query, 'tt' );

			if ( ! empty( $wheres ) ) {
				$clauses['where'] .= ' AND ' . implode( ' AND ', $wheres );
			}

			return $clauses;
		}

		public function add_search_by_name_slug_query__premium_only( $clauses, $taxonomies, $args ) {
			if ( empty( $args['wpse_original_filters'] ) || empty( $args['wpse_original_filters']['meta_query'] ) || ! is_array( $args['wpse_original_filters']['meta_query'] ) ) {
				return $clauses;
			}
			$line_meta_query = WP_Sheet_Editor_Advanced_Filters::get_instance()->_parse_meta_query_args( $args['wpse_original_filters']['meta_query'], 'term_terms_fields' );
			if ( empty( $line_meta_query ) ) {
				return $clauses;
			}

			$wheres = WP_Sheet_Editor_Advanced_Filters::get_instance()->_build_sql_wheres_for_data_table( $line_meta_query, 't' );

			if ( ! empty( $wheres ) ) {
				$clauses['where'] .= ' AND ' . implode( ' AND ', $wheres );
			}

			return $clauses;
		}

		public function add_fields_to_advanced_filters__premium_only( $all_fields, $post_type ) {
			if ( ! taxonomy_exists( $post_type ) ) {
				return $all_fields;
			}
			$all_fields['term_taxonomy_fields'] = array( 'description', 'count' );
			$all_fields['term_terms_fields']    = array( 'name', 'slug' );

			return $all_fields;
		}

		public function merge_duplicate_terms( $taxonomy_key ) {
			global $wpdb;
			$sql = $wpdb->prepare(
				"SELECT term.name, taxonomy.parent, COUNT(*) count, GROUP_CONCAT(term.term_id SEPARATOR ',') as term_ids FROM {$wpdb->prefix}terms term
LEFT JOIN {$wpdb->prefix}term_taxonomy taxonomy ON term.term_id = taxonomy.term_id 
WHERE taxonomy.taxonomy = %s
GROUP BY term.name, taxonomy.parent 
HAVING count > 1 LIMIT 20",
				$taxonomy_key
			);

			$all_duplicate_terms = $wpdb->get_results( $sql, ARRAY_A );
			$deleted             = array();
			foreach ( $all_duplicate_terms as $duplicate_terms ) {
				$term_ids = array_map( 'intval', explode( ',', $duplicate_terms['term_ids'] ) );

				$first_term_id = $term_ids[0];
				unset( $term_ids[0] );
				$deleted = array_merge( $deleted, $term_ids );
				$this->_merge_terms__premium_only( $term_ids, $first_term_id, $taxonomy_key );
			}
			$out = compact( 'deleted', 'all_duplicate_terms', 'sql' );
			return $out;
		}
		public function merge_terms__premium_only() {
			global $wpdb;
			if ( empty( VGSE()->helpers->get_nonce_from_request() ) || empty( $_REQUEST['post_type'] ) || ! VGSE()->helpers->verify_nonce_from_request() || ! VGSE()->helpers->verify_sheet_permissions_from_request( 'edit' ) || empty( $_REQUEST['vgse_terms_source'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Request not allowed. Try again later.', 'vg_sheet_editor' ) ) );
			}
			if ( $_REQUEST['vgse_terms_source'] !== 'duplicates' && ( empty( $_REQUEST['final_term'] ) || ! is_string( $_REQUEST['final_term'] ) ) ) {
				wp_send_json_error( array( 'message' => __( 'Please select the term that you want to keep.', 'vg_sheet_editor' ) ) );
			}
			$post_type    = VGSE()->helpers->sanitize_table_key( $_REQUEST['post_type'] );
			$terms_source = sanitize_text_field( $_REQUEST['vgse_terms_source'] );
			$final_term   = sanitize_text_field( $_REQUEST['final_term'] );
			$page         = isset( $_REQUEST['page'] ) ? (int) $_REQUEST['page'] : 1;
			if ( $terms_source !== 'duplicates' ) {
				$final_term    = get_term_by( 'slug', $final_term, $post_type );
				$final_term_id = $final_term->term_id;
			}

			// Disable post actions to prevent conflicts with other plugins
			VGSE()->helpers->remove_all_post_actions( $post_type );

			if ( $terms_source === 'individual' ) {

				$terms_slugs_to_remove = array_map( 'sanitize_text_field', $_REQUEST['terms_to_remove'] );
				if ( empty( $terms_slugs_to_remove ) ) {
					wp_send_json_error( array( 'message' => __( 'Please select the terms to remove.', 'vg_sheet_editor' ) ) );
				}

				$terms_to_remove = array();
				foreach ( $terms_slugs_to_remove as $slug ) {
					$term = get_term_by( 'slug', $slug, $post_type );
					if ( is_object( $term ) ) {
						$terms_to_remove[] = $term->term_id;
					}
				}
			} elseif ( $terms_source === 'duplicates' ) {
				$results = $this->merge_duplicate_terms( $post_type );

				wp_send_json_success(
					array(
						'message'               => sprintf( __( '%s terms merged.', 'vg_sheet_editor' ), count( $results['deleted'] ) ),
						'deleted'               => $results['deleted'],
						'query'                 => VGSE()->helpers->user_can_manage_options() ? $results['sql'] : null,
						'force_complete'        => empty( $results['all_duplicate_terms'] ),
						'duplicate_terms_found' => $results['all_duplicate_terms'],
					)
				);
			} elseif ( $terms_source === 'search' ) {

				$get_rows_args = apply_filters(
					'vg_sheet_editor/terms/merge/search_query/get_rows_args',
					array(
						'nonce'       => wp_create_nonce( 'bep-nonce' ),
						'post_type'   => $post_type,
						'filters'     => $_REQUEST['filters'],
						'paged'       => 1,
						'wpse_source' => 'merge_terms',
					)
				);
				$base_query    = VGSE()->helpers->prepare_query_params_for_retrieving_rows( $get_rows_args );
				$base_query    = apply_filters( 'vg_sheet_editor/terms/merge/posts_query', $base_query );

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
				$terms_to_remove                     = $query->posts;
			}
			if ( (int) $page === 1 && empty( $terms_to_remove ) ) {
				wp_send_json_error( array( 'message' => __( 'Terms to remove not found.', 'vg_sheet_editor' ) ) );
			}

			if ( ! empty( $terms_to_remove ) ) {
				$this->_merge_terms__premium_only( $terms_to_remove, $final_term_id, $post_type );
			}
			wp_send_json_success(
				array(
					'message' => sprintf( __( '%s terms merged.', 'vg_sheet_editor' ), count( $terms_to_remove ) ),
					'deleted' => array_values( array_diff( array_unique( $terms_to_remove ), array( $final_term_id ) ) ),
					'query'   => VGSE()->helpers->user_can_manage_options() && ! empty( $query ) ? $query : null,
				)
			);
		}

		public function _merge_terms__premium_only( $terms_to_remove, $term_id_to_keep, $taxonomy ) {

			if ( ! $term_id_to_keep || is_wp_error( $term_id_to_keep ) ) {
				return false;
			}

			do_action( 'vg_sheet_editor/terms/before_merge', $terms_to_remove, $term_id_to_keep, $taxonomy );

			$successfully_merged_ids = array();
			foreach ( $terms_to_remove as $term_to_remove ) {
				$term_id = $term_to_remove;
				if ( ! $term_id || $term_id == $term_id_to_keep ) {
					continue;
				}

				$delete_status = wp_delete_term(
					$term_id,
					$taxonomy,
					array(
						'default'       => $term_id_to_keep,
						'force_default' => true,
					)
				);
				if ( is_bool( $delete_status ) && $delete_status ) {
					$successfully_merged_ids[] = $term_id;
				}
			}
			do_action( 'vg_sheet_editor/terms/after_merge', $successfully_merged_ids, $term_id_to_keep, $taxonomy );

			return true;
		}

		/**
		 * Register toolbar item
		 */
		public function register_toolbar_items__premium_only( $editor ) {

			if ( ! taxonomy_exists( $editor->args['provider'] ) ) {
				return;
			}
			$editor->args['toolbars']->register_item(
				'merge_terms',
				array(
					'type'                  => 'button', // html | switch | button
					'content'               => __( 'Merge terms', 'vg_sheet_editor' ),
					'id'                    => 'merge-terms',
					'help_tooltip'          => __( 'Combine terms into one and automatically reassign the posts to use the final term.', 'vg_sheet_editor' ),
					'extra_html_attributes' => 'data-remodal-target="merge-terms-modal"',
					'footer_callback'       => array( $this, 'render_merge_terms_modal__premium_only' ),
				),
				$editor->args['provider']
			);
			$editor->args['toolbars']->register_item(
				'recount_terms',
				array(
					'type'                => 'button',
					'content'             => __( 'Recount terms', 'vg_sheet_editor' ),
					'id'                  => 'recount_terms',
					'allow_in_frontend'   => false,
					'toolbar_key'         => 'secondary',
					'help_tooltip'        => __( 'We will count the number of posts using each term and update the term counts.', 'vg_sheet_editor' ),
					'parent'              => 'support',
					'url'                 => esc_url( add_query_arg( 'wpse_recount_terms', $editor->args['provider'] ) ),
					'required_capability' => 'manage_options',
				),
				$editor->args['provider']
			);
		}

		/**
		 * Render modal for merging terms
		 * @param str $post_type
		 * @return null
		 */
		public function render_merge_terms_modal__premium_only( $post_type ) {
			$nonce = wp_create_nonce( 'bep-nonce' );
			include vgse_taxonomy_terms()->plugin_dir . '/views/merge-terms-modal.php';
		}

		public function search_by_multiple_parents( $pieces, $taxonomies, $args ) {

			// Check if our custom argument, 'wpse_term_parents' is set, if not, bail
			if ( ! isset( $args['wpse_term_parents'] ) || ! is_array( $args['wpse_term_parents'] )
			) {
				return $pieces;
			}

			// If  'wpse_term_parents' is set, make sure that 'parent' and 'child_of' is not set
			if ( $args['parent'] || $args['child_of']
			) {
				return $pieces;
			}

			// Validate the array as an array of integers
			$parents = array_map( 'intval', $args['wpse_term_parents'] );

			// Loop through $parents and set the WHERE clause accordingly
			$where = array();
			foreach ( $parents as $parent ) {
				// Make sure $parent is not 0, if so, skip and continue
				if ( 0 === $parent ) {
					continue;
				}

				$where[] = " tt.parent = '$parent'";
			}

			if ( ! $where ) {
				return $pieces;
			}

			$where_string     = implode( ' OR ', $where );
			$pieces['where'] .= " AND ( $where_string ) ";

			return $pieces;
		}

		public function register_custom_filters( $sanitized_filters, $dirty_filters ) {

			if ( isset( $dirty_filters['parent_term_keyword'] ) ) {
				$sanitized_filters['parent_term_keyword'] = sanitize_text_field( $dirty_filters['parent_term_keyword'] );
			}
			return $sanitized_filters;
		}

		/**
		 * Apply filters to wp-query args
		 * @param array $query_args
		 * @param array $data
		 * @return array
		 */
		public function filter_posts( $query_args, $data ) {
			if ( ! empty( $data['filters'] ) ) {
				$filters = WP_Sheet_Editor_Filters::get_instance()->get_raw_filters( $data );

				if ( ! empty( $filters['parent_term_keyword'] ) ) {
					$terms_by_keyword                = get_terms(
						array(
							'hide_empty'             => false,
							'update_term_meta_cache' => false,
							'name__like'             => sanitize_text_field( $filters['parent_term_keyword'] ),
							'fields'                 => 'ids',
						)
					);
					$query_args['wpse_term_parents'] = $terms_by_keyword;
				}
			}

			return $query_args;
		}

		public function add_filters_fields( $current_post_type, $filters ) {
			if ( ! taxonomy_exists( $current_post_type ) ) {
				return;
			}
			if ( is_taxonomy_hierarchical( $current_post_type ) ) {
				?>
				<li>
					<label><?php _e( 'Parent keyword', 'vg_sheet_editor' ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php _e( 'We will display all the categories below parent that contains this keyword', 'vg_sheet_editor' ); ?>">( ? )</a></label>
					<input type="text" name="parent_term_keyword" />							
				</li>
				<?php
			}
		}

		public function filter_welcome_url( $url ) {
			$url = esc_url( admin_url( 'admin.php?page=wpsett_welcome_page' ) );
			return $url;
		}

		public function filter_wp_check_options_for_import( $columns, $taxonomy ) {

			if ( ! taxonomy_exists( $taxonomy ) ) {
				return $columns;
			}
			$columns = array(
				'ID'   => $columns['ID'],
				'slug' => $columns['slug'],
			);
			return $columns;
		}

		public function add_wp_check_message_for_import( $taxonomy ) {

			if ( ! taxonomy_exists( $taxonomy ) ) {
				return;
			}
			?>
			<style>.field-find-existing-columns .wp-check-message { display: none; }</style>
			<p class="wp-custom-check-message"><?php _e( 'We find items that have the same SLUG in the CSV and the WP Field.<br>Please select the CSV column that contains the slug.<br>You must import the slug column if you want to update existing categories, items without slug will be created as new.', vgse_taxonomy_terms()->textname ); ?></p>
			<?php
		}

		public function find_existing_term_by_slug_for_import( $term_id, $row, $taxonomy, $meta_query, $writing_type, $check_wp_fields ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				$default_term_id = PHP_INT_MAX;
				if ( ! empty( $row['ID'] ) && in_array( 'ID', $check_wp_fields ) ) {
					$term_id = ( term_exists( (int) $row['ID'], $taxonomy ) ) ? (int) $row['ID'] : null;
				} else {
					if ( ! empty( $row['old_slug'] ) && in_array( 'old_slug', $check_wp_fields ) ) {
						$slug = $row['old_slug'];
					} elseif ( ! empty( $row['slug'] ) && in_array( 'slug', $check_wp_fields ) ) {
						$slug = $row['slug'];
					}

					if ( ! empty( $slug ) ) {
						$term = get_term_by( 'slug', $slug, $taxonomy );

						if ( $term && ! is_wp_error( $term ) ) {
							$term_id = $term->term_id;
						}
					}
				}
				if ( ! $term_id ) {
					$term_id = $default_term_id;
				}
			}
			return $term_id;
		}

		public function blacklist_private_columns( $blacklisted_fields, $provider ) {
			if ( ! in_array( $provider, $this->post_type ) ) {
				return $blacklisted_fields;
			}
			//          We have allowed the product_count_xxx" term meta because WooCommerce uses this as usage count
			//          so we need this for the searches to delete unused tags and categories,
			//          to prevent confusions we are blacklisting the core count column
			//          $blacklisted_fields[] = '^product_count_';
			if ( in_array( $provider, array( 'product_cat', 'product_tag' ), true ) ) {
				$blacklisted_fields[] = '^count$';
			}

			return $blacklisted_fields;
		}

		// WooCommerce returns 0 even on success, so we must return
		// something to avoid showing the automatic ajax error notification
		// that sheet editor shows
		public function woocommerce_term_ordering() {
			echo 1;
		}

		/**
		 * Register frontend assets
		 */
		public function register_assets() {
			wp_enqueue_script( 'wpse-taxonomy-terms-js', plugins_url( '/assets/js/init.js', vgse_taxonomy_terms()->args['main_plugin_file'] ), array(), VGSE()->version, false );
			wp_localize_script(
				'wpse-taxonomy-terms-js',
				'wpse_tt_data',
				array(
					'sort_icon_url' => plugins_url( '/assets/imgs/sort-icon.png', vgse_taxonomy_terms()->args['main_plugin_file'] ),
				)
			);
		}

		public function enable_row_sorting( $handsontable_args, $provider ) {
			if ( class_exists( 'WooCommerce' ) && ( strstr( $provider, 'pa_' ) || in_array( $provider, apply_filters( 'woocommerce_sortable_taxonomies', array( 'product_cat' ) ) ) ) ) {
				$handsontable_args['manualRowMove'] = true;
			}
			return $handsontable_args;
		}

		public function get_taxonomies_and_labels() {

			if ( wpsett_fs()->can_use_premium_code__premium_only() ) {
				$taxonomies = array_merge(
					get_taxonomies(
						array(
							'public'   => true,
							'show_ui'  => true,
							'_builtin' => true,
						),
						'objects'
					),
					get_taxonomies(
						array(
							'show_ui'  => true,
							'_builtin' => false,
						),
						'objects'
					)
				);

				if ( VGSE()->get_option( 'taxonomy_sheets_keywords' ) ) {
					$allowed_keywords = array_map( 'trim', explode( ',', VGSE()->get_option( 'taxonomy_sheets_keywords' ) ) );
					foreach ( $taxonomies as $index => $taxonomy ) {
						$keyword_found = false;
						foreach ( $allowed_keywords as $keyword ) {
							if ( stripos( $taxonomy->name, $keyword ) !== false || stripos( $taxonomy->label, $keyword ) !== false ) {
								$keyword_found = true;
								break;
							}
						}
						if ( ! $keyword_found ) {
							unset( $taxonomies[ $index ] );
						}
					}
				}
				$out = array(
					'post_types' => array_values( wp_list_pluck( $taxonomies, 'name' ) ),
					'labels'     => array_values( wp_list_pluck( $taxonomies, 'label' ) ),
				);
			} else {
				$out = array(
					'post_types' => array( 'category', 'post_tag' ),
					'labels'     => array( 'Blog categories', 'Blog tags' ),
				);
			}

			// Don't register sheets for taxonomies with same key as a registered post type
			// Because the post types sheets take priority over taxonomy sheets
			foreach ( $out['post_types'] as $index => $post_type ) {
				if ( post_type_exists( $post_type ) ) {
					unset( $out['post_types'][ $index ] );
					unset( $out['labels'][ $index ] );
				}
			}

			return $out;
		}

		public function set_default_provider_for_taxonomies( $provider_class_key, $provider ) {
			if ( taxonomy_exists( $provider ) ) {
				$provider_class_key = 'term';
			}
			return $provider_class_key;
		}

		public function filter_cell_data_for_readings( $value, $id, $key, $single, $context, $item = null ) {
			if ( $context !== 'read' || ( $item && ! in_array( $item->taxonomy, $this->post_type ) ) ) {
				return $value;
			}
			if ( $key === 'parent' && $value ) {
				$term  = VGSE()->helpers->get_current_provider()->get_item( $value );
				$value = $term->name;
			}
			if ( $key === 'count' ) {
				$value = (int) $value;
			}

			return $value;
		}

		public function filter_cell_data_for_saving( $new_value, $id, $key ) {
			if ( get_post_type( $id ) !== $this->post_type ) {
				return $new_value;
			}

			if ( $key === 'taxonomy_term_files' && is_array( $new_value ) ) {
				$new_value = $new_value;
			}

			return $new_value;
		}

		public function get_columns() {
		}
	}

	$GLOBALS['wpse_taxonomy_terms_sheet'] = new WPSE_Taxonomy_Terms_Sheet();
}
