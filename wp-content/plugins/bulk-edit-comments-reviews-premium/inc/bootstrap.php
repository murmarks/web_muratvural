<?php defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WPSE_Comments_Spreadsheet_Bootstrap' ) ) {

	class WPSE_Comments_Spreadsheet_Bootstrap extends WP_Sheet_Editor_Bootstrap {

		function render_quick_access() {
			$screen = get_current_screen();
			if ( empty( $screen ) || $screen->base !== 'edit-comments' ) {
				return;
			}
			?>
			<script>jQuery(window).on('load', function () {
					jQuery('.wp-heading-inline').last().after('<a href=<?php echo json_encode( esc_url( VGSE()->helpers->get_editor_url( 'comments' ) ) ); ?> class="page-title-action"><?php _e( 'Open in a Spreadsheet', 'vg_sheet_editor' ); ?></a>');
				});</script>
			<?php
		}

		function get_post_title_for_column( $post, $cell_key, $cell_args ) {
			$value = get_the_title( $post->comment_post_ID ) . ' (' . get_post_type( $post->comment_post_ID ) . ')';
			return $value;
		}

		function get_post_slug_for_column( $post, $cell_key, $cell_args ) {
			$parent_post = get_post( $post->comment_post_ID );
			$value       = $parent_post ? $parent_post->post_name : '';
			return $value;
		}

		function get_user_login_for_column( $post, $cell_key, $cell_args ) {
			$value = '';
			if ( $post->user_id ) {
				$user  = get_userdata( $post->user_id );
				$value = $user ? $user->user_login : '';
			}
			return $value;
		}

		function get_post_url_for_column( $post, $cell_key, $cell_args ) {
			$value = get_permalink( $post->comment_post_ID );
			return $value;
		}

		function _register_columns() {
			global $wpdb;
			$post_type = 'comments';
			$this->columns->register_item(
				'ID',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 75,
					'title'             => __( 'ID', vgse_comments()->textname ),
					'type'              => '',
					'supports_formulas' => false,
					'allow_to_hide'     => false,
					'allow_to_save'     => false,
					'allow_to_rename'   => false,
					'is_locked'         => true,
				)
			);
			$this->columns->register_item(
				'comment_author',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'Author', vgse_comments()->textname ),
					'supports_formulas' => true,
				)
			);
			$this->columns->register_item(
				'comment_author_email',
				$post_type,
				array(
					'data_type'           => 'post_data',
					'column_width'        => 180,
					'title'               => __( 'Author email', vgse_comments()->textname ),
					'supports_formulas'   => true,
					'save_value_callback' => array( $this, 'save_comment_user_email' ),
					'value_type'          => 'email',
				)
			);
			$this->columns->register_item(
				'comment_author_url',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'Author URL', vgse_comments()->textname ),
					'supports_formulas' => true,
				)
			);
			$this->columns->register_item(
				'comment_author_IP',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'Author IP', vgse_comments()->textname ),
					'supports_formulas' => true,
				)
			);
			$this->columns->register_item(
				'comment_content',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'Content', vgse_comments()->textname ),
					'supports_formulas' => true,
					'formatted'         => array(
						'renderer'          => 'wp_tinymce',
						'wpse_template_key' => 'tinymce_cell_template',
					),
				)
			);
			$this->columns->register_item(
				'comment_date',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'Date', vgse_comments()->textname ),
					'supports_formulas' => true,
					'formatted'         => array(
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
					),
				)
			);
			$this->columns->register_item(
				'comment_post_ID',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 100,
					'title'             => __( 'Post ID', vgse_comments()->textname ),
					'supports_formulas' => true,
					'allow_to_import'   => false,
				)
			);
			$this->columns->register_item(
				'comment_post_title',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'column_width'       => 320,
					'title'              => __( 'Post Title', vgse_comments()->textname ),
					'supports_formulas'  => false,
					'allow_to_save'      => false,
					'is_locked'          => true,
					'get_value_callback' => array( $this, 'get_post_title_for_column' ),
					'allow_to_import'    => false,
				)
			);

			$this->columns->register_item(
				'post_slug',
				$post_type,
				array(
					'data_type'             => 'post_data',
					'column_width'          => 320,
					'title'                 => __( 'Post Slug', vgse_comments()->textname ),
					'supports_formulas'     => true,
					'supports_sql_formulas' => false,
					'allow_to_save'         => true,
					'get_value_callback'    => array( $this, 'get_post_slug_for_column' ),
					'save_value_callback'   => array( $this, 'save_comment_post_by_slug' ),
				)
			);
			$this->columns->register_item(
				'view_post',
				$post_type,
				array(
					'data_type'          => 'post_data',
					'title'              => __( 'View Post', 'vg_sheet_editor' ),
					'supports_formulas'  => false,
					'type'               => 'external_button',
					'formatted'          => array(
						'data'     => 'view_post',
						'renderer' => 'wp_external_button',
						'readOnly' => true,
					),
					'allow_to_save'      => false,
					'get_value_callback' => array( $this, 'get_post_url_for_column' ),
					'allow_to_import'    => false,
				)
			);
			$this->columns->register_item(
				'comment_karma',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 120,
					'title'             => __( 'Karma', vgse_comments()->textname ),
					'supports_formulas' => true,
				)
			);
			$this->columns->register_item(
				'comment_approved',
				$post_type,
				array(
					'data_type'             => 'post_data',
					'column_width'          => 120,
					'title'                 => __( 'Status', vgse_comments()->textname ),
					'supports_formulas'     => true,
					'supports_sql_formulas' => false,
					'formatted'             => array(
						'editor'        => 'select',
						'selectOptions' => array(
							'0'      => __( 'Pending moderation', vgse_comments()->textname ),
							'1'      => __( 'Approved', vgse_comments()->textname ),
							'spam'   => __( 'Spam', vgse_comments()->textname ),
							'trash'  => __( 'Trash', vgse_comments()->textname ),
							'delete' => __( 'Delete completely', vgse_comments()->textname ),
						),
					),
				)
			);
			$this->columns->register_item(
				'comment_agent',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'Browser of the user', vgse_comments()->textname ),
					'supports_formulas' => true,
				)
			);
			$types             = array_merge( array( 'comment' ), $wpdb->get_col( "SELECT comment_type FROM $wpdb->comments WHERE comment_type != '' GROUP BY comment_type" ) );
			$types_with_labels = array(
				'comment'          => __( 'Comment' ),
				'order_note'       => __( 'Order notes', 'woocommerce' ),
				'review'           => __( 'Reviews', 'woocommerce' ),
				'edd_payment_note' => __( 'Payment Notes', 'edd' ),
			);
			$final_types       = array();
			foreach ( $types as $type ) {
				if ( isset( $types_with_labels[ $type ] ) ) {
					$final_types[ $type ] = $types_with_labels[ $type ];
				} else {
					$final_types[ $type ] = $type;
				}
			}
			$this->columns->register_item(
				'comment_type',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 120,
					'title'             => __( 'Type', vgse_comments()->textname ),
					'supports_formulas' => true,
					'formatted'         => array(
						'editor'        => 'select',
						'selectOptions' => $final_types,
					),
				)
			);
			$this->columns->register_item(
				'comment_parent',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 120,
					'title'             => __( 'Parent comment', vgse_comments()->textname ),
					'supports_formulas' => true,
				)
			);
			$this->columns->register_item(
				'user_id',
				$post_type,
				array(
					'data_type'         => 'post_data',
					'column_width'      => 180,
					'title'             => __( 'User ID of the Author', vgse_comments()->textname ),
					'supports_formulas' => true,
					'allow_to_import'   => false,
				)
			);
			$this->columns->register_item(
				'user_login',
				$post_type,
				array(
					'data_type'           => 'post_data',
					'column_width'        => 320,
					'title'               => __( 'Username of the Author', vgse_comments()->textname ),
					'get_value_callback'  => array( $this, 'get_user_login_for_column' ),
					'save_value_callback' => array( $this, 'save_comment_user_by_username' ),
					'supports_formulas'   => true,
					'formatted'           => array(
						'type'   => 'autocomplete',
						'source' => 'searchUsers',
					),
				)
			);
			$this->columns->register_item(
				'source_site_id',
				$post_type,
				array(
					'data_type'         => 'meta_data',
					'column_width'      => 100,
					'title'             => __( 'Source site id', vgse_comments()->textname ),
					'supports_formulas' => true,
					'formatted'         => array(
						'comment' => array( 'value' => __( 'When you import comments, we save the ID from the old site here so you can import new comments and skip previously imported comments. You can leave this empty if the comments originate from the current site.', 'vg_sheet_editor' ) ),
					),
				)
			);
		}

		function save_comment_post_by_slug( $comment_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns ) {
			global $wpdb;
			if ( empty( $data_to_save ) ) {
				return;
			}

			$post_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s", $data_to_save ) );
			if ( ! $post_id ) {
				return;
			}

			$commentarr                    = array();
			$commentarr['comment_ID']      = $comment_id;
			$commentarr['comment_post_ID'] = $post_id;
			wp_update_comment( $commentarr );
		}

		function save_comment_user_email( $comment_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns ) {
			if ( empty( $data_to_save ) ) {
				return;
			}

			$user_id                            = email_exists( $data_to_save );
			$commentarr                         = array();
			$commentarr['comment_ID']           = $comment_id;
			$commentarr['comment_author_email'] = $data_to_save;
			if ( $user_id ) {
				$commentarr['user_id'] = $user_id;
			}

			wp_update_comment( $commentarr );
		}

		function save_comment_user_by_username( $comment_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns ) {
			global $wpdb;
			if ( empty( $data_to_save ) ) {
				return;
			}

			$user_id                  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_login = %s", $data_to_save ) );
			$commentarr               = array();
			$commentarr['comment_ID'] = $comment_id;
			$commentarr['user_id']    = $user_id;
			wp_update_comment( $commentarr );
		}
	}

}
