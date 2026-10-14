<?php

/**
 * Add the pixel code for Facebook in the auto-insert locations.
 */
class WPCode_Pixel_Auto_Insert_Facebook extends WPCode_Pixel_Auto_Insert_Type {

	/**
	 * The type of pixel.
	 *
	 * @var string
	 */
	public $type = 'facebook';

	/**
	 * The name of the auto-insert type. This is the label that is displayed in the admin.
	 *
	 * @var string
	 */
	public $name = 'Facebook';
	/**
	 * The FB pixel id.
	 *
	 * @var string
	 */
	public $pixel_id;

	/**
	 * @var string
	 */
	public $events_option_name = 'facebook_pixel_events';

	/**
	 * @var string The key used to store the api token in the WPCode settings.
	 */
	public $api_token_key = 'facebook_pixel_api_token';

	/**
	 * @var string
	 */
	public $server_handler_name = 'WPCode_Pixel_Server_Facebook';

	/**
	 * Event names.
	 *
	 * @var string[]
	 */
	public $event_names = array(
		'view_content'   => 'ViewContent',
		'add_to_cart'    => 'AddToCart',
		'begin_checkout' => 'InitiateCheckout',
		'purchase'       => 'Purchase',
	);

	public $standard_events = array(
		'AddPaymentInfo'       => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
		),
		'AddToCart'            => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
		),
		'AddToWishlist'        => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
		),
		'CompleteRegistration' => array(
			'value',
			'currency',
			'content_name',
			'status',
		),
		'Contact'              => array(),
		'CustomizeProduct'     => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
		),
		'Donate'               => array(
			'value',
			'currency',
		),
		'InitiateCheckout'     => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
			'num_items',
		),
		'Lead'                 => array(),
		'Purchase'             => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
			'num_items',
		),
		'Schedule'             => array(),
		'SubmitApplication'    => array(
			'content_category',
			'status',
		),
		'Subscribe'            => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
		),
		'ViewContent'          => array(
			'content_ids',
			'content_type',
			'value',
			'currency',
		),
	);


	/**
	 * Load the server pixel handler.
	 *
	 * @return void
	 */
	public function require_server_handler() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/server/class-wpcode-pixel-server-facebook.php';
	}

	/**
	 * Add the fb pixel snippet to the array of snippets passed to the auto-insert function.
	 *
	 * @return WPCode_Snippet[]
	 */
	public function get_global_header_snippets() {
		$pixel_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $pixel_id ) ) {
			return array();
		}

		$snippet = $this->get_basic_snippet(
			array(
				'code'     => $this->get_fb_pixel_code( $pixel_id ),
				'location' => 'site_wide_header',
			)
		);

		return array( $snippet );
	}

	/**
	 * Get the pixel id in a single place.
	 *
	 * @return string
	 */
	public function get_pixel_id() {
		if ( ! isset( $this->pixel_id ) ) {
			$this->pixel_id = wpcode()->settings->get_option( 'facebook_pixel_id', '' );
		}

		return $this->pixel_id;
	}

	/**
	 * Get the Facebook Pixel code with the pixel id set.
	 *
	 * @param string $pixel_id The pixel id to use, defaults to the one saved in the settings.
	 *
	 * @return string
	 */
	public function get_fb_pixel_code( $pixel_id = '' ) {
		if ( empty( $pixel_id ) ) {
			// If not set, default to the one in the settings.
			$pixel_id = wpcode()->settings->get_option( 'facebook_pixel_id' );
		}

		if ( empty( $pixel_id ) ) {
			return '';
		}

		$extra_events = '';

		if ( $this->has_event( 'page_view' ) ) {
			$extra_events = "fbq('track', 'PageView');";
		}

		$code = "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '%1\$s');%2\$s</script>";

		$code .= '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=%1$s&ev=PageView&noscript=1"/></noscript>';

		return sprintf(
			$code,
			$pixel_id,
			$extra_events
		);
	}

	/**
	 * Get the code for the view content event for products.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function view_content( $provider ) {
		$pixel_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $pixel_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'view_content' ) ) {
			return array();
		}

		// Get the data.
		$data = $this->get_product_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$code = sprintf(
			"<script>fbq('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'view_content' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Facebook Pixel ViewContent Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'view_content' ),
			)
		);

		return array( $snippet );
	}

	/**
	 * Get product data specific to WooCommerce.
	 *
	 * @param WPCode_Pixel_Provider $provider The vendor for the data.
	 *
	 * @return array
	 */
	public function process_product_data( $provider ) {
		$product_data = $provider->get_product_data_filtered();

		if ( empty( $product_data ) || ! is_array( $product_data ) ) {
			return array();
		}

		return array(
			'content_name' => $product_data['name'],
			'content_ids'  => array( $product_data['id'] ),
			'content_type' => 'product',
			'value'        => $product_data['price'],
			'currency'     => $product_data['currency'],
		);
	}

	/**
	 * Get the code for the initiate checkout event.
	 *
	 * @param WPCode_Pixel_Provider $provider The data vendor.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function begin_checkout( $provider ) {
		$pixel_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $pixel_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'begin_checkout' ) ) {
			return array();
		}

		// If we have the API token, let's send the more precise event on the server-side hook instead.
		if ( $this->has_api_token() ) {
			return array();
		}

		// Get the data.
		$data = $this->get_checkout_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$code = sprintf(
			"<script>fbq('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'begin_checkout' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Facebook Pixel InitiateCheckout Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'begin_checkout' ),
			)
		);

		return array( $snippet );
	}

	/**
	 * Get checkout data specific to Facebook.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array
	 */
	public function process_checkout_data( $provider ) {

		$checkout_data = $provider->get_checkout_data_filtered();

		if ( empty( $checkout_data ) ) {
			return array();
		}

		$data = array(
			'content_type' => 'product',
			'num_items'    => $checkout_data['num_items'],
			'currency'     => $checkout_data['currency'],
			'value'        => $checkout_data['total'],
		);

		foreach ( $checkout_data['products'] as $product ) {
			$data['content_ids'][] = $product['id'];
			$data['contents'][]    = array(
				'id'       => $product['id'],
				'quantity' => $product['quantity'],
			);
		}

		return $data;

	}

	/**
	 * Get the code for tracking a purchase event.
	 *
	 * @param $provider WPCode_Pixel_Provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function purchase( $provider ) {
		$pixel_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $pixel_id ) ) {
			return array();
		}

		// If we have the API token, let's send the more precise event on the server-side hook instead.
		if ( $this->has_api_token() ) {
			return array();
		}

		// Is this event enabled in the settings?
		if ( ! $this->has_event( 'purchase' ) ) {
			return array();
		}

		// Get the data.
		$data = $this->get_purchase_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$event_sent = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_sent_facebook_purchase' );

		// If the event for this specific order and pixel was already sent, bail.
		if ( $event_sent ) {
			return array();
		}

		$code = sprintf(
			"<script>fbq('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'purchase' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Facebook Pixel Purchase Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'purchase' ),
			)
		);

		// Mark event as sent for this pixel to avoid duplicated events.
		$provider->store_extra_data(
			$data['order_id'],
			array(
				'_wpcode_pixel_sent_facebook_purchase' => time(),
			)
		);

		return array( $snippet );
	}

	/**
	 * Get purchase data specific to the FB Pixel from the data provider.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array
	 */
	public function process_purchase_data( $provider ) {

		$purchase_data = $provider->get_purchase_data_filtered();

		if ( empty( $purchase_data ) ) {
			return array();
		}

		$data = array(
			'content_type' => 'product',
			'num_items'    => $purchase_data['num_items'],
			'currency'     => $purchase_data['currency'],
			'value'        => $purchase_data['total'],
			'order_id'     => $purchase_data['order_id'],
		);

		foreach ( $purchase_data['products'] as $product ) {
			$data['content_ids'][] = $product['id'];
			$data['contents'][]    = array(
				'id'       => $product['id'],
				'quantity' => $product['quantity'],
			);
		}

		return $data;

	}

	/**
	 * If the add to cart event is enabled, add the code to the footer.
	 * to make sure we send the event when the user clicks the add to cart button.
	 * This should load on all the pages to capture add-to-cart events anywhere on the site.
	 *
	 * @return string
	 */
	public function get_cart_event_code() {
		if ( ! $this->has_event( 'add_to_cart' ) || empty( $this->get_pixel_id() ) ) {
			return '';
		}

		if ( $this->has_api_token() ) {
			// Don't send frontend event if we can send an API event.
			return '';
		}

		return "
fbq( 'track', '{$this->get_event_name( 'add_to_cart' )}', {
	content_ids: [id],
	quantity: quantity,
	content_type: 'product',
	currency: '%CURRENCY%',
	contents: [{
		id: id,
		quantity: quantity,
	}],
} );
		";
	}

	/**
	 * Init the server handler with the pixel-specific data.
	 *
	 * @return mixed
	 */
	public function init_server_handler() {
		$handler = $this->server_handler_name;

		return new $handler( $this->get_pixel_id(), $this->get_api_token() );

	}

	/**
	 * Send a server-side event for the purchase.
	 * This should not be called directly as it is called through send_server_event to ensure proper checks.
	 *
	 * @param array                 $data The event data from the provider.
	 * @param WPCode_Pixel_Provider $provider The provider instance.
	 *
	 * @return void
	 */
	public function server_purchase( $data, $provider ) {
		$event_sent = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_sent_facebook_purchase' );

		// If the event for this specific order and pixel was already sent, bail.
		if ( $event_sent ) {
			return;
		}

		$event_data = array(
			'event_name'  => $this->get_event_name( 'purchase' ),
			'event_time'  => time(),
			'custom_data' => array(
				'content_type' => 'product',
				'num_items'    => $data['num_items'],
				'currency'     => $data['currency'],
				'value'        => $data['total'],
			),
			'contents'    => array(),
			'content_ids' => array(),
		);

		foreach ( $data['products'] as $product ) {
			$event_data['custom_data']['content_ids'][] = $product['id'];
			$event_data['contents'][]                   = array(
				'id'       => $product['id'],
				'quantity' => $product['quantity'],
			);
		}

		$event_data['user_data'] = $this->get_user_data( $data );

		$event_data['user_data']['fbc'] = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_fbc' );
		$event_data['user_data']['fbp'] = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_fbp' );

		$this->send_server_event( $event_data );

		// Mark event as sent for this pixel to avoid duplicated events.
		$provider->store_extra_data(
			$data['order_id'],
			array(
				'_wpcode_pixel_sent_facebook_purchase' => $event_data['event_time'],
			)
		);
	}

	/**
	 * Send a server-side event for add to cart.
	 * This should not be called directly as it is called through send_server_event to ensure proper checks.
	 *
	 * @param array                 $data The event data from the provider.
	 * @param WPCode_Pixel_Provider $provider The provider instance.
	 *
	 * @return void
	 */
	public function server_add_to_cart( $data, $provider ) {

		$event_data = array(
			'event_name'  => $this->get_event_name( 'add_to_cart' ),
			'event_time'  => time(),
			'custom_data' => array(
				'content_type' => 'product',
				'currency'     => $data['currency'],
				'value'        => $data['price'],
			),
			'contents'    => array(
				array(
					'id'         => $data['product_id'],
					'quantity'   => $data['quantity'],
					'item_price' => $data['price'],
				),
			),
			'content_ids' => array( (string) $data['product_id'] ),
		);

		$event_data['user_data'] = $this->get_user_data( $data );

		$this->send_server_event( $event_data );
	}

	/**
	 * Get an array of user data formatted for a server request to FB.
	 *
	 * @param array $data The data from the provider.
	 *
	 * @return array The data processed
	 */
	public function get_user_data( $data ) {
		$data = wp_parse_args(
			$data,
			array(
				'user_ip'    => '',
				'user_agent' => '',
				'user_id'    => '',
				'email'      => '',
				'city'       => '',
				'state'      => '',
				'country'    => '',
				'zip'        => '',
				'first_name' => '',
				'last_name'  => '',
				'phone'      => '',
			)
		);

		$user_data = array(
			'client_ip_address' => $data['user_ip'],
			'client_user_agent' => $data['user_agent'],
			'fbc'               => $this->get_fbc(),
			'fbp'               => $this->get_fbp(),
			'em'                => array(
				$this->hash( $data['email'] ),
			),
			'external_id'       => $this->hash( $data['user_id'] ),
			'ct'                => $this->hash( $data['city'] ),
			'st'                => $this->hash( $data['state'] ),
			'country'           => $this->hash( $data['country'] ),
			'zp'                => $this->hash( $data['zip'] ),
			'fn'                => $this->hash( $data['first_name'] ),
			'ln'                => $this->hash( $data['last_name'] ),
			'ph'                => $this->hash( $data['phone'] ),
		);

		foreach ( $user_data as $key => $value ) {
			if ( ! is_array( $value ) && '' === (string) $value ) {
				unset( $user_data[ $key ] );
			}
		}

		return $user_data;
	}

	/**
	 * Look for fb-specific cookies and store them with the order for proper attribution when order is marked
	 * as complete.
	 *
	 * @param int $order_id The order id.
	 *
	 * @return array
	 */
	public function get_extra_order_data( $order_id ) {
		$extra_data = array();

		$fbc = $this->get_fbc();
		if ( ! empty( $fbc ) ) {
			$extra_data['_wpcode_pixel_fbc'] = $fbc;
		}
		$fbp = $this->get_fbp();
		if ( ! empty( $fbp ) ) {
			$extra_data['_wpcode_pixel_fbp'] = $fbp;
		}

		return $extra_data;
	}

	/**
	 * Get facebook click id from the browser cookie.
	 *
	 * @return string
	 */
	public function get_fbc() {
		if ( isset( $_COOKIE['_fbc'] ) ) {
			return sanitize_text_field( $_COOKIE['_fbc'] );
		}

		return '';
	}

	/**
	 * Get facebook browser id from the browser cookie.
	 *
	 * @return string
	 */
	public function get_fbp() {
		if ( isset( $_COOKIE['_fbp'] ) ) {
			return sanitize_text_field( $_COOKIE['_fbp'] );
		}

		return '';
	}

	/**
	 * Send a server-side event for add to cart.
	 * This should not be called directly as it is called through send_server_event to ensure proper checks.
	 *
	 * @param array                 $data The event data from the provider.
	 * @param WPCode_Pixel_Provider $provider The provider instance.
	 *
	 * @return void
	 */
	public function server_begin_checkout( $data, $provider ) {

		$event_data = array(
			'event_name'  => $this->get_event_name( 'begin_checkout' ),
			'event_time'  => time(),
			'custom_data' => array(
				'content_type' => 'product',
				'num_items'    => $data['num_items'],
				'currency'     => $data['currency'],
				'value'        => $data['total'],
			),
			'contents'    => array(),
			'content_ids' => array(),
		);

		foreach ( $data['products'] as $product ) {
			$event_data['custom_data']['content_ids'][] = $product['id'];
			$event_data['contents'][]                   = array(
				'id'       => $product['id'],
				'quantity' => $product['quantity'],
			);
		}

		$event_data['user_data'] = $this->get_user_data( $data );

		$this->send_server_event( $event_data );
	}
}

new WPCode_Pixel_Auto_Insert_Facebook();
