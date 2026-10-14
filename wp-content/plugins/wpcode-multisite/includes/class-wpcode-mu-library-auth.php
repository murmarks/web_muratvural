<?php
/**
 * Class for handling the WPCode library authentication.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Library_Auth.
 */
class WPCode_MU_Library_Auth extends WPCode_Library_Auth {

	/**
	 * Library auth constructor.
	 */
	public function __construct() {
		remove_action( 'wp_ajax_wpcode_library_store_auth', array( wpcode()->library_auth, 'store_auth_key' ) );
		remove_action( 'wp_ajax_wpcode_library_delete_auth', array( wpcode()->library_auth, 'delete_auth' ) );

		parent::__construct();

		wpcode()->library_auth = $this;
	}

	/**
	 * Store the auth data in the network context.
	 *
	 * @param string $key The api key.
	 * @param string $username The username.
	 * @param string $webhook_secret The webhook secret.
	 * @param string $client_id The client id.
	 *
	 * @return void
	 */
	public function save_auth_data( $key, $username, $webhook_secret, $client_id ) {
		// Don't autoload this as we'll only need it on some pages and in specific requests.
		update_site_option(
			'wpcode_library_api_auth',
			array(
				'key'            => $key,
				'username'       => $username,
				'webhook_secret' => $webhook_secret,
				'client_id'      => $client_id,
				'connected_at'   => time(),
			)
		);
	}

	/**
	 * Delete the auth data from the db in the network context.
	 *
	 * @return bool
	 */
	public function delete_auth_data() {
		return delete_site_option( 'wpcode_library_api_auth' );
	}

	/**
	 * Get the auth data from the db.
	 *
	 * @return array|bool
	 */
	public function load_auth_data() {
		$option = get_site_option( 'wpcode_library_api_auth' );

		if ( empty( $option ) ) {
			return false;
		} else {
			return $option;
		}
	}
}
