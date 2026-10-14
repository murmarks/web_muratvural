<?php defined( 'ABSPATH' ) || exit;

// Fix. If they update one plugin and use an old version of another,
// the Abstract class might not exist and they will get fatal errors.
// So we make sure it loads the class from the current plugin if it's missing
// This can be removed in a future update.
if ( ! class_exists( 'VGSE_Provider_Abstract' ) ) {
	require_once vgse_comments()->plugin_dir . '/modules/wp-sheet-editor/inc/providers/abstract.php';
}

class VGSE_Provider_Comments extends VGSE_Provider_Abstract {

	private static $instance  = false;
	public $key               = 'comments';
	public $is_post_type      = false;
	public static $data_store = array();

	private function __construct() {

	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  Foo A single instance of this class.
	 */
	static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new VGSE_Provider_Comments();
			self::$instance->init();
		}
		return self::$instance;
	}

	function get_provider_read_capability( $post_type_key ) {
		return 'edit_posts';
	}

	function delete_meta_key( $old_key, $post_type ) {
		global $wpdb;
		$meta_table_name = $this->get_meta_table_name( $post_type );
		$modified        = (int) $wpdb->delete(
			$meta_table_name,
			array(
				'meta_key' => $old_key,
			)
		);
		return $modified;
	}

	function rename_meta_key( $old_key, $new_key, $post_type ) {
		global $wpdb;
		$meta_table_name = $this->get_meta_table_name( $post_type );
		$modified        = (int) $wpdb->update(
			$meta_table_name,
			array(
				'meta_key' => $new_key,
			),
			array(
				'meta_key' => $old_key,
			)
		);
		return $modified;
	}

	function get_provider_edit_capability( $post_type_key ) {
		return 'moderate_comments';
	}

	function get_provider_delete_capability( $post_type_key ) {
		return 'moderate_comments';
	}

	function init() {

	}

	function get_total( $post_type = null ) {
		$result = get_comments(
			array(
				'count'  => true,
				'offset' => 0,
				'number' => 0,
			)
		);
		return (int) $result;
	}

	function get_post_data_table_id_key( $post_type = null ) {
		if ( ! $post_type ) {
			$post_type = VGSE()->helpers->get_provider_from_query_string();
		}

		$post_id_key = apply_filters( 'vgse_sheet_editor/provider/comments/post_data_table_id_key', 'comment_ID', $post_type );
		if ( ! $post_id_key ) {
			$post_id_key = 'comment_ID';
		}
		if ( method_exists( VGSE()->helpers, 'sanitize_table_key' ) ) {
			$post_id_key = VGSE()->helpers->sanitize_table_key( $post_id_key );
		}
		return $post_id_key;
	}

	function get_meta_table_post_id_key( $post_type = null ) {
		if ( ! $post_type ) {
			$post_type = VGSE()->helpers->get_provider_from_query_string();
		}

		$post_id_key = apply_filters( 'vgse_sheet_editor/provider/comments/meta_table_post_id_key', 'comment_id', $post_type );
		if ( ! $post_id_key ) {
			$post_id_key = 'comment_id';
		}
		if ( method_exists( VGSE()->helpers, 'sanitize_table_key' ) ) {
			$post_id_key = VGSE()->helpers->sanitize_table_key( $post_id_key );
		}
		return $post_id_key;
	}

	function get_meta_table_name( $post_type = null ) {
		global $wpdb;
		if ( ! $post_type ) {
			$post_type = VGSE()->helpers->get_provider_from_query_string();
		}

		$table_name = apply_filters( 'vgse_sheet_editor/provider/comments/meta_table_name', $wpdb->commentmeta, $post_type );
		if ( ! $table_name ) {
			$table_name = $wpdb->commentmeta;
		}
		if ( method_exists( VGSE()->helpers, 'sanitize_table_key' ) ) {
			$table_name = VGSE()->helpers->sanitize_table_key( $table_name );
		}
		return $table_name;
	}

	function prefetch_data( $post_ids, $post_type, $spreadsheet_columns ) {

	}

	function get_item_terms( $id, $taxonomy ) {
		return false;
	}

	function get_statuses() {
		return array();
	}

	function get_items( $query_args ) {
		$post_keys_to_remove = array(
			'post_status',
			'post_type',
			'tax_query',
		);
		foreach ( $post_keys_to_remove as $post_key_to_remove ) {
			if ( isset( $query_args[ $post_key_to_remove ] ) ) {
				unset( $query_args[ $post_key_to_remove ] );
			}
		}

		$query_args['update_comment_meta_cache'] = false;
		$query_args['update_comment_post_cache'] = false;
		if ( isset( $query_args['comments_post_type'] ) ) {
			$query_args['post_type'] = $query_args['comments_post_type'];
		}
		if ( isset( $query_args['comments_author'] ) ) {
			$query_args['author__in'] = $query_args['comments_author'];
		}
		if ( isset( $query_args['posts_per_page'] ) ) {
			if ( $query_args['posts_per_page'] < 1 ) {
				$query_args['posts_per_page'] = null;
			}
			$query_args['number'] = $query_args['posts_per_page'];
		}
		if ( isset( $query_args['paged'] ) && isset( $query_args['number'] ) ) {
			$query_args['offset'] = $start = ( $query_args['paged'] - 1 ) * $query_args['number'];
		}
		if ( isset( $query_args['comment_status'] ) && isset( $query_args['comment_status'] ) ) {
			$query_args['status'] = $query_args['comment_status'];
		}

		if ( isset( $query_args['post__in'] ) ) {
			$query_args['comment__in'] = $query_args['post__in'];
			unset( $query_args['post__in'] );
		}
		if ( isset( $query_args['post__not_in'] ) ) {
			$query_args['comment__not_in'] = $query_args['post__not_in'];
			unset( $query_args['post__not_in'] );
		}
		if ( ! empty( $query_args['s'] ) ) {
			$query_args['search'] = $query_args['s'];
		}
		// Allow to search by order notes
		if ( ! empty( $query_args['type'] ) && $query_args['type'] === 'order_note' ) {
			remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
		}
		if ( ! empty( $query_args['type'] ) && $query_args['type'] === 'edd_review' && function_exists( 'edd_reviews' ) ) {
			remove_action( 'pre_get_comments', array( edd_reviews(), 'hide_reviews' ) );
		}

		$comments_query = new WP_Comment_Query();
		$comments       = $comments_query->query( $query_args );

		$total_comments = get_comments(
			array_merge(
				$query_args,
				array(
					'count'  => true,
					'offset' => 0,
					'number' => 0,
				)
			)
		);

		$out              = (object) array();
		$out->found_posts = $total_comments;
		$out->posts       = array();
		$out->request     = $comments_query->request;
		if ( ! empty( $comments ) ) {
			foreach ( $comments as $comment ) {
				if ( is_object( $comment ) ) {
					$comment      = $this->_standarize_item( $comment );
					$out->posts[] = $comment;
				} else {
					$out->posts[] = $comment;
				}
			}
		}
		return $out;
	}

	function _standarize_item( $item, $context = 'read' ) {
		if ( $context === 'read' ) {
			$item->post_type = 'comments';
			$item->ID        = $item->comment_ID;
		}
		return $item;
	}

	function get_item( $id, $format = null ) {
		$item = get_comment( $id );

		if ( ! empty( $item ) ) {
			$item = $this->_standarize_item( $item );
		}
		if ( $format == ARRAY_A ) {
			$item = (array) $item;
		}
		return apply_filters( 'vg_sheet_editor/provider/comments/get_item', $item, $id, $format );
	}

	function get_item_meta( $id, $key, $single = true, $context = 'save', $bypass_cache = false ) {
		return apply_filters( 'vg_sheet_editor/provider/comments/get_item_meta', get_comment_meta( $id, $key, $single ), $id, $key, $single, $context );
	}

	function get_item_data( $id, $key ) {
		$item = $this->get_item( $id );
		if ( isset( $item->$key ) ) {
			$out = htmlspecialchars_decode( apply_filters( 'vg_sheet_editor/provider/comments/get_item_data', $item->$key, $id, $key, true, 'read', $item ) );
		} else {
			$out = $this->get_item_meta( $id, $key, true, 'read' );
		}

		return $out;
	}

	function update_item_data( $values, $wp_error = false ) {

		if ( empty( $values['ID'] ) ) {
			return new WP_Error( 'wpse', __( 'The item id does not exist. Error #89827cc', vgse_comments()->textname ) );
		}

		$values['comment_ID'] = $values['ID'];
		$comment_id           = $values['comment_ID'];

		if ( ! empty( $values['comment_approved'] ) && $values['comment_approved'] === 'delete' ) {
			$result                    = wp_delete_comment( $comment_id, true );
			VGSE()->deleted_rows_ids[] = (int) $comment_id;
			return $comment_id;
		}
		wp_update_comment( $values );
		do_action( 'vg_sheet_editor/provider/comments/data_updated', $comment_id, $values );

		return $comment_id;
	}

	function delete_item_meta( $id, $key ) {
		delete_comment_meta( $id, $key );
	}
	function update_item_meta( $id, $key, $value ) {
		return update_comment_meta( $id, $key, $value );
	}

	function set_object_terms( $post_id, $comments_saved, $key ) {
		return;
	}

	function get_object_taxonomies( $post_type = null ) {
		return get_taxonomies( array(), 'objects' );
	}


	function create_item( $values ) {
		$values['comment_content'] = '...';
		$result                    = wp_insert_comment( $values );
		$out                       = ( is_wp_error( $result ) || ! $result ) ? null : $result;
		return $out;
	}

	function get_item_ids_by_keyword( $keyword, $post_type, $operator = 'LIKE' ) {
		global $wpdb;
		$operator = ( $operator === 'LIKE' ) ? 'LIKE' : 'NOT LIKE';

		$checks        = array();
		$keywords      = array_map( 'trim', explode( ';', $keyword ) );
		$prepared_data = array();
		foreach ( $keywords as $single_keyword ) {
			$checks[]      = " (comment_content $operator %s OR comment_author_email $operator %s OR comment_type $operator %s OR comment_author_url $operator %s OR comment_date $operator %s)";
			$prepared_data = array_merge( $prepared_data, array( '%' . $wpdb->esc_like( $single_keyword ) . '%', '%' . $wpdb->esc_like( $single_keyword ) . '%', '%' . $wpdb->esc_like( $single_keyword ) . '%', '%' . $wpdb->esc_like( $single_keyword ) . '%', '%' . $wpdb->esc_like( $single_keyword ) . '%' ) );
		}

		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE " . implode( ' OR ', $checks ), $prepared_data ) );
		return $ids;
	}

	function get_meta_object_id_field( $field_key, $column_settings ) {
		$post_meta_post_id_key = $this->get_meta_table_post_id_key();
		return $post_meta_post_id_key;
	}

	function get_table_name_for_field( $field_key, $column_settings ) {
		global $wpdb;

		$comment_data = wp_list_pluck( $wpdb->get_results( "SHOW COLUMNS FROM $wpdb->comments;" ), 'Field' );
		$table_name   = ( in_array( $field_key, $comment_data ) ) ? $wpdb->comments : $this->get_meta_table_name();
		if ( method_exists( VGSE()->helpers, 'sanitize_table_key' ) ) {
			$table_name = VGSE()->helpers->sanitize_table_key( $table_name );
		}
		return $table_name;
	}

	function get_meta_field_unique_values( $meta_key, $post_type = null ) {
		global $wpdb;
		$meta_table = $this->get_meta_table_name( $this->key );
		$id_key     = $this->get_meta_table_post_id_key( $this->key );
		$sql        = $wpdb->prepare( "SELECT m.meta_value FROM $wpdb->comments p LEFT JOIN $meta_table m ON p.comment_ID = m.$id_key WHERE m.meta_key = %s GROUP BY m.meta_value ORDER BY LENGTH(m.meta_value) DESC LIMIT 4", $meta_key );
		$values     = apply_filters( 'vg_sheet_editor/provider/comments/meta_field_unique_values', $wpdb->get_col( $sql ), $meta_key, $post_type );
		return $values;
	}

	function get_all_meta_fields( $post_type = null ) {
		global $wpdb;
		$pre_value = apply_filters( 'vg_sheet_editor/provider/comments/all_meta_fields_pre_value', null, $this->key );

		if ( is_array( $pre_value ) ) {
			return $pre_value;
		}
		$meta_table    = $this->get_meta_table_name( $this->key );
		$id_key        = $this->get_meta_table_post_id_key( $this->key );
		$meta_keys_sql = "SELECT m.meta_key FROM $wpdb->comments p LEFT JOIN $meta_table m ON p.comment_ID = m.$id_key WHERE m.meta_value NOT LIKE 'field_%' AND m.meta_key NOT LIKE '_oembed%' GROUP BY m.meta_key";
		$meta_keys     = $wpdb->get_col( $meta_keys_sql );
		return apply_filters( 'vg_sheet_editor/provider/comments/all_meta_fields', $meta_keys, $this->key );
	}

}
