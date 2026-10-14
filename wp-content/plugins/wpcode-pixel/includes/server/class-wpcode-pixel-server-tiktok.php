<?php
/**
 * TikTok server class.
 *
 * @package WPCode\Pixel
 */

/**
 * Class WPCode_Pixel_Server_TikTok.
 */
class WPCode_Pixel_Server_TikTok extends WPCode_Pixel_Server {

	/**
	 * The api url.
	 *
	 * @var string
	 */
	public $api_url = 'https://business-api.tiktok.com/open_api/';

	/**
	 * The api version.
	 *
	 * @var string
	 */
	public static $API_VERSION = 'v1.3'; // phpcs:ignore

	/**
	 * The pixel type key.
	 *
	 * @var string
	 */
	public $pixel_key = 'tiktok';

	/**
	 * This is called before an event is sent in order to add
	 * any additional data to the event.
	 *
	 * @param array $data The event data.
	 *
	 * @return string
	 */
	public function prepare_event( $data ) {

		$data['pixel_code'] = $this->pixel_id;

		$test_event_code = apply_filters( 'wpcode_pixel_tiktok_test_event_code', false );
		if ( false !== $test_event_code ) {
			$data['test_event_code'] = $test_event_code;
		}

		return wp_json_encode( $data );
	}

	/**
	 * Get the Pinterest-specific api url.
	 *
	 * @return string
	 */
	public function get_api_url() {
		return $this->api_url . '/' . self::$API_VERSION . '/pixel/track/'; // phpcs:ignore
	}

	/**
	 * Add authorization header using the api token.
	 *
	 * @return string[]
	 */
	public function get_headers() {
		return array(
			'Access-Token' => $this->api_token,
			'Content-Type' => 'application/json',
		);
	}
}
