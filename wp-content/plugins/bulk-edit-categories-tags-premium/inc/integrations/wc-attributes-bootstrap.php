<?php
if ( ! class_exists( 'WPSE_WC_Attributes_Spreadsheet_Bootstrap' ) ) {

	class WPSE_WC_Attributes_Spreadsheet_Bootstrap extends WP_Sheet_Editor_Bootstrap {

		public function __construct( $args ) {
			parent::__construct( $args );

			if ( $this->settings['register_admin_menus'] ) {
				add_action( 'woocommerce_before_add_attribute_fields', array( $this, '_render_quick_access' ), 10, 0 );
			}
		}

		function render_quick_access() {
		}

		function _render_quick_access() {
			?>
			<hr><p class="wpse-quick-access"><a href="<?php echo esc_url( VGSE()->helpers->get_editor_url( $this->enabled_post_types[0] ) ); ?>" class="button button-primary"><?php _e( 'Edit in a Spreadsheet', vgse_taxonomy_terms()->textname ); ?></a></p><hr>
			<?php
		}

		function _register_columns() {
			$post_types = $this->enabled_post_types;

			foreach ( $post_types as $post_type ) {
				$this->columns->register_item(
					'ID',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 75,
						'title'             => __( 'ID', vgse_taxonomy_terms()->textname ),
						'supports_formulas' => false,
						'allow_to_hide'     => false,
						'allow_to_save'     => false,
						'allow_to_rename'   => false,
						'is_locked'         => true,
					)
				);
				$this->columns->register_item(
					'attribute_label',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 210,
						'title'             => __( 'Label', vgse_taxonomy_terms()->textname ),
						'supports_formulas' => true,
					)
				);
				$this->columns->register_item(
					'attribute_name',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 210,
						'title'             => __( 'Name', vgse_taxonomy_terms()->textname ),
						'supports_formulas' => true,
					)
				);
				// We can't use the function wc_has_custom_attribute_type because it's too early
				$this->columns->register_item(
					'attribute_type',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 210,
						'title'             => __( 'Type', vgse_taxonomy_terms()->textname ),
						'supports_formulas' => true,
						'formatted'         => array(
							'editor'        => 'select',
							// We use a callable because some plugins filter the output of the attribute_types and throw an error if we call the function here because it's too early
							'selectOptions' => 'wc_get_attribute_types',
						),
					)
				);
				$this->columns->register_item(
					'attribute_orderby',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 210,
						'title'             => __( 'Default sort order', vgse_taxonomy_terms()->textname ),
						'supports_formulas' => true,
						'formatted'         => array(
							'editor'        => 'select',
							'selectOptions' => array(
								'menu_order' => __( 'Custom ordering', 'woocommerce' ),
								'name'       => __( 'Name', 'woocommerce' ),
								'name_num'   => __( 'Name (numeric)', 'woocommerce' ),
								'id'         => __( 'Term ID', 'woocommerce' ),
							),
						),
					)
				);
				$this->columns->register_item(
					'attribute_public',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 130,
						'title'             => __( 'Enable Archives', vgse_taxonomy_terms()->textname ),
						'supports_formulas' => true,
						'formatted'         => array(
							'type'              => 'checkbox',
							'checkedTemplate'   => '1',
							'uncheckedTemplate' => '0',
						),
						'default_value'     => '0',
					)
				);
				$this->columns->register_item(
					'wpse_status',
					$post_type,
					array(
						'data_type'           => 'post_data',
						'column_width'        => 150,
						'title'               => __( 'Status', vgse_taxonomy_terms()->textname ),
						'supports_formulas'   => true,
						'allow_to_hide'       => false,
						'allow_to_save'       => true,
						'allow_to_rename'     => true,
						'default_value'       => 'active',
						'formatted'           => array(
							'data'          => 'wpse_status',
							'editor'        => 'select',
							'selectOptions' => array(
								'active',
								'delete',
							),
						),
						'save_value_callback' => array( $this, 'delete_taxonomy' ),
					)
				);

				$this->columns->register_item(
					'manage_terms',
					$post_type,
					array(
						'data_type'          => 'post_data',
						'column_width'       => 145,
						'title'              => __( 'Manage terms', VGSE()->textname ),
						'type'               => 'external_button',
						'supports_formulas'  => false,
						'allow_to_hide'      => true,
						'allow_to_save'      => false,
						'allow_to_rename'    => true,
						'get_value_callback' => array( $this, 'get_taxonomy_spreadsheet_url_for_cell' ),
					)
				);
			}
		}

		function get_taxonomy_spreadsheet_url_for_cell( $post, $cell_key, $cell_args ) {
			$taxonomy_key = wc_attribute_taxonomy_name_by_id( (int) $post->ID );
			$value        = $taxonomy_key ? wp_nonce_url( add_query_arg( 'wpse_auto_enable_sheet', 1, VGSE()->helpers->get_editor_url( $taxonomy_key ) ), 'bep-nonce' ) : '';
			return $value;
		}

		function delete_taxonomy( $post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns ) {
			global $wpdb;
			if ( (int) $post_id < 1 || $data_to_save !== 'delete' ) {
				return;
			}
			// Try to delete with WC first, and delete with direct query as a fallback
			wc_delete_attribute( $post_id );
			$wpdb->delete( $wpdb->prefix . 'woocommerce_attribute_taxonomies', array( 'attribute_id' => (int) $post_id ) );
			WPSE_WC_Attributes_Sheet::clear_caches();

			VGSE()->deleted_rows_ids[] = (int) $post_id;
		}
	}

}
