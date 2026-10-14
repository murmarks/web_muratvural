<?php

if ( ! class_exists( 'WPSE_Rename_Media_Files_Lite' ) ) {

	/**
	 * Functions forked from the open-source, gpl plugin: Phoeniex Media Rename
	 * We only needed a few functions and we also needed to tweak it, so it feels
	 * better to load those functions here instead of requiring the installation of the full plugin.
	 * Big thanks to the developer of the Phoenix Media Rename plugin
	 */
	class WPSE_Rename_Media_Files_Lite {

		function __construct() {

		}

		/**
		 * Handles the actual rename process
		 *
		 * @param [type] $post_id
		 * @return void
		 */
		static function get_file_parts( $post_id ) {
			preg_match( '~([^/]+)\.([^\.]+)$~', get_attached_file( $post_id ), $file_parts ); // extract current filename and extension
			return array(
				'filename'  => $file_parts[1],
				'extension' => $file_parts[2],
			);
		}

		/**
		 * Returns the attachment URL and sizes URLs, in case of an image
		 *
		 * @param [type] $attachment_id
		 * @return void
		 */
		static function get_attachment_urls( $attachment_id ) {
			$urls = array( wp_get_attachment_url( $attachment_id ) );
			if ( wp_attachment_is_image( $attachment_id ) ) {
				foreach ( get_intermediate_image_sizes() as $size ) {
					$image  = wp_get_attachment_image_src( $attachment_id, $size );
					$urls[] = $image[0];
				}
			}

			return array_unique( $urls );
		}

		/**
		 * Convert filename to post title
		 *
		 * @param [type] $filename
		 * @return void
		 */
		static function filename_to_title( $filename ) {
			// return ucwords( preg_replace('~[^a-zA-Z0-9]~', ' ', $filename) );
			return $filename;
		}

		/**
		 * Unserializes a variable until reaching a non-serialized value
		 *
		 * @param [type] $var
		 * @return void
		 */
		static function unserialize_deep( $var ) {
			while ( is_serialized( $var ) ) {
				$var = @unserialize( $var );
			}

			return $var;
		}

		/**
		 * Handles the actual rename process
		 *
		 * @param [type] $attachment_id
		 * @param [type] $new_filename
		 * @param integer $retitle
		 * @return void
		 */
		static function do_rename_faster( $attachment_id, $new_filename, $retitle = 0, $update_in_post_content = true ) {
			global $wpdb;

			// Variables
			$post                     = get_post( $attachment_id );
			$file_parts               = self::get_file_parts( $attachment_id );
			$old_filename             = $file_parts['filename'];
			$new_filename_unsanitized = $new_filename;

			//sanitizing file name (using sanitize_title because sanitize_file_name doesn't remove accents)
			$new_filename = sanitize_file_name( remove_accents( $new_filename ) );

			$file_abs_path     = get_attached_file( $post->ID );
			$file_abs_dir      = dirname( $file_abs_path );
			$new_file_abs_path = preg_replace( '~[^/]+$~', $new_filename . '.' . $file_parts['extension'], $file_abs_path );

			$file_rel_path     = get_post_meta( $post->ID, '_wp_attached_file', 1 );
			$new_file_rel_path = preg_replace( '~[^/]+$~', $new_filename . '.' . $file_parts['extension'], $file_rel_path );

			$uploads_path = wp_upload_dir();
			$uploads_path = $uploads_path['basedir'];

			//attachment miniatures
			$searches = self::get_attachment_urls( $attachment_id );

			//Validations
			if ( ! $post ) {
				return __( 'Post with ID ' . $attachment_id . ' does not exist!' );
			}
			if ( $post && $post->post_type != 'attachment' ) {
				return __( 'Post with ID ' . $attachment_id . ' is not an attachment!', 'phoenix-media-rename' );
			}
			if ( ! $new_filename ) {
				return __( 'The field is empty!', 'phoenix-media-rename' );
			}
			//if ( ($new_filename != sanitize_file_name( remove_accents( $new_filename ) )) || preg_match('~[^\p{Common}\p{Latin}]~u', $new_filename) ) return __('Bad characters or invalid filename!', 'phoenix-media-rename');
			if ( $new_filename != sanitize_file_name( remove_accents( $new_filename ) ) ) {
				return __( 'Bad characters or invalid filename!', 'phoenix-media-rename' );
			}
			if ( file_exists( $new_file_abs_path ) ) {
				return __( 'A file with that name already exists in the containing folder!', 'phoenix-media-rename' );
			}
			if ( ! is_writable( $file_abs_dir ) ) {
				return __( 'The media containing directory is not writable!', 'phoenix-media-rename' );
			}

			// Change the attachment post
			$post_changes['ID']         = $post->ID;
			$post_changes['guid']       = preg_replace( '~[^/]+$~', $new_filename . '.' . $file_parts['extension'], $post->guid );
			$post_changes['post_title'] = ( $retitle ) ? self::filename_to_title( $new_filename_unsanitized ) : $post->post_title;
			$post_changes['post_name']  = wp_unique_post_slug( $new_filename, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
			wp_update_post( $post_changes );

			// Change attachment post metas & rename files
			foreach ( get_intermediate_image_sizes() as $size ) {
				$size_data = image_get_intermediate_size( $attachment_id, $size );
				if ( ! $size_data ) {
					continue;
				}

				@unlink( $uploads_path . DIRECTORY_SEPARATOR . $size_data['path'] );

				// Include webp files in the renaming process
				if ( file_exists( $uploads_path . DIRECTORY_SEPARATOR . $size_data['path'] . '.webp' ) ) {
					unlink( $uploads_path . DIRECTORY_SEPARATOR . $size_data['path'] . '.webp' );
				}
			}

			if ( ! @rename( $file_abs_path, $new_file_abs_path ) ) {
				return __( 'File renaming error!' );
			}
			// Include webp files in the renaming process
			if ( file_exists( $file_abs_path . '.webp' ) ) {
				rename( $file_abs_path . '.webp', $new_file_abs_path . '.webp' );
			}

			update_post_meta( $attachment_id, '_wp_attached_file', $new_file_rel_path );
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $new_file_abs_path ) );

			// Replace the old with the new media link in the content of all posts and metas
			$replaces = self::get_attachment_urls( $attachment_id );
			if ( $update_in_post_content ) {
				$search_file_names = array();
				// Use full paths (/wp-content/...) instead of just file names
				// Because it's possible that multiple images use the same file name in different date folders
				// Which can cause timeouts because it would try to replace across many unrelated images
				foreach ( $searches as $search_url ) {
					$search_file_names[] = parse_url( $search_url, PHP_URL_PATH );
				}

				$post_types = get_post_types();
				unset( $post_types['attachment'] );

				$meta_wheres                 = array();
				$post_data_wheres            = array();
				$meta_file_names_values      = array();
				$post_data_file_names_values = array();
				foreach ( $search_file_names as $search_file_name ) {
					$meta_wheres[]                 = ' meta_value LIKE %s ';
					$post_data_wheres[]            = ' post_content LIKE %s ';
					$meta_file_names_values[]      = '%' . $search_file_name . '%';
					$post_data_file_names_values[] = '%' . $search_file_name . '%';
				}
				$im = 0;
				while ( $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE " . implode( ' OR ', $meta_wheres ) . ' LIMIT ' . $im * 100 . ',100', $meta_file_names_values ) ) ) {
					if ( empty( $post_ids ) ) {
						break;
					}
					foreach ( $post_ids as $post_id ) {
						$page_template = get_post_meta( $post_id, '_wp_page_template', true );
						self::_replace_in_post_meta( $post_id, $searches, $replaces );
						// Sometimes WP deletes the page template during the wp_update_post call
						if ( $page_template ) {
							update_post_meta( $post->ID, '_wp_page_template', $page_template );
						}
					}
					// Don't update more than 1100 meta records
					if ( $im > 10 ) {
						break;
					}
					$im++;
				}

				$ip = 0;
				while ( $posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content FROM $wpdb->posts WHERE " . implode( ' OR ', $post_data_wheres ) . ' LIMIT ' . $ip * 100 . ',100', $post_data_file_names_values ) ) ) {
					if ( empty( $posts ) ) {
						break;
					}
					foreach ( $posts as $post ) {
						self::_replace_in_one_post( $post, $searches, $replaces );
					}
					$ip++;
				}

				// Updating options if necessary
				$options = self::get_all_options();
				foreach ( $options as $option ) {
					$option['value'] = self::unserialize_deep( $option['value'] );
					$new_option      = self::replace_media_urls( $option['value'], $searches, $replaces );
					if ( $new_option != $option['value'] ) {
						update_option( $option['name'], $new_option );
					}
				}

				//Updating SmartSlider 3 tables
				self::UpdateSmartSlider( $old_filename, $new_filename, $file_parts['extension'] );
			}

			do_action( 'vg_sheet_editor/media/after_file_name_replaced', $attachment_id, $searches, $replaces );

			return 1;
		}

		static function _replace_in_post_meta( $post_id, $searches, $replaces ) {
			// Updating post metas if necessary
			$metas = get_post_meta( $post_id );

			foreach ( $metas as $key => $meta ) {
				$meta[0]  = self::unserialize_deep( $meta[0] );
				$new_meta = self::replace_media_urls( $meta[0], $searches, $replaces );
				if ( $new_meta != $meta[0] ) {
					update_post_meta( $post_id, $key, $new_meta, $meta[0] );
				}
			}
		}

		static function _replace_in_one_post( $post, $searches, $replaces ) {
			// Updating post content if necessary
			$new_post                 = array( 'ID' => $post->ID );
			$new_post['post_content'] = str_replace( $searches, $replaces, $post->post_content );
			$page_template            = get_post_meta( $post->ID, '_wp_page_template', true );
			if ( $new_post['post_content'] != $post->post_content ) {
				wp_update_post( wp_slash( $new_post ) );
			}

			// Sometimes WP deletes the page template during the wp_update_post call
			if ( $page_template ) {
				update_post_meta( $post->ID, '_wp_page_template', $page_template );
			}
		}

		/**
		 * Handles the actual rename process
		 *
		 * @param [type] $attachment_id
		 * @param [type] $new_filename
		 * @param integer $retitle
		 * @return void
		 */
		static function do_rename( $attachment_id, $new_filename, $retitle = 0, $update_in_post_content = true ) {

			// Variables
			$post                     = get_post( $attachment_id );
			$file_parts               = self::get_file_parts( $attachment_id );
			$old_filename             = $file_parts['filename'];
			$new_filename_unsanitized = $new_filename;

			//sanitizing file name (using sanitize_title because sanitize_file_name doesn't remove accents)
			$new_filename = sanitize_file_name( remove_accents( $new_filename ) );

			$file_abs_path     = get_attached_file( $post->ID );
			$file_abs_dir      = dirname( $file_abs_path );
			$new_file_abs_path = preg_replace( '~[^/]+$~', $new_filename . '.' . $file_parts['extension'], $file_abs_path );

			$file_rel_path     = get_post_meta( $post->ID, '_wp_attached_file', 1 );
			$new_file_rel_path = preg_replace( '~[^/]+$~', $new_filename . '.' . $file_parts['extension'], $file_rel_path );

			$uploads_path = wp_upload_dir();
			$uploads_path = $uploads_path['basedir'];

			//attachment miniatures
			$searches = self::get_attachment_urls( $attachment_id );

			//Validations
			if ( ! $post ) {
				return __( 'Post with ID ' . $attachment_id . ' does not exist!' );
			}
			if ( $post && $post->post_type != 'attachment' ) {
				return __( 'Post with ID ' . $attachment_id . ' is not an attachment!', 'phoenix-media-rename' );
			}
			if ( ! $new_filename ) {
				return __( 'The field is empty!', 'phoenix-media-rename' );
			}
			//if ( ($new_filename != sanitize_file_name( remove_accents( $new_filename ) )) || preg_match('~[^\p{Common}\p{Latin}]~u', $new_filename) ) return __('Bad characters or invalid filename!', 'phoenix-media-rename');
			if ( $new_filename != sanitize_file_name( remove_accents( $new_filename ) ) ) {
				return __( 'Bad characters or invalid filename!', 'phoenix-media-rename' );
			}
			if ( file_exists( $new_file_abs_path ) ) {
				return __( 'A file with that name already exists in the containing folder!', 'phoenix-media-rename' );
			}
			if ( ! is_writable( $file_abs_dir ) ) {
				return __( 'The media containing directory is not writable!', 'phoenix-media-rename' );
			}

			// Change the attachment post
			$post_changes['ID']         = $post->ID;
			$post_changes['guid']       = preg_replace( '~[^/]+$~', $new_filename . '.' . $file_parts['extension'], $post->guid );
			$post_changes['post_title'] = ( $retitle ) ? self::filename_to_title( $new_filename_unsanitized ) : $post->post_title;
			$post_changes['post_name']  = wp_unique_post_slug( $new_filename, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
			wp_update_post( $post_changes );

			// Change attachment post metas & rename files
			foreach ( get_intermediate_image_sizes() as $size ) {
				$size_data = image_get_intermediate_size( $attachment_id, $size );

				@unlink( $uploads_path . DIRECTORY_SEPARATOR . $size_data['path'] );

				// Include webp files in the renaming process
				if ( file_exists( $uploads_path . DIRECTORY_SEPARATOR . $size_data['path'] . '.webp' ) ) {
					unlink( $uploads_path . DIRECTORY_SEPARATOR . $size_data['path'] . '.webp' );
				}
			}

			if ( ! @rename( $file_abs_path, $new_file_abs_path ) ) {
				return __( 'File renaming error!' );
			}
			// Include webp files in the renaming process
			if ( file_exists( $file_abs_path . '.webp' ) ) {
				unlink( $file_abs_path . '.webp' );
			}

			// Include webp files in the renaming process
			if ( file_exists( $file_abs_path . '.webp' ) ) {
				rename( $file_abs_path . '.webp', $new_file_abs_path . '.webp' );
			}

			update_post_meta( $attachment_id, '_wp_attached_file', $new_file_rel_path );
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $new_file_abs_path ) );

			// Replace the old with the new media link in the content of all posts and metas
			if ( $update_in_post_content ) {
				$replaces = self::get_attachment_urls( $attachment_id );

				$i          = 0;
				$post_types = get_post_types();
				unset( $post_types['attachment'] );

				while ( $posts = get_posts(
					array(
						'post_type'   => $post_types,
						'post_status' => 'any',
						'numberposts' => 100,
						'offset'      => $i * 100,
					)
				) ) {
					foreach ( $posts as $post ) {
						// Updating post content if necessary
						$new_post                 = array( 'ID' => $post->ID );
						$new_post['post_content'] = str_replace( $searches, $replaces, $post->post_content );
						$page_template            = get_post_meta( $post->ID, '_wp_page_template', true );
						if ( $new_post['post_content'] != $post->post_content ) {
							wp_update_post( wp_slash( $new_post ) );
						}

						// Updating post metas if necessary
						$metas = get_post_meta( $post->ID );
						foreach ( $metas as $key => $meta ) {
							$meta[0]  = self::unserialize_deep( $meta[0] );
							$new_meta = self::replace_media_urls( $meta[0], $searches, $replaces );
							if ( $new_meta != $meta[0] ) {
								update_post_meta( $post->ID, $key, $new_meta, $meta[0] );
							}
						}

						// Sometimes WP deletes the page template during the wp_update_post call
						if ( $page_template ) {
							update_post_meta( $post->ID, '_wp_page_template', $page_template );
						}
					}

					$i++;
				}

				// Updating options if necessary
				$options = self::get_all_options();
				foreach ( $options as $option ) {
					$option['value'] = self::unserialize_deep( $option['value'] );
					$new_option      = self::replace_media_urls( $option['value'], $searches, $replaces );
					if ( $new_option != $option['value'] ) {
						update_option( $option['name'], $new_option );
					}
				}

				//Updating SmartSlider 3 tables
				self::UpdateSmartSlider( $old_filename, $new_filename, $file_parts['extension'] );
			}

			return 1;
		}

		/**
		 * Check if table exists
		 *
		 * @param [type] $tablename
		 * @return boolean
		 */
		static function TableExist( $tablename ) {
			global $wpdb;

			if ( $wpdb->get_var( "SHOW TABLES LIKE '$tablename'" ) == $tablename ) {
				//table is not present
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Update Smart Slider 3 custom table
		 *
		 * @param string $old_filename
		 * @param string $new_filename
		 * @param string $extension
		 * @return void
		 */
		static function UpdateSmartSlider( $old_filename, $new_filename, $extension ) {
			global $wpdb;

			//compose file names
			$old_filename = $old_filename . '.' . $extension;
			$new_filename = $new_filename . '.' . $extension;

			if ( empty( $old_filename ) || empty( $new_filename ) ) {
				return false;
			}
			if ( $old_filename == '' ) {
				return false;
			}

			//escape filename for use in LIKE statement
			$old_filename = $wpdb->esc_like( $old_filename );

			$filter = '%/' . $old_filename;

			//compose Smart Slider table name
			$tablename = $wpdb->prefix . 'nextend2_smartslider3_slides';

			if ( ! self::TableExist( $tablename ) ) {
				//if table does not exist, exit and return false
				return false;
			} else {
				//if table exist, change file name
				$sqlQuery = 'UPDATE ' . $tablename . ' SET thumbnail = REPLACE(thumbnail, %s, %s), params = REPLACE(params, %s, %s) WHERE thumbnail LIKE %s';

				$updated = $wpdb->query(
					$wpdb->prepare(
						$sqlQuery,
						$old_filename,
						$new_filename,
						$old_filename,
						$new_filename,
						$filter
					)
				);
			}

			$tablename = $wpdb->prefix . 'nextend2_image_storage';

			if ( self::TableExist( $tablename ) ) {
				//if table exist, change file name (unnecessary table, does not exit if table is missing)
				$sqlQuery = 'UPDATE ' . $tablename . ' SET image = REPLACE(image, %s, %s) WHERE image LIKE %s';

				$updated = $wpdb->query(
					$wpdb->prepare(
						$sqlQuery,
						$old_filename,
						$new_filename,
						$filter
					)
				);
			}

			return true;
		}

		/**
		 * Get all options
		 *
		 * @return void
		 */
		static function get_all_options() {
			return $GLOBALS['wpdb']->get_results( "SELECT option_name as name, option_value as value FROM {$GLOBALS['wpdb']->options} WHERE option_name NOT LIKE '%transient%' ", ARRAY_A );
		}

		/**
		 * Replace the media url and fix serialization if necessary
		 *
		 * @param [type] $subj
		 * @param [type] $searches
		 * @param [type] $replaces
		 * @return void
		 */
		static function replace_media_urls( $subj, &$searches, &$replaces ) {
			$subj = is_object( $subj ) ? clone $subj : $subj;

			if ( ! is_scalar( $subj ) && is_countable( $subj ) && count( $subj ) ) {
				foreach ( $subj as &$item ) {
					$item = self::replace_media_urls( $item, $searches, $replaces );
				}
			} else {
				$subj = is_string( $subj ) ? str_replace( $searches, $replaces, $subj ) : $subj;
			}

			return $subj;
		}

	}

}
