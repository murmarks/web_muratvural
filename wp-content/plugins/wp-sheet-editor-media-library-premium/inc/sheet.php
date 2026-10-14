<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Media_Library_Sheet' ) ) {

	class WPSE_Media_Library_Sheet extends WPSE_Sheet_Factory {
		var $post_type = 'attachment';

		public function __construct() {
			parent::__construct(
				array(
					'fs_object'       => wpseml_freemius(),
					'post_type'       => array( $this->post_type ),
					'post_type_label' => array( __( 'Media' ) ),
				)
			);

			$this->set_hooks();
		}

		function set_hooks() {
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbars' ) );
			add_action( 'vg_sheet_editor/editor/register_columns', array( $this, 'register_columns' ) );
			add_filter( 'vg_sheet_editor/filters/after_fields', array( $this, 'add_filters_fields' ), 10, 2 );
			add_filter( 'vg_sheet_editor/load_rows/wp_query_args', array( $this, 'filter_posts' ), 12, 2 );
			add_filter( 'vg_sheet_editor/columns/all_items', array( $this, 'reorder_columns' ), 5 );
			add_filter( 'vg_sheet_editor/prepared_post_types', array( $this, 'rename_media_sheet' ) );
			add_filter( 'vg_sheet_editor/provider/post/get_item', array( $this, 'modify_default_status' ) );
			add_filter( 'vg_sheet_editor/options_page/options', array( $this, 'add_settings_page_options' ) );
			add_filter( 'vg_sheet_editor/filters/allowed_fields', array( $this, 'remove_unnecessary_search_options' ), 10, 2 );
			add_filter( 'vg_sheet_editor/advanced_filters/all_fields_groups', array( $this, 'remove_unnecessary_advanced_search_options' ), 10, 2 );
			add_filter( 'vg_sheet_editor/custom_columns/column_type', array( $this, 'exclude_sizes_meta_data_from_columns' ), 10, 3 );
			add_filter( 'posts_clauses', array( $this, 'search_by_parent_post_type' ), 10, 2 );
			add_action( 'vg_sheet_editor/after_enqueue_assets', array( $this, 'register_assets' ) );
			add_action( 'wp_ajax_vgse_attach_images', array( $this, 'attach_images' ) );

			add_filter( 'vg_sheet_editor/import/save_rows_args', array( $this, 'disable_creation_during_import' ) );
			add_filter( 'vg_sheet_editor/provider/post/meta_field_unique_values', array( $this, 'detect_subfields_for_all_file_types' ), 10, 3 );
			add_filter( 'vg_sheet_editor/provider/post/update_item_meta', array( $this, 'sync_alt_text_on_post_content' ), 10, 3 );
			add_filter( 'vg_sheet_editor/provider/post/data_updated', array( $this, 'sync_title_on_post_content' ), 10, 2 );
			add_filter( 'vg_sheet_editor/import/find_post_id', array( $this, 'find_image_id_for_import' ), 10, 6 );
			add_filter( 'vg_sheet_editor/js_data', array( $this, 'modify_preview_size' ), 10, 2 );
			add_filter( 'vg_sheet_editor/handsontable/custom_args', array( $this, 'render_all_columns_for_bigger_sizes' ), 10, 3 );
			add_filter( 'vg_sheet_editor/import/wp_check/available_columns_options', array( $this, 'filter_wp_check_options_for_import' ), 10, 2 );
			add_filter( 'vg_sheet_editor/import/after_available_columns_options', array( $this, 'render_similar_file_name_column_for_import' ) );
			add_action( 'media_buttons', array( $this, 'render_tinymce_editor_shortcut' ) );
			add_action( 'vg_sheet_editor/editor_page/before_toolbars', array( $this, 'maybe_attach_post_images' ) );
			add_action( 'vg_sheet_editor/filters/after_advanced_fields_section', array( $this, 'add_advanced_search_options' ) );
			add_action( 'vg_sheet_editor/save/after_image_url_saved', array( $this, 'after_image_saved_from_url' ), 10, 6 );
			add_action( 'vg_sheet_editor/import/after_advanced_options', array( $this, 'add_import_option_to_auto_rename_files' ) );
			add_filter( 'vg_sheet_editor/import/rows_before_find_existing_id', array( $this, 'maybe_download_external_images_before_import' ), 10, 2 );
			add_filter( 'vg_sheet_editor/formulas/formula_after_placeholders_replaced', array( $this, 'formula_replace_parent_placeholders' ), 10, 5 );
			add_filter( 'vg_sheet_editor/filters/sanitize_request_filters', array( $this, 'register_custom_filters' ), 10, 2 );
		}

		public function formula_replace_parent_placeholders( $formula, $data, $post_id, $cell_args, $post_type ) {
			if ( $post_type !== $this->post_type || strpos( $formula, '$post:' ) === false ) {
				return $formula;
			}

			$post   = get_post( $post_id );
			$parent = get_post( $post->post_parent );
			preg_match_all( '/\$post:(tax|meta|data):([^\$]+)\$/', $formula, $matches );
			if ( empty( $matches ) || empty( $matches[0] ) ) {
				return $formula;
			}
			foreach ( $matches[1] as $match_index => $data_source ) {
				if ( ! isset( $matches[2][ $match_index ] ) ) {
					continue;
				}
				$key      = $matches[2][ $match_index ];
				$variable = $matches[0][ $match_index ];
				$value    = '';
				if ( $post->post_parent && $parent ) {
					if ( $data_source === 'meta' ) {
						$value = get_post_meta( $parent->ID, $key, true );
					} elseif ( $data_source === 'tax' ) {
						$terms = wp_get_object_terms( $parent->ID, $key, array( 'fields' => 'names' ) );
						$value = is_array( $terms ) ? implode( ', ', $terms ) : '';
					} elseif ( $data_source === 'data' ) {
						$value = property_exists( $parent, $key ) ? $parent->$key : '';
					}
				}

				$formula = str_replace( $variable, $value, $formula );
			}
			return $formula;
		}

		public function maybe_download_external_images_before_import( $rows, $post_type ) {
			if ( $post_type !== $this->post_type ) {
				return $rows;
			}

			$first_row = current( $rows );
			if ( isset( $first_row['wpse_new_file_url'] ) ) {
				foreach ( $rows as $index => $row ) {
					if ( ! empty( $row['wpse_new_file_url'] ) ) {
						$file_ids = VGSE()->helpers->maybe_replace_urls_with_file_ids( $row['wpse_new_file_url'] );
						if ( ! empty( $file_ids ) && is_array( $file_ids ) ) {
							$image_id             = current( $file_ids );
							$rows[ $index ]['ID'] = (int) $image_id;
						}
					}
				}
			}
			return $rows;
		}

		public function add_import_option_to_auto_rename_files( $post_type ) {
			if ( ! post_type_exists( $post_type ) ) {
				return;
			}
			?>
			<div class="field">
				<label><input type="checkbox" name="auto_rename_image_files" class="auto_rename_image_files"/> <?php _e( 'Auto rename image files?', vgse_media_library()->textname ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php _e( 'You need to add the new file name at the end of the image URL separated with 2 colons (::). for example: https://site.com/1234.jpg::my-great-file.jpg - will rename the file 1234.jpg into my-great-file.jpg. This only works for image columns.', vgse_media_library()->textname ); ?>">( ? )</a></label>								
			</div>
			<?php
		}

		public function after_image_saved_from_url( $file_id, $alt_text, $image_title, $image_caption, $image_description, $file_name ) {
			if ( ! empty( $alt_text ) ) {
				$alt_text = urldecode( $alt_text );
				update_post_meta( $file_id, '_wp_attachment_image_alt', $alt_text );
				$import_settings = WPSE_CSV_API_Obj()->get_current_import_settings();
				if ( ! empty( $import_settings ) && ! empty( $import_settings['auto_rename_image_files'] ) && ! $file_name ) {
					WPSE_Rename_Media_Files_Obj()->_rename_file( $file_id, str_replace( '.', ' ', $alt_text ), false );
				}
			}
			if ( is_string( $file_name ) && ! empty( $file_name ) ) {
				WPSE_Rename_Media_Files_Obj()->_rename_file( $file_id, $file_name, false );
			}

			$image_data = array(
				'ID' => $file_id,
			);

			if ( $image_title ) {
				$image_data['post_title'] = $image_title;
			}
			if ( $image_caption ) {
				$image_data['post_excerpt'] = $image_caption;
			}
			if ( $image_description ) {
				$image_data['post_content'] = $image_description;
			}
			if ( count( $image_data ) > 1 ) {
				wp_update_post( $image_data );
			}
		}

		public function add_advanced_search_options( $post_type ) {
			if ( $post_type !== $this->post_type ) {
				return;
			}
			?>

			<li>
				<label><input type="checkbox" name="wpse_is_featured_image" value="yes"> <?php _e( 'Is image used as featured image?', vgse_media_library()->textname ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php echo esc_attr( __( 'It searches all images being used as a featured image of a post', vgse_media_library()->textname ) ); ?>">( ? )</a></label>
			</li>
			<?php
		}

		public function maybe_attach_post_images( $post_type ) {
			if ( $post_type !== $this->post_type || empty( $_REQUEST['wpse_attach_images_from_post'] ) || ! is_numeric( $_REQUEST['wpse_attach_images_from_post'] ) ) {
				return;
			}

			$post_id = (int) $_REQUEST['wpse_attach_images_from_post'];
			$this->attach_images_from_post( $post_id );
		}

		public function render_tinymce_editor_shortcut( $editor_id ) {
			global $post;
			if ( ! is_object( $post ) || $editor_id !== 'content' || wp_doing_ajax() || wp_doing_cron() || ! is_admin() ) {
				return;
			}
			?>
			<a class="button wpse-open-media-sheet-from-post" href="<?php echo esc_url( VGSE()->helpers->get_editor_url( 'attachment' ) ); ?>" target="_blank" ><?php _e( 'Edit Images in a Spreadsheet', vgse_media_library()->textname ); ?></a>
			<input type="hidden" class="wpse-media-data" name="wpse_custom_filters[meta_query][0][compare]" value="=" />
			<input type="hidden" class="wpse-media-data" name="wpse_custom_filters[meta_query][0][key]" value="post_parent" />
			<input type="hidden" class="wpse-media-data" name="wpse_custom_filters[meta_query][0][source]" value="post_data" />
			<input type="hidden" class="wpse-media-data" name="wpse_custom_filters[meta_query][0][value]" value="<?php echo (int) $post->ID; ?>" />
			<input type="hidden" class="wpse-media-data" name="wpse_attach_images_from_post" value="<?php echo (int) $post->ID; ?>" />
			<script>
				jQuery(document).ready(function () {
					jQuery('.wpse-open-media-sheet-from-post').on('click', function (e) {
						e.preventDefault();
						var $button = jQuery(this);
						var $form = jQuery('<form target="_blank" action="' + $button.attr('href') + '" method="POST"></form>');
						$form.append(jQuery('.wpse-media-data').clone());
						jQuery('body').append($form);
						$form.submit();
						$form.remove();
					});
				});
			</script>
			<?php
		}

		public function render_similar_file_name_column_for_import( $post_type ) {

			if ( $post_type === $this->post_type ) {
				echo '<option value="wpse_similar_file_name">' . __( 'Similar file name', vgse_media_library()->textname ) . '</option>';
				echo '<option value="wpse_new_file_url">' . __( 'Download file from this URL', vgse_media_library()->textname ) . '</option>';
			}
		}

		public function filter_wp_check_options_for_import( $columns, $post_type ) {

			if ( $post_type === $this->post_type ) {
				// The array elements contain the <option> html, so we use str_replace to change the option key
				$columns['wpse_similar_file_name'] = '<option value="wpse_similar_file_name">' . __( 'Similar file name', vgse_media_library()->textname ) . '</option>';
			}
			return $columns;
		}

		public function render_all_columns_for_bigger_sizes( $settings, $provider, $post_type = null ) {
			if ( $post_type !== $this->post_type || empty( VGSE()->options['media_preview_width'] ) ) {
				return $settings;
			}

			$settings['viewportColumnRenderingOffset'] = 999;
			return $settings;
		}

		public function modify_preview_size( $settings, $post_type ) {
			if ( $post_type !== $this->post_type ) {
				return $settings;
			}

			$settings['media_cell_preview_width']      = ! empty( VGSE()->options['media_sheet_preview_width'] ) ? (int) VGSE()->options['media_sheet_preview_width'] : 125;
			$settings['media_cell_preview_max_height'] = ! empty( VGSE()->options['media_sheet_preview_height'] ) ? (int) VGSE()->options['media_sheet_preview_height'] : 90;

			return $settings;
		}

		public function find_image_id_for_import( $image_id, $row, $post_type, $meta_query, $writing_type, $check_wp_fields ) {
			global $wpdb;
			if ( $post_type !== $this->post_type ) {
				return $image_id;
			}
			// Allow to update images by file name
			if ( ! empty( $row['phoenix_new_filename'] ) && in_array( 'phoenix_new_filename', $check_wp_fields ) ) {
				$file_name = $row['phoenix_new_filename'];
				$sql       = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND (meta_value LIKE %s OR meta_value = %s) LIMIT 1", '%/' . $wpdb->esc_like( $file_name ), $file_name );
				$new_id    = (int) $wpdb->get_var( $sql );
				if ( $new_id ) {
					$image_id = $new_id;
				}
			}
			if ( ! empty( $row['wpse_similar_file_name'] ) && in_array( 'wpse_similar_file_name', $check_wp_fields ) ) {
				$file_name_parts   = explode( '.', $row['wpse_similar_file_name'] );
				$file_name_keyword = current( $file_name_parts );

				if ( strlen( $file_name_keyword ) > 4 ) {
					$sql    = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%/%' . $wpdb->esc_like( $file_name_keyword ) . '%' );
					$new_id = $wpdb->get_col( $sql );
					if ( $new_id ) {
						$image_id = $new_id;
					}
				}
			}
			return $image_id;
		}

		public function sync_alt_text_on_post_content( $value, $id, $key ) {
			if ( get_post_type( $id ) !== $this->post_type || $key !== '_wp_attachment_image_alt' ) {
				return $value;
			}
			$this->_sync_image_field_on_post_content( $id, $value, 'alt' );
			$this->_sync_image_field_on_siteorigin_builder( $id, $value, 'alt' );
			return $value;
		}

		public function sync_title_on_post_content( $id, $values ) {
			if ( get_post_type( $id ) !== $this->post_type || ! isset( $values['post_title'] ) ) {
				return;
			}

			$this->_sync_image_field_on_post_content( $id, $values['post_title'], 'title' );
			$this->_sync_image_field_on_siteorigin_builder( $id, $values['post_title'], 'title' );
		}

		public function _sync_image_field_on_siteorigin_builder( $id, $value, $html_attribute_name ) {
			if ( ! class_exists( 'SiteOrigin_Panels' ) ) {
				return;
			}
			// Prevent performance issues, we want a quick post update
			VGSE()->helpers->remove_all_post_actions( $this->post_type );

			$posts_using_image = $this->find_posts_containing_hardcoded_image( $id, 5 );
			$all_image_urls    = $this->_get_all_urls_related_to_image( $id );

			$regex = '/<img.*?src="(.*?)"[^\>]+>/s';

			foreach ( $posts_using_image as $post_id ) {
				$panels_data = get_post_meta( $post_id, 'panels_data', true );
				if ( empty( $panels_data ) ) {
					continue;
				}

				$content = json_encode( $panels_data );
				if ( ! $content ) {
					continue;
				}
				if ( json_decode( $content, true ) !== $panels_data ) {
					continue;
				}

				$new_content = $this->_sync_image_field_on_string( $all_image_urls, $content, $value, $html_attribute_name );

				if ( $new_content && $new_content !== $content ) {
					$new_content_final = json_decode( $new_content, true );
					update_post_meta( $post_id, 'panels_data', $new_content_final );
				}
			}

			return $value;
		}

		public function _sync_image_field_on_string( $all_image_urls, $original_content, $value, $html_attribute_name ) {
			$regex         = '/<img.*?src=\\\"(.*?)\\\"[^\>]+>/s';
			$content       = $original_content;
			$total_matches = preg_match_all( $regex, $content, $results );
			if ( ! $total_matches ) {
				return $original_content;
			}
			foreach ( $results[0] as $img_match_index => $img_tag ) {
				$url = wp_unslash( $results[1][ $img_match_index ] );
				if ( ! in_array( $url, $all_image_urls, true ) ) {
					continue;
				}
				$original_img_tag = $img_tag;

				// If the img tag does not have the attribute, add it empty so we can insert the attribute value
				if ( strpos( $img_tag, $html_attribute_name . '=' ) === false ) {
					$img_tag = str_replace( '<img', '<img ' . $html_attribute_name . '=\"\" ', $img_tag );
				}
				$img_tag_with_attribute = preg_replace( '/' . $html_attribute_name . '=\\\"(.*?)\\\"/s', $html_attribute_name . '=\"' . esc_attr( $value ) . '\"', $img_tag );
				$content                = str_replace( $original_img_tag, $img_tag_with_attribute, $content );
			}
			return $content;
		}
		public function _sync_image_field_on_post_content( $id, $value, $html_attribute_name ) {
			// Prevent performance issues, we want a quick post update
			VGSE()->helpers->remove_all_post_actions( $this->post_type );

			$posts_using_image = $this->find_posts_containing_hardcoded_image( $id, 5 );
			$all_image_urls    = $this->_get_all_urls_related_to_image( $id );

			$regex = '/<img.*?src="(.*?)"[^\>]+>/s';

			foreach ( $posts_using_image as $post_id ) {
				$post    = get_post( $post_id );
				$content = $post->post_content;

				$total_matches = preg_match_all( $regex, $content, $results );
				if ( ! $total_matches ) {
					continue;
				}

				foreach ( $results[0] as $img_match_index => $img_tag ) {
					$url = $results[1][ $img_match_index ];
					if ( ! in_array( $url, $all_image_urls, true ) ) {
						continue;
					}
					$original_img_tag = $img_tag;

					// If the img tag does not have the attribute, add it empty so we can insert the attribute value
					if ( strpos( $img_tag, $html_attribute_name . '=' ) === false ) {
						$img_tag = str_replace( '<img', '<img ' . $html_attribute_name . '="" ', $img_tag );
					}
					$img_tag_with_attribute = preg_replace( '/' . $html_attribute_name . '="(.*?)"/s', $html_attribute_name . '="' . esc_attr( $value ) . '"', $img_tag );
					$content                = str_replace( $original_img_tag, $img_tag_with_attribute, $content );
				}

				if ( $content !== $post->post_content ) {
					wp_update_post(
						wp_slash(
							array(
								'ID'           => $post_id,
								'post_content' => $content,
							)
						)
					);
				}
			}

			return $value;
		}

		public function detect_subfields_for_all_file_types( $values, $meta_key, $post_type ) {
			global $wpdb;

			if ( $meta_key !== '_wp_attachment_metadata' || $post_type !== $this->post_type ) {
				return $values;
			}
			$raw_values = $wpdb->get_col(
				"SELECT DISTINCT meta_value FROM $wpdb->postmeta pm 
INNER JOIN $wpdb->posts p 
ON p.ID = pm.post_id AND p.post_type = 'attachment' 
WHERE pm.meta_key = '_wp_attachment_metadata' AND pm.meta_value <> '' 
GROUP BY p.post_mime_type
ORDER BY LENGTH(pm.meta_value) DESC LIMIT 4"
			);
			$values     = array_map( 'maybe_unserialize', $raw_values );
			$out        = array();
			foreach ( $values as $value ) {
				$out = array_merge( $out, $value );
			}
			return array( $out );
		}

		public function disable_creation_during_import( $args ) {
			if ( $args['post_type'] === $this->post_type ) {
				$args['allow_to_create_new'] = false;
			}
			return $args;
		}

		public function get_images_found_in_post_content( $post ) {
			$post    = get_post( $post );
			$content = $post->post_content;
			$regex   = '/<img.*?src="(.*?)"[^\>]+>/s';
			$out     = array();

			$total_matches = preg_match_all( $regex, $content, $results );
			if ( ! $total_matches ) {
				return $out;
			}
			foreach ( $results[0] as $img_match_index => $img_tag ) {
				$url = $results[1][ $img_match_index ];
				if ( ! empty( $url ) ) {
					$out[] = $url;
				}
			}
			return $out;
		}

		public function attach_images_from_post( $post_id ) {
			global $wpdb;
			$featured_image_id   = (int) get_post_meta( $post_id, '_thumbnail_id', true );
			$gallery_images_id   = explode( ',', get_post_meta( $post_id, '_product_image_gallery', true ) );
			$content_images_urls = $this->get_images_found_in_post_content( $post_id );
			$content_images      = array();
			foreach ( $content_images_urls as $image_url ) {
				$image_id = VGSE()->helpers->get_attachment_id_from_url( $image_url );
				if ( $image_id ) {
					$content_images[] = $image_id;
				}
			}

			$images = array_filter( array_unique( array_map( 'intval', array_merge( array( $featured_image_id ), $gallery_images_id, $content_images ) ) ) );
			if ( empty( $images ) ) {
				return;
			}
			$ids_in_query_placeholders = implode( ', ', array_fill( 0, count( $images ), '%d' ) );
			$images_without_parent     = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = 0 AND post_type = 'attachment' AND ID IN ($ids_in_query_placeholders)", $images ) );

			// Prevent performance issues, we want a quick post update
			VGSE()->helpers->remove_all_post_actions( $this->post_type );
			foreach ( $images_without_parent as $image_id ) {
				wp_update_post(
					array(
						'ID'          => $image_id,
						'post_parent' => $post_id,
					)
				);
			}
		}

		public function attach_images() {
			global $wpdb;

			// Prevent performance issues, we want a quick post update
			VGSE()->helpers->remove_all_post_actions( $this->post_type );

			if ( ! VGSE()->helpers->user_can_edit_post_type( 'attachment' ) || empty( $_REQUEST['step'] ) || empty( VGSE()->helpers->get_nonce_from_request() ) || ! VGSE()->helpers->verify_nonce_from_request() ) {
				$message = array( 'message' => __( 'You dont have enough permissions to do this action.', 'vg_sheet_editor' ) );
				wp_send_json_error( $message );
			}
			$out = array(
				'step1_completed' => false,
				'step2_completed' => false,
				'step3_completed' => false,
			);

			$step = (int) $_REQUEST['step'];
			// Attach featured images
			if ( $step === 1 ) {
				$total_unattached_sql = "SELECT COUNT(*) FROM $wpdb->posts p WHERE post_type = 'attachment' AND post_mime_type LIKE '%image%' AND post_parent = 0";
				$total_unattached     = (int) $wpdb->get_var( apply_filters( 'vg_sheet_editor/media/total_unattached_images_sql', $total_unattached_sql ) );
				update_option( 'vgse_total_unattached_images', $total_unattached );

				$featured_images = $wpdb->get_results(
					apply_filters(
						'vg_sheet_editor/media/featured_images_sql',
						"SELECT post_id as post_parent, image.ID as ID FROM $wpdb->postmeta as meta 
JOIN $wpdb->posts as image ON image.ID = meta.meta_value 
WHERE image.post_parent = 0 AND image.post_type = 'attachment' AND meta.meta_key = '_thumbnail_id' AND meta_value > 0 GROUP BY meta_value LIMIT 500"
					),
					ARRAY_A
				);

				if ( empty( $featured_images ) ) {
					$out['step1_completed'] = true;
					$out['message']         = __( 'Step 1/3 completed: All featured images were attached.', vgse_media_library()->textname );
					wp_send_json_success( $out );
				}
				foreach ( $featured_images as $meta_row ) {
					wp_update_post( $meta_row );
				}
				$out['message'] = sprintf( __( 'Step 1/3 started: Finding featured images to attach...', vgse_media_library()->textname ), count( $featured_images ) );
			}

			if ( $step === 2 ) {
				$gallery_images_field_keys = array();
				if ( class_exists( 'WooCommerce' ) ) {
					$gallery_images_field_keys[] = '_product_image_gallery';
				}

				if ( function_exists( 'acf_get_field_groups' ) ) {
					$acf_groups = acf_get_field_groups();
					foreach ( $acf_groups as $acf ) {
						if ( empty( $acf['location'] ) ) {
							continue;
						}
						if ( empty( $acf['active'] ) ) {
							continue;
						}

						$location = wp_json_encode( $acf['location'] );
						if ( strpos( $location, '"post_type"' ) === false ) {
							continue;
						}
						$acf_fields     = acf_get_fields( $acf );
						$image_fields   = wp_list_filter( $acf_fields, array( 'type' => 'image' ) );
						$gallery_fields = wp_list_filter( $acf_fields, array( 'type' => 'gallery' ) );

						if ( ! empty( $image_fields ) ) {
							$gallery_images_field_keys = array_unique( array_merge( $gallery_images_field_keys, wp_list_pluck( $image_fields, 'name' ) ) );
						}
						if ( ! empty( $gallery_fields ) ) {
							$gallery_images_field_keys = array_unique( array_merge( $gallery_images_field_keys, wp_list_pluck( $gallery_fields, 'name' ) ) );
						}
					}
				}

				if ( ! empty( $gallery_images_field_keys ) ) {
					$keys_in_query_placeholders = implode( ', ', array_fill( 0, count( $gallery_images_field_keys ), '%s' ) );
					$sql                        = $wpdb->prepare( "SELECT post_id as post_parent, meta_value as images FROM $wpdb->postmeta WHERE meta_key IN ($keys_in_query_placeholders) AND meta_value != '0' AND meta_value <> '' GROUP BY meta_value", $gallery_images_field_keys );
					$all_gallery_images         = $wpdb->get_results( apply_filters( 'vg_sheet_editor/media/all_gallery_images_sql', $sql ), ARRAY_A );
					$all_gallery_image_ids      = array();
					$all_image_ids              = array();
					foreach ( $all_gallery_images as $images_row ) {
						$images_row_values = is_serialized( $images_row['images'] ) ? maybe_unserialize( $images_row['images'] ) : explode( ',', $images_row['images'] );
						if ( empty( $images_row_values ) ) {
							continue;
						}
						if ( ! isset( $all_gallery_image_ids[ $images_row['post_parent'] ] ) ) {
							$all_gallery_image_ids[ $images_row['post_parent'] ] = array();
						}
						$all_gallery_image_ids[ $images_row['post_parent'] ] = array_merge( $all_gallery_image_ids[ $images_row['post_parent'] ], $images_row_values );

						$all_image_ids = array_unique( array_merge( $all_image_ids, $all_gallery_image_ids[ $images_row['post_parent'] ] ) );
					}

					if ( ! empty( $all_image_ids ) ) {
						$ids_in_query_placeholders = implode( ', ', array_fill( 0, count( $all_image_ids ), '%d' ) );
						$sql                       = $wpdb->prepare( "SELECT ID FROM $wpdb->posts p WHERE post_type = 'attachment' AND post_parent > 0 AND ID IN ($ids_in_query_placeholders) ", $all_image_ids );

						$gallery_images_with_parent = $wpdb->get_col( apply_filters( 'vg_sheet_editor/media/gallery_images_with_parent_sql', $sql ) );

						$updated_images  = 0;
						$step2_completed = true;
						foreach ( $all_gallery_image_ids as $post_parent => $images ) {
							foreach ( $images as $image_id ) {
								if ( $updated_images > 500 ) {
									$step2_completed = false;
									break;
								}
								if ( ! in_array( (string) $image_id, $gallery_images_with_parent, true ) ) {
									++$updated_images;
									wp_update_post(
										array(
											'ID'          => $image_id,
											'post_parent' => $post_parent,
										)
									);
								}
							}
						}
					}
				} else {
					$step2_completed = true;
				}

				// FIFU Premium. Attach images saved as featured slider
				if ( defined( 'FIFU_PLUGIN_DIR' ) ) {
					$featured_slider_images = $wpdb->get_results(
						apply_filters(
							'vg_sheet_editor/media/featured_slider_images_sql',
							"SELECT post_id as post_parent, meta_value as url FROM $wpdb->postmeta 
	WHERE meta_key LIKE 'fifu_slider_image_url_%' AND meta_value <> '' GROUP BY meta_value"
						),
						ARRAY_A
					);

					foreach ( $featured_slider_images as $image ) {
						$image_id = VGSE()->helpers->get_attachment_id_from_url( $image['url'] );
						if ( ! $image_id ) {
							continue;
						}
						$current_parent_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_parent FROM $wpdb->posts WHERE post_type = 'attachment' AND ID = %d", $image_id ) );
						if ( $current_parent_id ) {
							continue;
						}
						wp_update_post(
							array(
								'ID'          => $image_id,
								'post_parent' => $image['post_parent'],
							)
						);
					}
				}

				if ( $step2_completed ) {
					$out['step2_completed'] = $step2_completed;
					$out['message']         = __( 'Step 2/3 completed: Product gallery images were attached.', vgse_media_library()->textname );
				} else {
					$out['step2_completed'] = $step2_completed;
					$out['message']         = __( 'Step 2/3 in progress...', vgse_media_library()->textname );
				}
				wp_send_json_success( $out );
			}
			if ( $step === 3 ) {
				$batch_size = (int) VGSE()->options['attach_images_batch_size'];
				if ( empty( $batch_size ) ) {
					$batch_size = 30;
				}
				$sql                  = apply_filters( 'vg_sheet_editor/media/unattached_image_ids_sql', $wpdb->prepare( "SELECT ID FROM $wpdb->posts p WHERE post_type = 'attachment' AND post_mime_type LIKE %s AND post_parent = 0 AND post_content_filtered != 'yes' LIMIT %d", '%image%', (int) $batch_size ) );
				$unattached_image_ids = $wpdb->get_col( $sql );

				if ( ! empty( $unattached_image_ids ) ) {
					$unattached_image_ids = array_filter( $unattached_image_ids, 'is_numeric' );
				}

				if ( empty( $unattached_image_ids ) ) {
					// Remove flag of processed images
					$wpdb->update(
						$wpdb->posts,
						array(
							'post_content_filtered' => '',
						),
						array(
							'post_content_filtered' => 'yes',
							'post_type'             => 'attachment',
						)
					);
					$out['step3_completed']    = true;
					$original_unattached_count = (int) get_option( 'vgse_total_unattached_images' );
					$total_unattached          = (int) $wpdb->get_var( apply_filters( 'vg_sheet_editor/media/total_unattached_images_sql', "SELECT COUNT(*) FROM $wpdb->posts p WHERE post_type = 'attachment' AND post_mime_type LIKE '%image%' AND post_parent = 0" ) );
					$out['message']            = sprintf( __( 'Step 3/3 completed: Images from the posts content were attached.<br>You had %1$d unattached images initially, we were able to attach %2$d, now you have %3$d unattached images.', vgse_media_library()->textname ), $original_unattached_count, $original_unattached_count - $total_unattached, $total_unattached );
					wp_send_json_success( $out );
				}

				// Add a flag to mark processed images

				$ids_in_query_placeholders = implode( ', ', array_fill( 0, count( $unattached_image_ids ), '%d' ) );
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content_filtered = 'yes' WHERE post_type = 'attachment'  AND ID IN ($ids_in_query_placeholders)", $unattached_image_ids ) );
				$attached_from_post_content = 0;
				$process_results            = array();
				foreach ( $unattached_image_ids as $image_id ) {
					$process_results[ $image_id ] = null;
					$posts_using_image            = $this->find_posts_containing_hardcoded_image( $image_id, 1 );
					if ( empty( $posts_using_image ) ) {
						continue;
					}
					$process_results[ $image_id ] = current( $posts_using_image );
					++$attached_from_post_content;
					wp_update_post(
						array(
							'ID'          => $image_id,
							'post_parent' => current( $posts_using_image ),
						)
					);
				}
				$out['unattached_images_found'] = $process_results;
				$out['unattached_images_sql']   = $sql;
				$out['message']                 = __( 'Step 3/3 started: Finding images from the posts content to attach...', vgse_media_library()->textname );
			}
			wp_send_json_success( $out );
		}

		public function _get_all_urls_related_to_image( $attachment_id ) {

			$all_urls  = array( wp_get_attachment_url( $attachment_id ) );
			$meta_data = wp_get_attachment_metadata( $attachment_id );

			if ( ! empty( $meta_data['sizes'] ) ) {

				foreach ( $meta_data['sizes'] as $size => $size_info ) {
					// We use wp_get_attachment_image_src() instead of get_post_meta() to make it work
					// with external storage plugins like Offload Media.
					$src = wp_get_attachment_image_src( $attachment_id, $size );
					if ( empty( $src ) ) {
						continue;
					}
					$all_urls[] = $src[0];
				}
			}
			return apply_filters( 'vg_sheet_editor/media/get_all_urls_related_to_image', array_unique( array_filter( $all_urls ) ), $attachment_id );
		}

		public function find_posts_containing_hardcoded_image( $attachment_id, $limit = 20 ) {
			global $wpdb;
			if ( ! $limit ) {
				$limit = 1;
			}
			$all_urls = $this->_get_all_urls_related_to_image( $attachment_id );
			if ( empty( $all_urls ) ) {
				return array();
			}
			$wheres        = array(
				'post_content RLIKE %s',
			);
			$prepared_data = array( '\[gallery [^\d\"]*(\"|,)' . (int) $attachment_id . '("|,).*\]' );
			foreach ( $all_urls as $url ) {
				$wheres[]        = ' p.post_content LIKE %s ';
				$prepared_data[] = '%' . $wpdb->esc_like( $url ) . '%';
			}
			// In reality, one image will not be used on many posts. We limit the search to 20 posts to prevent performance issues
			$sql             = apply_filters( 'vg_sheet_editor/media/sql_posts_containing_image_by_url', "SELECT p.ID FROM $wpdb->posts p WHERE p.post_status != 'inherit' AND ( " . implode( ' OR ', $wheres ) . ' ) LIMIT %d', $attachment_id, $limit );
			$prepared_data[] = $limit;
			$post_ids        = $wpdb->get_col( $wpdb->prepare( $sql, $prepared_data ) );
			return $post_ids;
		}

		public function register_assets() {
			wp_enqueue_script( 'media-sheet-js', vgse_media_library()->plugin_url . 'assets/js/init.js', array(), VGSE()->version, false );
		}

		public function search_by_parent_post_type( $clauses, $wp_query ) {
			global $wpdb;
			if ( ! empty( $wp_query->query['vgse_parent_post_type'] ) ) {
				if ( empty( $clauses['join'] ) ) {
					$clauses['join'] = ' JOIN ';
				}
				$clauses['join']  .= " $wpdb->posts as sepp1 ON sepp1.ID = $wpdb->posts.post_parent ";
				$clauses['where'] .= " AND sepp1.post_type = '" . esc_sql( $wp_query->query['vgse_parent_post_type'] ) . "' ";
			}
			return $clauses;
		}

		public function exclude_sizes_meta_data_from_columns( $out, $meta_key, $editor ) {
			if ( $this->post_type === $editor->args['provider'] && $meta_key === '_wp_attachment_metadata' ) {
				if ( isset( $out['sample_field']['sizes'] ) ) {
					unset( $out['sample_field']['sizes'] );
				}
			}

			return $out;
		}

		public function remove_unnecessary_advanced_search_options( $key_groups, $post_type ) {
			if ( $this->post_type === $post_type ) {
				$status_key = array_search( 'post_status', $key_groups['post_data'] );
				if ( $status_key !== false ) {
					unset( $key_groups['post_data'][ $status_key ] );
				}
			}

			return $key_groups;
		}

		public function remove_unnecessary_search_options( $filters, $post_type ) {
			if ( $this->post_type === $post_type ) {
				if ( isset( $filters['post_author'] ) ) {
					$filters['post_author']['label'] = __( 'Uploaded by these users', vgse_media_library()->textname );
				}
				if ( isset( $filters['post_status'] ) ) {
					unset( $filters['post_status'] );
				}
			}
			return $filters;
		}

		/**
		 * Add fields to options page
		 * @param array $sections
		 * @return array
		 */
		public function add_settings_page_options( $sections ) {
			$sections['media']             = array(
				'icon'   => 'el-icon-cogs',
				'title'  => __( 'Media sheet', vgse_media_library()->textname ),
				'fields' => array(
					array(
						'id'    => 'media_post_columns',
						'type'  => 'text',
						'title' => __( 'Show these columns from the associated post', vgse_media_library()->textname ),
						'desc'  => __( 'For example: When an image is uploaded to a product, we automatically show columns with the product title and product taxonomies (categories, attributes). You can use those columns to copy the values into the alt text or use them for exports, or any bulk edit. This option is for displaying additional post information in the media sheet. Enter the list of post meta keys separated by commas, every meta key will appear as individual column.', vgse_media_library()->textname ),
					),
					array(
						'id'       => 'media_sheet_preview_width',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Image preview width inside the cell', vgse_media_library()->textname ),
						'default'  => 125,
					),
					array(
						'id'       => 'media_sheet_preview_height',
						'type'     => 'text',
						'validate' => 'numeric',
						'title'    => __( 'Image preview height inside the cell', vgse_media_library()->textname ),
						'default'  => 90,
					),
					array(
						'id'    => 'media_allow_dettach_parent_post',
						'type'  => 'switch',
						'title' => __( 'Allow to dettach images from parent post?', vgse_media_library()->textname ),
						'desc'  => __( 'By default, the column "Attached to this post" is read only. Activate this option and the column will become editable, but the only edit that will be saved will be empty values to dettach the images from the associated posts, it wont be possible to attach images to another specific post.', vgse_media_library()->textname ),
					),
				),
			);
			$sections['speed']['fields'][] = array(
				'id'       => 'attach_images_batch_size',
				'type'     => 'text',
				'validate' => 'numeric',
				'title'    => __( 'How many images do you want to attach per batch?', vgse_media_library()->textname ),
				'default'  => 30,
				'desc'     => __( 'When you use the "Attach images" option, we will get 30 unattached images and find posts where the images are used and attach the images to those posts and we will repeat the process until it finishes. You can control the batch size to reduce the server usage.', vgse_media_library()->textname ),
			);
			return $sections;
		}

		public function modify_default_status( $item ) {
			if ( is_array( $item ) && $item['post_type'] === $this->post_type ) {
				$item['post_status'] = 'publish';
			} elseif ( is_object( $item ) && $item->post_type === $this->post_type ) {
				$item->post_status = 'publish';
			}

			return $item;
		}

		public function rename_media_sheet( $sheets ) {

			if ( isset( $sheets[ $this->post_type ] ) ) {
				$sheets[ $this->post_type ]['label'] = __( 'Media library', vgse_media_library()->textname );
			}
			return $sheets;
		}

		public function reorder_columns( $items ) {
			if ( ! isset( $items[ $this->post_type ] ) || ! isset( $items[ $this->post_type ]['guid'] ) ) {
				return $items;
			}

			$items[ $this->post_type ] = array_filter(
				array_merge(
					array(
						'ID'                       => array(),
						'guid'                     => array(),
						'post_title'               => array(),
						'_wp_attachment_image_alt' => array(),
						'post_parent'              => array(),
						'view_post_parent'         => array(),
						'parent_taxonomies'        => array(),
					),
					$items[ $this->post_type ]
				)
			);

			$all_columns = serialize( $items[ $this->post_type ] );
			if ( strpos( $all_columns, '_wp_attachment_metadata' ) !== false ) {
				foreach ( $items[ $this->post_type ] as $column_key => $column ) {
					if ( strpos( $column_key, '_wp_attachment_metadata_' ) === 0 ) {
						$items[ $this->post_type ][ $column_key ]['title'] = str_replace( 'Wp Attachment Metadata : ', '', $column['title'] );
					}
				}
			}

			return $items;
		}

		public function register_custom_filters( $sanitized_filters, $dirty_filters ) {

			if ( isset( $dirty_filters['vgse_parent_post_type'] ) ) {
				$sanitized_filters['vgse_parent_post_type'] = sanitize_text_field( $dirty_filters['vgse_parent_post_type'] );
			}
			if ( isset( $dirty_filters['parent_keyword'] ) ) {
				$sanitized_filters['parent_keyword'] = sanitize_text_field( $dirty_filters['parent_keyword'] );
			}
			if ( isset( $dirty_filters['parent_terms'] ) ) {
				$sanitized_filters['parent_terms'] = array_map( 'sanitize_text_field', $dirty_filters['parent_terms'] );
			}
			$sanitized_filters['wpse_is_featured_image'] = ! empty( $dirty_filters['wpse_is_featured_image'] );
			return $sanitized_filters;
		}

		/**
		 * Apply filters to wp-query args
		 * @param array $query_args
		 * @param array $data
		 * @return array
		 */
		public function filter_posts( $query_args, $data ) {
			global $wpdb;
			if ( $query_args['post_type'] !== $this->post_type ) {
				return $query_args;
			}

			if ( ! empty( $data['filters'] ) ) {
				$filters = WP_Sheet_Editor_Filters::get_instance()->get_raw_filters( $data );

				if ( ! empty( $filters['vgse_parent_post_type'] ) ) {
					$query_args['vgse_parent_post_type'] = sanitize_text_field( $filters['vgse_parent_post_type'] );
				}
				if ( ! empty( $filters['parent_keyword'] ) ) {
					$editor     = VGSE()->helpers->get_provider_editor( $this->post_type );
					$parent_ids = $editor->provider->get_item_ids_by_keyword( $filters['parent_keyword'], false );

					$query_args['post_parent__in'] = ( ! empty( $parent_ids ) ) ? $parent_ids : array( PHP_INT_MAX );
				}

				if ( ! empty( $filters['parent_terms'] ) && is_array( $filters['parent_terms'] ) ) {

					$taxonomies_group        = array();
					$filters['parent_terms'] = array_map( 'sanitize_text_field', $filters['parent_terms'] );

					foreach ( $filters['parent_terms'] as $term ) {
						$term_parts = explode( '--', $term );
						if ( count( $term_parts ) !== 2 ) {
							continue;
						}
						$taxonomy = $term_parts[0];
						$term     = $term_parts[1];

						if ( ! isset( $taxonomies_group[ $taxonomy ] ) ) {
							$taxonomies_group[ $taxonomy ] = array();
						}
						$taxonomies_group[ $taxonomy ][] = $term;
					}

					$tax_query = array(
						'relation' => 'AND',
					);

					foreach ( $taxonomies_group as $taxonomy_key => $terms ) {
						$tax_query[] = array(
							'taxonomy' => $taxonomy_key,
							'field'    => 'slug',
							'terms'    => $terms,
						);
					}

					$post_types_with_taxonomies = array();
					foreach ( VGSE()->helpers->get_all_post_types_names() as $post_type ) {
						if ( get_object_taxonomies( $post_type ) ) {
							$post_types_with_taxonomies[] = $post_type;
						}
					}

					$parents                       = new WP_Query(
						array(
							'post_type'      => $post_types_with_taxonomies,
							'post_status'    => array( 'publish', 'draft' ),
							'fields'         => 'ids',
							'posts_per_page' => -1,
							'nopaging'       => true,
							'tax_query'      => $tax_query,
						)
					);
					$parent_ids                    = ( ! empty( $query_args['post_parent__in'] ) ) ? array_intersect( $parents->posts, $query_args['post_parent__in'] ) : $parents->posts;
					$query_args['post_parent__in'] = ( ! empty( $parent_ids ) ) ? $parent_ids : array( PHP_INT_MAX );
				}
				if ( ! empty( $filters['wpse_is_featured_image'] ) ) {
					// This query is not optimal for performance but it's a very advanced feature that few people will use
					// We will optimize it if many people use it
					$featured_images_ids = array_filter( array_map( 'intval', $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND meta_value > 0 GROUP BY meta_value LIMIT 100000" ) ) );

					$query_args['post__in'] = ( ! empty( $query_args['post__in'] ) ) ? array_intersect( $query_args['post__in'], $featured_images_ids ) : $featured_images_ids;
				}
			}

			return $query_args;
		}

		public function add_filters_fields( $current_post_type, $filters ) {
			if ( $current_post_type !== $this->post_type ) {
				return;
			}

			$labels = wp_list_pluck(
				wp_list_filter(
					get_taxonomies(
						array(
							'show_ui'      => true,
							'hierarchical' => true,
						),
						'objects'
					)
				),
				'label'
			);
			?>

			<li>
				<label><?php _e( 'Keyword in title of associated post', vgse_media_library()->textname ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php echo esc_attr( __( 'It searches in the title of the post that contains the image. <br>i.e. find images from products containing the keyword music.<br/>Search by multiple keywords separating keywords with a semicolon (;)' ) ); ?>">( ? )</a></label><input type="text" name="parent_keyword" />
			</li>
			<li class="">
				<label><?php _e( 'Files associated to', vgse_media_library()->textname ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php _e( 'Example: Find images used by posts or products', vgse_media_library()->textname ); ?>">( ? )</a></label>
				<select name="vgse_parent_post_type">
					<option value=""><?php _e( 'Any post type', vgse_media_library()->textname ); ?></option>
					<?php
					foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
						?>
						<option value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->label ); ?></option>
						<?php
					}
					?>

				</select>
			</li>
			<li class="">
				<label><?php printf( __( 'Taxonomies in associated post: %s', vgse_media_library()->textname ), esc_html( implode( ', ', array_unique( $labels ) ) ) ); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php printf( __( 'Example: Find images of products from the category Shoes. <br>Enter the names of %s', vgse_media_library()->textname ), esc_html( implode( ', ', $labels ) ) ); ?>">( ? )</a></label>
				<select data-global-search="1" data-placeholder="<?php _e( 'Category name...', vgse_media_library()->textname ); ?>" name="parent_terms[]" class="select2"  multiple data-remote="true" data-action="vgse_search_taxonomy_terms" data-min-input-length="4">

				</select>
			</li>
			<?php
		}

		/**
		 * Register toolbar items
		 */
		public function register_columns( $editor ) {
			$post_type = $this->post_type;
			if ( ! in_array( $editor->args['provider'], array( $post_type ) ) ) {
				return;
			}
			$editor->args['columns']->register_item(
				'guid',
				$post_type,
				array(
					'data_type'            => 'post_data',
					'column_width'         => 150,
					'get_value_callback'   => array( $this, 'get_attachment_preview' ),
					'type'                 => 'boton_gallery',
					'supports_formulas'    => false,
					'title'                => __( 'Preview', vgse_media_library()->textname ),
					'allow_to_save'        => false,
					'forced_allow_to_save' => false,
					'allow_to_save_sanitization' => false,
				)
			);

			$editor->args['columns']->register_item(
				'post_mime_type',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 150,
					'title'             => __( 'Format', vgse_media_library()->textname ),
					'supports_formulas' => false,
					'formatted'         => array(
						'renderer' => 'text',
						'readOnly' => true,
					),
					'allow_to_save'     => false,
					'is_locked'         => true,
				)
			);
			$editor->args['columns']->register_item(
				'_wp_attachment_image_alt',
				$post_type,
				array(
					'data_type'             => 'meta_data',
					'column_width'          => 150,
					'title'                 => __( 'Alt text', vgse_media_library()->textname ),
					'supports_formulas'     => true,
					'supports_sql_formulas' => false,
				)
			);
			$editor->args['columns']->register_item(
				'view_post_parent',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 190,
					'title'              => __( 'View Associated Post', vgse_media_library()->textname ),
					'type'               => 'external_button',
					'supports_formulas'  => false,
					'allow_to_save'      => false,
					'get_value_callback' => array( $this, 'get_associated_post_url' ),
				)
			);
			if ( ! empty( VGSE()->options['media_allow_dettach_parent_post'] ) ) {
				$editor->args['columns']->register_item(
					'post_parent',
					$post_type,
					array(
						'data_type'          => 'post_data',
						'column_width'       => 380,
						'title'              => __( 'Attached to this post', vgse_media_library()->textname ),
						'get_value_callback' => array( $this, 'get_attachment_parent' ),
						'supports_formulas'  => true,
						'allow_to_save'      => true,
						'is_locked'          => true,
						'lock_template_key'  => 'enable_lock_cell_template',
					)
				);
			} else {
				$editor->args['columns']->register_item(
					'post_parent',
					$post_type,
					array(
						'data_type'          => 'post_data',
						'column_width'       => 380,
						'title'              => __( 'Attached to this post', vgse_media_library()->textname ),
						'supports_formulas'  => true,
						'allow_to_save'      => false,
						'is_locked'          => true,
						'get_value_callback' => array( $this, 'get_attachment_parent' ),
					)
				);
			}
			$editor->args['columns']->register_item(
				'parent_taxonomies',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 380,
					'title'              => __( 'Taxonomies of parent post', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_parent_taxonomies' ),
				)
			);

			$parent_meta_keys = ( ! empty( VGSE()->options['media_post_columns'] ) ) ? array_map( 'trim', explode( ',', VGSE()->options['media_post_columns'] ) ) : array();
			foreach ( $parent_meta_keys as $parent_meta_key ) {
				$label = vgse_custom_columns_init()->_convert_key_to_label( $parent_meta_key );
				$editor->args['columns']->register_item(
					'parent_meta_key_' . $parent_meta_key,
					$post_type,
					array(
						'data_type'          => 'post_data',
						'column_width'       => 190,
						'title'              => sprintf( __( 'Associated post: %s', vgse_media_library()->textname ), $label ),
						'supports_formulas'  => true,
						'allow_to_save'      => false,
						'get_value_callback' => array( $this, 'get_parent_meta_field' ),
						'is_locked'          => true,
					)
				);
			}

			$editor->args['columns']->register_item(
				'wpse_image_width',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 116,
					'title'              => __( 'Image width (px)', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_image_width' ),
				)
			);
			$editor->args['columns']->register_item(
				'wpse_image_height',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 116,
					'title'              => __( 'Image height (px)', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_image_height' ),
				)
			);
			$editor->args['columns']->register_item(
				'file_size',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 90,
					'title'              => __( 'File size (kb)', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_file_size' ),
				)
			);
			$editor->args['columns']->register_item(
				'local_file_exists',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 90,
					'title'              => __( 'Local file exists', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_file_exists' ),
				)
			);

			$editor->args['columns']->register_item(
				'view_post',
				$post_type,
				array(
					'get_value_callback' => array( $this, 'get_view_file_url' ),
				),
				true
			);
			$editor->args['columns']->register_item(
				'post_content',
				$post_type,
				array(
					'title' => __( 'Description', vgse_media_library()->textname ),
				),
				true
			);

			$post_status_column = $editor->args['columns']->get_item( 'post_status', $post_type, false, true );

			if ( isset( $post_status_column['formatted']['selectOptions']['publish'] ) ) {
				$post_status_column['formatted']['selectOptions'] = array(
					'publish' => $post_status_column['formatted']['selectOptions']['publish'],
					'delete'  => $post_status_column['formatted']['selectOptions']['delete'],
				);
			}

			$editor->args['columns']->register_item(
				'post_status',
				$post_type,
				array(
					'formatted' => $post_status_column['formatted'],
				),
				true
			);
			$editor->args['columns']->register_item(
				'post_excerpt',
				$post_type,
				array(
					'title' => __( 'Caption', vgse_media_library()->textname ),
				),
				true
			);
			$editor->args['columns']->register_item(
				'post_author',
				$post_type,
				array(
					'title' => __( 'Uploaded by', vgse_media_library()->textname ),
				),
				true
			);
			$editor->args['columns']->register_item(
				'open_wp_editor',
				$post_type,
				array(
					'title' => __( 'File editor', vgse_media_library()->textname ),
				),
				true
			);
			$editor->args['columns']->register_item(
				'post_parent_slug',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 380,
					'title'              => __( 'Parent post slug', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_attachment_parent_slug' ),
				)
			);
			$editor->args['columns']->register_item(
				'parent_taxonomy_slug',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 380,
					'title'              => __( 'Parent post taxonomy slugs', vgse_media_library()->textname ),
					'supports_formulas'  => true,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_parent_taxonomy_slug' ),
				)
			);
		}

		public function get_view_file_url( $post, $column_key ) {
			return wp_get_attachment_url( $post->ID );
		}

		public function get_attachment_preview( $post, $column_key ) {
			$src = ( strpos( $post->post_mime_type, 'image/' ) !== false ) ? wp_get_attachment_image_src( $post->ID, 'medium', false ) : wp_get_attachment_image_src( $post->ID, 'thumbnail', true );

			$url = ( is_array( $src ) && ! empty( $src['0'] ) ) ? $src['0'] : '';

			if ( strpos( $url, includes_url( 'images/media' ) ) !== false ) {
				$url = basename( wp_get_attachment_url( $post->ID ) );
			}
			// Some plugins allow to upload images using external URLs and set this mime type, so we force WPSE to recognize an image extension to display the previews in the cells
			if ( $post->post_mime_type === 'image/url' ) {
				$url = add_query_arg( 'wpse_force_display', '.jpg', $url );
			}
			return $url;
		}

		public function get_parent_meta_field( $post, $column_key ) {
			$value = '';
			if ( $post->post_parent > 0 ) {
				$meta_key  = str_replace( 'parent_meta_key_', '', $column_key );
				$raw_value = get_post_meta( $post->post_parent, $meta_key, true );

				if ( is_array( $raw_value ) ) {
					$value = implode( ', ', $raw_value );
				} elseif ( is_string( $raw_value ) ) {
					$value = $raw_value;
				}
			}
			return $value;
		}

		public function get_parent_taxonomy_slug( $post, $column_key ) {
			$value = '';
			if ( $post->post_parent > 0 ) {
				$parent     = get_post( $post->post_parent );
				$taxonomies = wp_list_filter(
					get_object_taxonomies( $parent->post_type, 'objects' ),
					array(
						'show_ui'      => true,
						'hierarchical' => true,
					)
				);

				if ( ! empty( $taxonomies ) ) {
					$out = array();
					foreach ( $taxonomies as $taxonomy ) {
						$terms = wp_get_object_terms( $post->post_parent, $taxonomy->name, array( 'fields' => 'slugs' ) );
						if ( ! empty( $terms ) ) {
							$out[] = implode( '-', $terms );
						}
					}
					$value = implode( '-', $out );
				}
			}
			return $value;
		}

		public function get_parent_taxonomies( $post, $column_key ) {
			$value = '';
			if ( $post->post_parent > 0 ) {
				$parent     = get_post( $post->post_parent );
				$taxonomies = wp_list_filter(
					get_object_taxonomies( $parent->post_type, 'objects' ),
					array(
						'show_ui'      => true,
						'hierarchical' => true,
					)
				);

				if ( ! empty( $taxonomies ) ) {
					$out = array();
					foreach ( $taxonomies as $taxonomy ) {
						$terms = VGSE()->helpers->get_current_provider()->get_item_terms( $post->post_parent, $taxonomy->name );
						if ( ! empty( $terms ) ) {
							$out[] = $taxonomy->label . ': ' . $terms;
						}
					}
					$value = implode( '. ', $out );
				}
			}
			return $value;
		}

		public function get_file_exists( $post, $column_key ) {
			$file_path = get_attached_file( $post->ID );
			return ( $file_path && file_exists( $file_path ) ) ? __( 'Yes', vgse_media_library()->textname ) : __( 'No', vgse_media_library()->textname );
		}

		public function get_file_size( $post, $column_key ) {
			$file_path = get_attached_file( $post->ID );
			return ( $file_path && file_exists( $file_path ) ) ? (int) ( filesize( $file_path ) / 1024 ) : '';
		}

		public function get_image_width( $post, $column_key ) {
			$searchable_width = (int) get_post_meta( $post->ID, 'wpse_image_width', true );

			if ( ! $searchable_width ) {
				$meta             = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
				$searchable_width = ( ! empty( $meta ) && ! empty( $meta['width'] ) ) ? $meta['width'] : '';
				update_post_meta( $post->ID, 'wpse_image_width', $searchable_width );
			}
			return $searchable_width;
		}

		public function get_image_height( $post, $column_key ) {
			$searchable_height = (int) get_post_meta( $post->ID, 'wpse_image_height', true );

			if ( ! $searchable_height ) {
				$meta              = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
				$searchable_height = ( ! empty( $meta ) && ! empty( $meta['height'] ) ) ? $meta['height'] : '';
				update_post_meta( $post->ID, 'wpse_image_height', $searchable_height );
			}
			return $searchable_height;
		}

		public function get_associated_post_url( $post, $column_key ) {
			$value = '';
			if ( $post->post_parent > 0 && get_post_status( $post->post_parent ) ) {
				$value = get_permalink( $post->post_parent );
			}
			return $value;
		}

		public function get_attachment_parent( $post, $column_key ) {
			$value = '';
			if ( $post->post_parent > 0 && get_post_status( $post->post_parent ) ) {
				$parent = get_post( $post->post_parent );
				$value  = ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'vgse_bulk_edit_formula_big' ) ? '' : '(' . $parent->post_type . ') ';
				$value .= $parent->post_title;
			}
			return $value;
		}

		public function get_attachment_parent_slug( $post, $column_key ) {
			$value = '';
			if ( $post->post_parent > 0 && get_post_status( $post->post_parent ) ) {
				$parent = get_post( $post->post_parent );
				$value  = $parent->post_name;
			}
			return $value;
		}

		public function register_toolbars( $editor ) {
			$post_type = $this->post_type;
			if ( ! in_array( $editor->args['provider'], array( $post_type ) ) ) {
				return;
			}
			if ( WP_Sheet_Editor_Helpers::current_user_can( 'upload_files' ) ) {
				$editor->args['toolbars']->register_item(
					'add_rows',
					array(
						'type'                  => 'button', // html | switch | button
						'icon'                  => 'fa fa-upload',
						'url'                   => admin_url( 'media-new.php' ),
						'content'               => __( 'Upload files', vgse_media_library()->textname ),
						'extra_html_attributes' => ' target="_blank" ',
						'allow_in_frontend'     => false,
					),
					$post_type
				);
			} else {
				$editor->args['toolbars']->remove_item( 'add_rows', 'primary', $post_type );
			}
			$editor->args['toolbars']->register_item(
				'attach_images',
				array(
					'type'                  => 'button',
					'help_tooltip'          => __( 'Find posts using any image and attach the images to the posts. The images should be attached to posts so you can make searches by post title, taxonomies, post type, or bulk edits that rely on information of the post.', vgse_media_library()->textname ),
					'content'               => __( 'Attach images', vgse_media_library()->textname ),
					'icon'                  => 'fa fa-edit',
					'extra_html_attributes' => 'data-remodal-target="modal-attach-images"',
					'footer_callback'       => array( $this, 'render_attach_images_modal' ),
				),
				$post_type
			);
		}

		public function render_attach_images_modal( $post_type ) {
			?>
			<div class="remodal remodal-modal-attach-images" data-remodal-id="modal-attach-images" data-remodal-options="closeOnOutsideClick: false">

				<div class="modal-content">
					<h3><?php _e( 'Attach images', vgse_media_library()->textname ); ?></h3>
					<p><?php _e( 'We will find the posts using the images and attach the images to those posts.', vgse_media_library()->textname ); ?></p>

					<div class="response"></div>
					<button type="button" class="remodal-confirm start-attach-image"><?php _e( 'Start now', vgse_media_library()->textname ); ?></button>
					<button data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', vgse_media_library()->textname ); ?></button>
				</div>
			</div>
			<?php
		}
	}

	$GLOBALS['wpse_media_sheet_object'] = new WPSE_Media_Library_Sheet();
}
