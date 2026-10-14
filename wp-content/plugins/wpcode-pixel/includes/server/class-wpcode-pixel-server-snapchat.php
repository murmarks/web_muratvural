<?php

/**
 * Class WPCode_Pixel_Server_Snapchat
 */
class WPCode_Pixel_Server_Snapchat extends WPCode_Pixel_Server {

	/**
	 * The api URL.
	 *
	 * @var string
	 */
	public $api_url = 'https://tr.snapchat.com/v2/conversion';

	/**
	 * The pixel type key.
	 *
	 * @var string
	 */
	public $pixel_key = 'snapchat';

	/**
	 * This is called before an event is sent in order to add
	 * any additional data to the event.
	 *
	 * @param array $data The event data.
	 *
	 * @return mixed
	 */
	public function prepare_event( $data ) {
		$data['pixel_id']              = $this->pixel_id;
		$data['timestamp']             = time();
		$data['event_conversion_type'] = 'WEB';

		return wp_json_encode( $data );
	}

	/**
	 * Add authorization header using the api token.
	 *
	 * @return string[]
	 */
	public function get_headers() {
		// Use token as authorization bearer.
		return array(
			'Authorization' => 'Bearer ' . $this->api_token,
			'Content-Type'  => 'application/json; charset=utf-8',
		);
	}
}
