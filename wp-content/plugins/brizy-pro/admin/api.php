<?php

class BrizyPro_Admin_Api extends Brizy_Admin_AbstractApi {

	const ACTION_AI = 'editorAi';
	const ACTION_UPLOAD_CUSTOM_ICON = 'editorUploadCustomIcon';
	const ACTION_GET_CUSTOM_ICONS = 'editorGetCustomIcons';
	const ACTION_RM_CUSTOM_ICON = 'editorRmCustomIcon';

	protected function initializeApiActions() {
		add_action( 'wp_ajax_' . self::ACTION_AI,                 [ $this, 'ai' ] );
		add_action( 'wp_ajax_' . self::ACTION_UPLOAD_CUSTOM_ICON, [ $this, 'uploadCustomIcon' ] );
		add_action( 'wp_ajax_' . self::ACTION_GET_CUSTOM_ICONS,   [ $this, 'getCustomIcons' ] );
		add_action( 'wp_ajax_' . self::ACTION_RM_CUSTOM_ICON,     [ $this, 'rmCustomIcon' ] );
	}

	public function ai() {

		$this->verifyNonce();

		$http     = new WP_Http();
		$response = $http->request( BrizyPro_Config::AI_URL . '?api_path=/v1/chat/completions', [
			'headers' => [
				'x-auth-wp-license-key' => BrizyPro_Admin_License::_init()->getCurrentLicense()['key'],
				'Content-Type'          => 'application/json',
			],
			'body'    => file_get_contents( "php://input" ),
			'method'  => 'POST',
			'timeout' => 120,
		] );

		$status = wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) || 200 != $status ) {
			$this->error( $status, wp_remote_retrieve_response_message( $response ) );
		}

		wp_send_json( json_decode( wp_remote_retrieve_body( $response ), true ) );
	}

	public function uploadCustomIcon() {

		$this->verifyNonce();

		if ( empty( $_POST['attachment'] ) || empty( $_POST['filename'] ) ) {
			$this->error(400, 'The filename or attachment is empty.');
		}

		$filename = $_POST['filename'];

		if ( ! in_array( pathinfo( $filename, PATHINFO_EXTENSION ), [ 'svg', 'ico' ] ) ) {
			$this->error( 400, 'Invalid file extension.' );
		}

		$content = base64_decode( $_POST['attachment'] );

		if ( false === $content ) {
			$this->error( 400, 'Invalid file content.' );
		}

		$uploadDir = wp_upload_dir();
		$filename  = wp_unique_filename( $uploadDir['path'], strtolower( $filename ) );
		$filePath  = $uploadDir['path'] . '/' . $filename;

		if ( false === file_put_contents( $filePath, $content ) ) {
			$this->error( 400, 'The file is not written.' );
		}

		$mimeType = Brizy_Public_AssetProxy::get_mime( $filePath );

		if ( ! in_array( $mimeType, [ 'image/svg+xml', 'image/vnd.microsoft.icon' ] ) ) {
			@unlink($filePath);
			$this->error( 400, 'Unsupported mime type.' );
		}

		$attachment = [
			'post_mime_type' => $mimeType,
			'post_title'     => $filename,
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $uploadDir['url'] . '/' . $filename,
		];

		$id = wp_insert_attachment( $attachment, $filePath );

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$metadata = wp_generate_attachment_metadata( $id, $filePath );
		$uid      = md5( $id . time() );

		wp_update_attachment_metadata( $id, $metadata );
		update_post_meta( $id, 'brizy_attachment_uid', $uid );
		update_post_meta( $id, 'brizy_custom_icon', true );

		return $this->success(['uid' => $uid, 'filename' => $filename]);
	}

	public function getCustomIcons() {
		$this->verifyNonce();

		$ids = get_posts( [
			'posts_per_page' => - 1,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => [ 'image/svg+xml', 'image/vnd.microsoft.icon' ],
			'meta_key'       => 'brizy_custom_icon',
			'meta_value'     => '1',
			'fields'         => 'ids',
		] );

		$result = [];
		foreach ( $ids as $id ) {
			$result[] = [
				'uid'       => get_post_meta( $id, 'brizy_attachment_uid', true ),
				'filename' => basename( get_attached_file( $id ) )
			];
		}

		$this->success( $result );
	}

	public function rmCustomIcon() {
		$this->verifyNonce();

		$uid = $this->param( 'uid' );

		if ( empty( $uid ) ) {
			$this->error( '400', 'Uid is a required parameter.' );
		}

		$post = get_posts( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => [ 'image/svg+xml', 'image/vnd.microsoft.icon' ],
			'posts_per_page' => 1,
			'meta_key'       => 'brizy_attachment_uid',
			'meta_value'     => $uid,
		] );

		if ( empty( $post[0]->ID ) ) {
			$this->error( '404', 'There is no such icon with this uid.' );
		}

		wp_delete_post( $post[0]->ID, true );

		$this->success( [ 'message' => 'The icon has been removed successfully.' ] );
	}

	public function verifyNonce( $action = null ) {
		if ( ! $this->getRequestNonce() || ! wp_verify_nonce( $this->getRequestNonce(), Brizy_Editor_API::nonce ) ) {
			$this->error( 400, 'Invalid token, please refresh page and try again.' );
		}
	}

	protected function getRequestNonce() {
		return $this->param( 'hash' );
	}
}