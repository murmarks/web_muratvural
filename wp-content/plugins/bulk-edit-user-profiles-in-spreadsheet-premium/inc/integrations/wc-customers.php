<?php

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}
if ( ! class_exists( 'WPSE_WC_Customers_Sheet' ) ) {

	class WPSE_WC_Customers_Sheet extends WPSE_Sheet_Factory {
		public $post_type        = null;
		public $orders_count_key = '_order_count';
		public $last_order_date  = 'wc_last_order_date';

		public function __construct() {
			global $wpdb;
			$this->post_type = $wpdb->prefix . 'wc_customer_lookup';
			parent::__construct(
				array(
					'fs_object'       => beupis_fs(),
					'post_type'       => array( $this->post_type ),
					'post_type_label' => array( __( 'Customers', 'vg_sheet_editor_users' ) ),
					'bootstrap_class' => 'WPSE_Custom_Tables_Spreadsheet_Bootstrap',
				)
			);
			add_filter( 'vg_sheet_editor/provider/default_provider_key', array( $this, 'set_default_provider' ), 10, 2 );
			add_filter( 'vg_sheet_editor/advanced_filters/all_fields_groups', array( $this, 'add_fields_to_advanced_filters' ), 10, 2 );
			add_filter( 'vg_sheet_editor/provider/custom_table/get_rows_sql', array( $this, 'filter_rows_query_post_data' ), 10, 3 );
			add_filter( 'vg_sheet_editor/provider/custom_table/get_rows_sql', array( $this, 'filter_rows_query_orders_count' ), 10, 3 );
			add_filter( 'vg_sheet_editor/provider/custom_table/get_rows_sql', array( $this, 'filter_rows_query_order_last_date' ), 10, 3 );
			add_action( 'vg_sheet_editor/editor/register_columns', array( $this, '_register_columns' ), 1 );
			add_filter( 'vg_sheet_editor/load_rows/full_output', array( $this, 'calculate_sales_totals' ), 10, 2 );
			add_filter( 'vg_sheet_editor/advanced_filters/all_fields_groups', array( $this, 'add_orders_count_filter' ), 10, 2 );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ), 999 );
			add_action( 'admin_init', array( $this, 'maybe_purge_customer_lookup_table' ) );
		}

		function maybe_purge_customer_lookup_table( $post_type ) {
			global $wpdb;
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}
			if ( empty( $_GET['wpse_wc_customer_lookup_purge'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wpse' ) ) {
				return;
			}

			$main_blog_prefix = $wpdb->get_blog_prefix( 1 );

			// Delete customers with user_id of deleted users
			$wpdb->query( "DELETE FROM {$wpdb->prefix}wc_customer_lookup WHERE user_id > 0 AND user_id NOT IN (SELECT ID FROM {$main_blog_prefix}users)" );

			// Delete customers with empty email
			$wpdb->delete(
				$wpdb->prefix . 'wc_customer_lookup',
				array(
					'email' => '',
				)
			);

			// Delete customers with email not used on any orders
			$hpos_enabled = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
			if ( $hpos_enabled ) {
				$sql = "DELETE FROM {$wpdb->prefix}wc_customer_lookup WHERE email NOT IN (SELECT billing_email FROM {$wpdb->prefix}wc_orders GROUP BY billing_email)";
			} else {
				$sql = "DELETE FROM {$wpdb->prefix}wc_customer_lookup WHERE email NOT IN (SELECT meta_value FROM {$wpdb->postmeta} pm LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_type = 'shop_order' AND pm.meta_key = '_billing_email' AND pm.meta_value <> '' GROUP BY meta_value)";
			}
			$wpdb->query( $sql );

			\Automattic\WooCommerce\Admin\API\Reports\Cache::invalidate();

			wp_safe_redirect( esc_url( remove_query_arg( array( '_wpnonce', 'wpse_wc_customer_lookup_purge' ) ) ) );
			exit();
		}

		public function register_toolbar_items( $editor ) {
			if ( ! VGSE()->helpers->user_can_manage_options() ) {
				return;
			}

			if ( ! in_array( $this->post_type, $editor->args['enabled_post_types'] ) ) {
				return;
			}
			$editor->args['toolbars']->register_item(
				'wc_flush_customers_lookup',
				array(
					'type'              => 'button',
					'url'               => esc_url( wp_nonce_url( add_query_arg( 'wpse_wc_customer_lookup_purge', 1 ), 'wpse' ) ),
					'allow_in_frontend' => false,
					'content'           => __( 'Delete customers with 0 orders, empty email, orphan customers', 'vg_sheet_editor_users' ),
					'icon'              => 'fa fa-reload',
					'toolbar_key'       => 'secondary',
					'parent'            => 'run_formula',
				),
				$this->post_type
			);
		}

		function add_orders_count_filter( $groups, $post_type ) {
			if ( $post_type === $this->post_type && class_exists( 'WooCommerce' ) ) {
				$groups['wc'] = array( $this->orders_count_key, $this->last_order_date );
			}
			return $groups;
		}
		function calculate_sales_totals( $data, $qry ) {
			global $wpdb;
			if ( ! class_exists( 'WooCommerce' ) || ! current_user_can( 'manage_woocommerce' ) ) {
				return $data;
			}
			if ( $qry['post_type'] !== $this->post_type ) {
				return $data;
			}

			// We use custom queries for performance reasons.

			$main_query_sql        = $GLOBALS['wpse_main_query']->request;
			$main_customer_ids_sql = str_replace(
				array(
					't.*',
				),
				array(
					't.customer_id',
				),
				$main_query_sql
			);
			if ( strpos( $main_customer_ids_sql, 'ORDER BY ' ) !== false ) {
				$main_customer_ids_sql = substr( $main_customer_ids_sql, 0, strpos( $main_customer_ids_sql, 'ORDER BY ' ) );
			}
			if ( empty( $main_customer_ids_sql ) ) {
				return $data;
			}
			$get_totals_sql = "SELECT SUM(o.net_total) AS 'net', SUM(total_sales) AS 'gross', SUM(o.num_items_sold) AS 'total_items_sold', COUNT(o.order_id) AS 'total_transactions' FROM {$wpdb->prefix}wc_order_stats o 
WHERE o.customer_id IN (" . $main_customer_ids_sql . ')';

			$totals            = $wpdb->get_row( $get_totals_sql, ARRAY_A );
			$data['wc_totals'] = array(
				str_replace( '.', '', __( 'Gross Sales.', 'woocommerce' ) ) => wc_price( $totals['gross'] ),
				__( 'Net Sales', 'woocommerce' ) => wc_price( $totals['net'] ),
				str_replace( '.', '', __( 'Items sold.', 'woocommerce' ) ) => intval( $totals['total_items_sold'] ),
				str_replace( '.', '', __( 'Number of orders.', 'woocommerce' ) ) => intval( $totals['total_transactions'] ),
			);

			return $data;
		}

		/**
		 * Register toolbar items
		 *
		 * @param  WP_Sheet_Editor_Factory $editor
		 * @return void
		 */
		function _register_columns( $editor ) {
			$post_type = $this->post_type;
			if ( ! in_array( $post_type, $editor->args['enabled_post_types'] ) ) {
				return;
			}

			if ( method_exists( $editor->provider, 'prefetch_tables_structure' ) ) {
				$editor->provider->prefetch_tables_structure( array( $post_type ) );
			}

			$columns = $editor->provider->get_arg( 'columns', $post_type );
			if ( empty( $columns ) ) {
				return;
			}
			$primary_column_key = $editor->provider->get_post_data_table_id_key( $post_type );
			foreach ( $columns as $column_key => $column ) {
				// Don't register the primary key column because the bootstrap
				// automatically registers the ID column
				if ( $column_key === $primary_column_key ) {
					continue;
				}
				$first_value = ! empty( $column['sample_values'] ) ? current( $column['sample_values'] ) : null;
				if ( empty( $first_value ) || ! is_array( $first_value ) ) {
					$formatted = array();
					if ( $column_key === 'country' ) {
						$formatted = array(
							'editor'        => 'select',
							'selectOptions' => WC()->countries->countries,
						);
					} elseif ( in_array( $column_key, array( 'date_last_active', 'date_registered' ), true ) ) {
						$formatted = array(
							'editor'           => 'wp_datetime',
							'type'             => 'date',
							'dateFormatPhp'    => 'Y-m-d H:i:s',
							'correctFormat'    => true,
							'defaultDate'      => date( 'Y-m-d H:i:s' ),
							'datePickerConfig' => array(
								'firstDay'       => 0,
								'showWeekNumber' => true,
								'numberOfMonths' => 1,
							),
						);
					}
					$editor->args['columns']->register_item(
						$column_key,
						$post_type,
						array(
							'data_type'           => 'post_data',
							'column_width'        => 150,
							'title'               => VGSE()->helpers->convert_key_to_label( $column_key ),
							'supports_formulas'   => true,
							'allow_to_rename'     => true,
							'allow_custom_format' => true,
							'formatted'           => $formatted,
						)
					);
				} elseif ( class_exists( 'WPSE_Custom_Tables_Serialized_Fields' ) ) {
					new WPSE_Custom_Tables_Serialized_Fields(
						array(
							'sample_field_key'   => $column_key,
							'sample_field'       => $first_value,
							'allowed_post_types' => array( $post_type ),
							'wpse_source'        => 'custom_table_controller',
							'column_settings'    => array(
								'allow_custom_format' => true,
							),
						)
					);
				}
			}
			$editor->args['columns']->register_item(
				'orders_amount',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'title'              => __( 'Orders', 'vg_sheet_editor_users' ),
					'supports_formulas'  => false,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => function ( $post, $cell_key, $cell_args ) {
						global $wpdb;
						$value = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i os WHERE customer_id = %d', $wpdb->prefix . 'wc_order_stats', $post->ID ) );
						return $value;
					},
					'allow_to_import'    => false,
				)
			);

			$editor->args['columns']->register_item(
				'wc_last_order_date',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'title'              => __( 'Last purchase date', 'vg_sheet_editor_users' ),
					'supports_formulas'  => false,
					'allow_to_save'      => false,
					'allow_to_rename'    => false,
					'is_locked'          => true,
					'get_value_callback' => function ( $post, $cell_key, $cell_args ) {
						global $wpdb;
						$value = $wpdb->get_var( $wpdb->prepare( 'SELECT date_created FROM %i os WHERE customer_id = %d ORDER BY date_created DESC', $wpdb->prefix . 'wc_order_stats', $post->ID ) );
						return $value;
					},
				)
			);
			$editor->args['columns']->register_item(
				'_money_spent',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'title'              => __( 'Total spent', 'vg_sheet_editor_users' ),
					'supports_formulas'  => false,
					'allow_to_save'      => false,
					'allow_to_rename'    => false,
					'is_locked'          => true,
					'get_value_callback' => function ( $post, $cell_key, $cell_args ) {
						global $wpdb;
						$value = (float) $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(net_total) FROM %i WHERE customer_id = %d', $wpdb->prefix . 'wc_order_stats', $post->ID ) );
						return $value;
					},
				)
			);
			$editor->args['columns']->register_item(
				'_wc_aov',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'title'              => __( 'Average order value', 'vg_sheet_editor_users' ),
					'supports_formulas'  => false,
					'allow_to_save'      => false,
					'allow_to_rename'    => false,
					'is_locked'          => true,
					'get_value_callback' => function ( $post, $cell_key, $cell_args ) {
						global $wpdb;
						$value = (float) $wpdb->get_var(
							$wpdb->prepare(
								'SELECT 
			SUM(net_total) / COUNT(order_id) AS avg_order_value 
			FROM %i
			WHERE customer_id = %d LIMIT 1',
								$wpdb->prefix . 'wc_order_stats',
								$post->ID
							)
						);
						return $value;
					},
				)
			);
			$editor->args['columns']->register_item(
				'wpse_status',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 150,
					'title'             => __( 'Status', 'vg_sheet_editor_users' ),
					'supports_formulas' => true,
					'allow_to_hide'     => false,
					'allow_to_save'     => true,
					'allow_to_rename'   => true,
					'default_value'     => 'active',
					'formatted'         => array(
						'editor'        => 'select',
						'selectOptions' => array(
							'active',
							'delete',
						),
					),
				)
			);
		}

		public function filter_rows_query_post_data( $sql, $args, $settings ) {
			$post_type = $settings['table_name'];
			if ( $post_type !== $this->post_type ) {
				return $sql;
			}

			if ( empty( $args['wpse_original_filters'] ) || empty( $args['wpse_original_filters']['meta_query'] ) ) {
				return $sql;
			}
			$table_data_filters = wp_list_filter(
				$args['wpse_original_filters']['meta_query'],
				array(
					'source' => 'customers_data',
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


		public function filter_rows_query_orders_count( $sql, $args, $settings ) {
			global $wpdb;
			$post_type = $settings['table_name'];
			if ( $post_type !== $this->post_type ) {
				return $sql;
			}

			if ( empty( $args['wpse_original_filters'] ) || empty( $args['wpse_original_filters']['meta_query'] ) ) {
				return $sql;
			}
			$user_data_filters = wp_list_filter(
				$args['wpse_original_filters']['meta_query'],
				array(
					'key' => $this->orders_count_key,
				)
			);
			if ( empty( $user_data_filters ) ) {
				return $sql;
			}

			$wheres = array();
			foreach ( $user_data_filters as $index => $meta_query ) {
				if ( ! in_array( $meta_query['compare'], array( '=', '!=', '>', '>=', '<', '<=' ), true ) ) {
					$meta_query['compare'] = '=';
				}

				if ( $meta_query['compare'] === '=' && (int) $meta_query['value'] === 0 || $meta_query['compare'] === '<' && (int) $meta_query['value'] === 1 ) {
					$wheres[] = 't.customer_id NOT IN (SELECT os.customer_id FROM ' . $wpdb->prefix . 'wc_order_stats os GROUP BY os.customer_id)';
				} else {
					$sub_sql  = "SELECT customer_id FROM ( SELECT os.customer_id, COUNT(*) 'count' FROM " . $wpdb->prefix . 'wc_order_stats os 
					GROUP BY os.customer_id
					HAVING count ' . $meta_query['compare'] . ' ' . (int) $meta_query['value'] . "
					
					UNION ALL
					
					SELECT c.customer_id, '0' as count 
					FROM " . $wpdb->prefix . 'wc_customer_lookup as c 
						WHERE c.customer_id  NOT IN (
						SELECT os2.customer_id FROM ' . $wpdb->prefix . 'wc_order_stats os2 GROUP BY os2.customer_id
						) 
					HAVING count ' . $meta_query['compare'] . ' ' . (int) $meta_query['value'] . ' ) tmpUsers';
					$wheres[] = 't.customer_id IN ( ' . $sub_sql . ' )';
				}
			}
			if ( empty( $wheres ) ) {
				return $sql;
			}

			$where = implode( ' AND ', $wheres );
			if ( strpos( $sql, ' WHERE ' ) === false ) {
				$where = ' WHERE ' . $where;
			} else {
				$where = ' AND ' . $where;
			}
			$sql = str_replace( ' ORDER ', $where . ' ORDER ', $sql );
			return $sql;
		}

		public function filter_rows_query_order_last_date( $sql, $args, $settings ) {
			global $wpdb;
			$post_type = $settings['table_name'];
			if ( $post_type !== $this->post_type ) {
				return $sql;
			}

			if ( empty( $args['wpse_original_filters'] ) || empty( $args['wpse_original_filters']['meta_query'] ) ) {
				return $sql;
			}
			$user_data_filters = wp_list_filter(
				$args['wpse_original_filters']['meta_query'],
				array(
					'key' => $this->last_order_date,
				)
			);
			if ( empty( $user_data_filters ) ) {
				return $sql;
			}

			$wheres = array();
			foreach ( $user_data_filters as $index => $meta_query ) {
				if ( ! in_array( $meta_query['compare'], array( '=', '!=', '>', '>=', '<', '<=' ), true ) ) {
					$meta_query['compare'] = '=';
				}

				if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $meta_query['value'] ) ) {
					$meta_query['compare'] = 'LIKE';
					$date                  = $meta_query['value'] . '%';
				} else {
					$date = date( 'Y-m-d H:i:s', strtotime( $meta_query['value'] ) );
				}

				$sub_sql      = "SELECT os.customer_id
FROM {$wpdb->prefix}wc_order_stats os 
GROUP BY os.customer_id 
HAVING MAX(date_created) " . $meta_query['compare'] . " '" . $date . "' ";
					$wheres[] = 't.customer_id IN ( ' . $sub_sql . ' )';
			}
			if ( empty( $wheres ) ) {
				return $sql;
			}

			$where = implode( ' AND ', $wheres );
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
			if ( $post_type !== $this->post_type ) {
				return $all_fields;
			}

			$data_fields                  = wp_list_pluck( $wpdb->get_results( "SHOW COLUMNS FROM {$this->post_type};" ), 'Field' );
			$all_fields['customers_data'] = $data_fields;

			return $all_fields;
		}


		public function set_default_provider( $provider_class_key, $post_type ) {
			if ( $post_type === $this->post_type ) {
				$provider_class_key = 'custom_table';
			}
			return $provider_class_key;
		}
	}

	new WPSE_WC_Customers_Sheet();
}
