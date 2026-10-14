<?php

if ( ! class_exists( 'WPSE_Give_Donation_Forms' ) ) {
	class WPSE_Give_Donation_Forms extends WPSE_Sheet_Factory {

		public $post_type = 'give_forms';
		public $columns   = array();

		function __construct() {
			$allowed_columns = array();

			if ( ! wpsegwf_fs()->can_use_premium_code__premium_only() ) {
				$allowed_columns = array(
					'ID',
					'post_title',
					'post_status',
				);
			}

			parent::__construct(
				array(
					'fs_object'          => wpsegwf_fs(),
					'post_type'          => array( $this->post_type ),
					'post_type_label'    => array( __( 'Donation Forms', 'vg_sheet_editor_givewp' ) ),
					'serialized_columns' => array(), // column keys
					'columns'            => array(),
					'allowed_columns'    => $allowed_columns,
					'remove_columns'     => array(
						'_give_custom_amount_range_maximum',
						'_give_custom_amount_range_minimum',
					),
				)
			);
			$this->set_hooks();
		}

		function set_hooks() {
			add_action( 'vg_sheet_editor/editor/register_columns', array( $this, 'register_columns' ), 60 );
			add_filter( 'vgse_sheet_editor/provider/post/meta_table_name', array( $this, 'use_different_meta_table' ), 10, 2 );
			add_filter( 'vgse_sheet_editor/provider/post/meta_table_post_id_key', array( $this, 'use_different_meta_id_column' ), 10, 2 );
		}
		function use_different_meta_id_column( $id_column, $post_type ) {
			if ( $post_type !== $this->post_type ) {
				return $id_column;
			}

			return 'form_id';
		}

		function use_different_meta_table( $table_name, $post_type ) {
			global $wpdb;
			if ( $post_type !== $this->post_type ) {
				return $table_name;
			}

			return $wpdb->prefix . 'give_formmeta';
		}
		/**
		 * Register spreadsheet columns
		 */
		function register_columns( $editor ) {
			$post_type = $this->post_type;
			if ( ! in_array( $editor->args['provider'], array( $post_type ) ) ) {
				return;
			}

			$give_metabox = new Give_MetaBox_Form_Data();
			$fields       = $give_metabox->get_settings();

			foreach ( $fields as $section ) {
				foreach ( $section['fields'] as $field ) {
					if ( empty( $field['id'] ) || empty( $field['name'] ) ) {
						continue;
					}
					if ( in_array( $field['type'], array( 'radio_inline', 'chosen', 'radio' ) ) ) {
						$editor->args['columns']->register_item(
							$field['id'],
							$post_type,
							array(
								'title'     => $field['name'],
								'formatted' => array(
									'editor'        => 'select',
									'selectOptions' => $field['options'],
								),
							),
							true
						);
					} elseif ( in_array( $field['type'], array( 'wysiwyg' ) ) ) {
						$editor->args['columns']->register_item(
							$field['id'],
							$post_type,
							array(
								'title'     => $field['name'],
								'formatted' => array( 'renderer' => 'wp_tinymce' ),
							),
							true
						);
					} elseif ( in_array( $field['type'], array( 'file' ) ) ) {
						$editor->args['columns']->register_item(
							$field['id'],
							$post_type,
							array(
								'title' => $field['name'],
								'type'  => 'boton_gallery',
							),
							true
						);
					} elseif ( $editor->args['columns']->has_item( $field['id'], $post_type ) ) {
						$editor->args['columns']->register_item(
							$field['id'],
							$post_type,
							array(
								'title' => $field['name'],
							),
							true
						);
					}
				}
			}

			$editor->args['columns']->register_item(
				'_give_levels_maximum_amount',
				$post_type,
				array(
					'type' => '',
				),
				true
			);
			$editor->args['columns']->register_item(
				'_give_levels_minimum_amount',
				$post_type,
				array(
					'type' => '',
				),
				true
			);

			if ( function_exists( 'vgse_columns_manager_init' ) ) {
				$editor->args['columns']->register_item(
					'expiry_date',
					$post_type,
					array(
						'prepare_value_for_database' => array( vgse_columns_manager_init(), 'prepare_date_for_database' ),
						'columns_manager_settings'   => array(
							'field_type'          => 'date',
							'date_format_save'    => 'U',
							'date_format_display' => 'Y-m-d H:i:s',
						),
					),
					true
				);
			}
			$editor->args['columns']->register_item(
				'customer_email',
				$post_type,
				array(
					'prepare_value_for_database' => array( $this, 'prepare_customer_email_for_database' ),
					'prepare_value_for_display'  => array( $this, 'prepare_customer_email_for_display' ),
				),
				true
			);
		}

		function prepare_customer_email_for_display( $value, $post, $column_key, $column_settings ) {
			$out = '';
			if ( empty( $value ) ) {
				return $out;
			}
			if ( is_string( $value ) ) {
				$out = $value;
			} elseif ( is_array( $value ) ) {
				$out = implode( ', ', $value );
			}
			return $out;
		}
		function prepare_customer_email_for_database( $post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns ) {
			if ( ! empty( $data_to_save ) ) {
				$data_to_save = array_filter( array_map( 'trim', explode( ',', $data_to_save ) ) );
			}
			return $data_to_save;
		}

	}
	new WPSE_Give_Donation_Forms();
}
