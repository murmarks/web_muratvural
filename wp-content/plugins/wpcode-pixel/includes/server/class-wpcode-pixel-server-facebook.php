<?php

/**
 * Class WPCode_Pixel_Server_Facebook
 */
class WPCode_Pixel_Server_Facebook extends WPCode_Pixel_Server {

	/**
	 * The api URL.
	 *
	 * @var string
	 */
	public $api_url = 'https://graph.facebook.com/';

	/**
	 * The api version.
	 *
	 * @var string
	 */
	public static $API_VERSION = 'v14.0';

	/**
	 * The pixel type key.
	 *
	 * @var string
	 */
	public $pixel_key = 'facebook';

	/**
	 * This is called before an event is sent in order to add
	 * any additional data to the event.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function prepare_event( $data ) {
		$data = array(
			'data'         => array( wp_json_encode( $data ) ),
			'access_token' => $this->api_token,
		);

		$test_event_code = apply_filters( 'wpcode_pixel_facebook_test_event_code', false );
		if ( false !== $test_event_code ) {
			$data['test_event_code'] = $test_event_code;
		}

		return $data;
	}

	/**
	 * Get the fb-specific api url.
	 *
	 * @return string
	 */
	public function get_api_url() {
		return $this->api_url . '/' . self::$API_VERSION . '/' . $this->pixel_id . '/events';
	}
}
