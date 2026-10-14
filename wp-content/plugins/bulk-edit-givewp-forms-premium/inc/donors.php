<?php

if ( ! class_exists( 'WPSE_GiveWP_Donors_Sheet' ) ) {

	class WPSE_GiveWP_Donors_Sheet extends WPSE_Sheet_Factory {
		public $post_type = null;

		public function __construct() {
			global $wpdb;
			$this->post_type = $wpdb->prefix . 'give_donors';
			parent::__construct(
				array(
					'fs_object'       => wpsegwf_fs(),
					'post_type'       => array( $this->post_type ),
					'post_type_label' => array( __( 'Donors', 'vg_sheet_editor_givewp' ) ),
					'bootstrap_class' => 'WPSE_Custom_Tables_Spreadsheet_Bootstrap',
				)
			);
			add_filter( 'vg_sheet_editor/provider/default_provider_key', array( $this, 'set_default_provider' ), 10, 2 );
			add_filter( 'vg_sheet_editor/advanced_filters/all_fields_groups', array( $this, 'add_fields_to_advanced_filters' ), 10, 2 );
			add_filter( 'vg_sheet_editor/provider/custom_table/get_rows_sql', array( $this, 'filter_rows_query_post_data' ), 10, 3 );
			add_filter( 'vgse_sheet_editor/provider/custom_table/meta_table_name', array( $this, 'use_different_meta_table' ), 10, 2 );
			add_filter( 'vgse_sheet_editor/provider/custom_table/meta_table_post_id_key', array( $this, 'use_different_meta_id_column' ), 10, 2 );
			add_action( 'vg_sheet_editor/editor/register_columns', array( $this, '_register_columns' ), 1 );
		}
		/**
		 * Register toolbar items
		 *
		 * @param  WP_Sheet_Editor_Factory $editor
		 * @return void
		 */
		function _register_columns( $editor ) {
			$post_type = $this->post_type;
			if ( $editor->args['provider'] !== $post_type ) {
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
					$editor->args['columns']->register_item(
						$column_key,
						$post_type,
						array(
							'data_type'           => 'post_data',
							'column_width'        => 150,
							'title'               => VGSE()->helpers->convert_key_to_label( $column_key ),
							'type'                => '',
							'supports_formulas'   => true,
							'allow_to_hide'       => true,
							'allow_to_rename'     => true,
							'allow_custom_format' => true,
						)
					);
				} elseif( class_exists('WPSE_Custom_Tables_Serialized_Fields')) {
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
                'wpse_status',
                $post_type,
                array(
                    'data_type'         => 'post_data', 
                    'column_width'      => 150, 
                    'title'             => __( 'Status', 'vg_sheet_editor_givewp' ),
                    'type'              => '',
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

		function use_different_meta_id_column( $id_column, $post_type ) {
			if ( $post_type !== $this->post_type ) {
				return $id_column;
			}

			return 'donor_id';
		}

		function use_different_meta_table( $table_name, $post_type ) {
			global $wpdb;
			if ( $post_type !== $this->post_type ) {
				return $table_name;
			}

			return $wpdb->prefix . 'give_donormeta';
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
					'source' => 'donors_data',
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
			if ( $post_type !== $this->post_type ) {
				return $all_fields;
			}

			$data_fields               = wp_list_pluck( $wpdb->get_results( "SHOW COLUMNS FROM {$this->post_type};" ), 'Field' );
			$all_fields['donors_data'] = $data_fields;

			return $all_fields;
		}


		public function set_default_provider( $provider_class_key, $post_type ) {
			if ( $post_type === $this->post_type ) {
				$provider_class_key = 'custom_table';
			}
			return $provider_class_key;
		}

	}

	new WPSE_GiveWP_Donors_Sheet();
}
