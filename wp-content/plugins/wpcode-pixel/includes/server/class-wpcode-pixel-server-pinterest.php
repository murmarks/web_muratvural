<?php
/**
 * The Pinterest server handler.
 *
 * @package WPCode_Pixel
 */

/**
 * Class WPCode_Pixel_Server_Pinterest
 */
class WPCode_Pixel_Server_Pinterest extends WPCode_Pixel_Server {

	/**
	 * The api URL.
	 *
	 * @var string
	 */
	public $api_url = 'https://api.pinterest.com';

	/**
	 * The api version.
	 *
	 * @var string
	 */
	public static $API_VERSION = 'v5';// phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * The pixel type key.
	 *
	 * @var string
	 */
	public $pixel_key = 'pinterest';

	/**
	 * This is called before an event is sent in order to add
	 * any additional data to the event.
	 *
	 * @param array $data The event data.
	 *
	 * @return string
	 */
	public function prepare_event( $data ) {

		$data = array(
			'data' => array( $data ),
		);

		return wp_json_encode( $data );
	}

	/**
	 * Get the Pinterest-specific api url.
	 *
	 * @return string
	 */
	public function get_api_url() {
		$url = $this->api_url . '/' . self::$API_VERSION . '/ad_accounts/' . $this->pixel_id . '/events/'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( apply_filters( 'wpcode_pixel_pinterest_api_test_mode', false ) ) {
			$url = add_query_arg( 'test', 'true', $url );
		}

		return $url;
	}

	/**
	 * Add authorization header using the api token.
	 *
	 * @return string[]
	 */
	public function get_headers() {
		return array(
			'Authorization' => 'Bearer ' . $this->api_token,
			'Content-Type'  => 'application/json; charset=utf-8',
		);
	}
}
