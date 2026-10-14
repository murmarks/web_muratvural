<?php

abstract class WPCode_Pixel_Server {

	/**
	 * The api URL.
	 *
	 * @var string
	 */
	public $api_url;
	/**
	 * Method used for requests.
	 *
	 * @var string
	 */
	public $api_method = 'POST';
	/**
	 * The pixel id.
	 *
	 * @var string
	 */
	public $pixel_id;
	/**
	 * The api token.
	 *
	 * @var string
	 */
	public $api_token;

	/**
	 * The pixel type key.
	 *
	 * @var string
	 */
	public $pixel_key = '';

	/**
	 * Constructor.
	 */
	public function __construct( $pixel_id = '', $api_token = '' ) {
		$this->pixel_id  = $pixel_id;
		$this->api_token = $api_token;
	}

	/**
	 * Send an event to the server but first give the pixel a chance to add any additional data.
	 *
	 * @param array $data The event data.
	 *
	 * @return void
	 */
	public function send_event( $data ) {
		$data = apply_filters( "wpcode_pixel_server_event_data_{$this->pixel_key}", $this->prepare_event( $data ) );

		// Send the event to the server.
		$this->send_request( $data );
	}

	/**
	 * Process event data in the format for the API, optional.
	 *
	 * @param array $data The event data.
	 *
	 * @return array
	 */
	public function prepare_event( $data ) {
		return $data;
	}

	/**
	 * Send a request to the API server.
	 *
	 * @param mixed $data The data to send in the body.
	 *
	 * @return void
	 */
	public function send_request( $data ) {

		$url     = $this->get_api_url();
		$method  = $this->get_api_method();
		$headers = $this->get_headers();

		// Send the request.
		$response = wp_remote_request(
			$url,
			array(
				'headers'  => $headers,
				'blocking' => apply_filters( 'wpcode_pixel_send_request_blocking', false ),
				'method'   => $method,
				'body'     => $data,
			)
		);
	}

	public function get_api_url() {
		return $this->api_url;
	}

	public function get_api_method() {
		return $this->api_method;
	}

	public function get_headers() {
		return array();
	}
}
