<?php

/**
 * Add the pixel code for Snapchat in the auto-insert locations.
 */
class WPCode_Pixel_Auto_Insert_Snapchat extends WPCode_Pixel_Auto_Insert_Type {

	/**
	 * The type of pixel.
	 *
	 * @var string
	 */
	public $type = 'snapchat';

	/**
	 * The name of the auto-insert type. This is the label that is displayed in the admin.
	 *
	 * @var string
	 */
	public $name = 'Snapchat';
	/**
	 * The FB pixel id.
	 *
	 * @var string
	 */
	public $pixel_id;

	/**
	 * @var string
	 */
	public $events_option_name = 'snapchat_pixel_events';

	/**
	 * @var string The key used to store the api token in the WPCode settings.
	 */
	public $api_token_key = 'snapchat_pixel_api_token';

	/**
	 * @var string
	 */
	public $server_handler_name = 'WPCode_Pixel_Server_Snapchat';

	/**
	 * Event names.
	 *
	 * @var string[]
	 */
	public $event_names = array(
		'view_content'   => 'VIEW_CONTENT',
		'add_to_cart'    => 'ADD_CART',
		'begin_checkout' => 'START_CHECKOUT',
		'purchase'       => 'PURCHASE',
	);

	/**
	 * Standard events.
	 *
	 * @var array[]
	 */
	public $standard_events = array(
		'PURCHASE'             => array(),
		'SAVE'                 => array(),
		'START_CHECKOUT'       => array(),
		'ADD_CART'             => array(),
		'ADD_BILLING'          => array(),
		'SIGN_UP'              => array(),
		'SUBSCRIBE'            => array(),
		'AD_CLICK'             => array(),
		'COMPLETE_TUTORIAL'    => array(),
		'LEVEL_COMPLETE'       => array(),
		'SHARE'                => array(),
		'ACHIEVEMENT_UNLOCKED' => array(),
		'ADD_TO_WISHLIST'      => array(),
		'RATE'                 => array(),
		'START_TRIAL'          => array(),
	);


	/**
	 * Load the server pixel handler.
	 *
	 * @return void
	 */
	public function require_server_handler() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/server/class-wpcode-pixel-server-snapchat.php';
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
				'code'     => $this->get_pixel_code( $pixel_id ),
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
			$this->pixel_id = wpcode()->settings->get_option( 'snapchat_pixel_id', '' );
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
	public function get_pixel_code( $pixel_id = '' ) {
		if ( empty( $pixel_id ) ) {
			// If not set, default to the one in the settings.
			$pixel_id = $this->get_pixel_id();
		}

		if ( empty( $pixel_id ) ) {
			return '';
		}

		$extra_events = '';

		if ( $this->has_event( 'page_view' ) ) {
			$extra_events = "snaptr('track', 'PAGE_VIEW');";
		}

		$code = "<script>(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};a.queue=[];var s='script';r=t.createElement(s);r.async=!0;r.src=n;var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u);})(window,document,'https://sc-static.net/scevent.min.js');";

		$code .= "snaptr('init', '%1\$s', %3\$s);%2\$s</script>";

		return sprintf(
			$code,
			$pixel_id,
			$extra_events,
			wp_json_encode( $this->get_init_data() )
		);
	}

	/**
	 * Get the user data to pass to Snapchat.
	 *
	 * @return array
	 */
	public function get_init_data() {
		$data = array();

		if ( is_user_logged_in() ) {
			// SHA 256 the user email to pass it to Snapchat.
			$data['user_hashed_email'] = hash( 'sha256', wp_get_current_user()->user_email );
		}

		return $data;
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
			"<script>snaptr('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'view_content' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Snapchat Pixel VIEW_CONTENT Event',
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
			'description'   => $product_data['name'],
			'item_ids'      => array( $product_data['id'] ),
			'item_category' => $this->get_product_category( $product_data ),
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
			"<script>snaptr('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'begin_checkout' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Snapchat Pixel InitiateCheckout Event',
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
			'currency' => $checkout_data['currency'],
			'price'    => $checkout_data['total'],
		);

		$quantities = array();

		foreach ( $checkout_data['products'] as $product ) {
			$data['item_ids'][] = $product['id'];
			$quantities[]       = $product['quantity'];
		}

		$data['number_items'] = implode( ',', $quantities );

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

		$event_sent = $provider->get_order_meta( $data['transaction_id'], '_wpcode_pixel_sent_snapchat_purchase' );

		// If the event for this specific order and pixel was already sent, bail.
		if ( $event_sent ) {
			return array();
		}

		$code = sprintf(
			"<script>snaptr('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'purchase' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Snapchat Pixel Purchase Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'purchase' ),
			)
		);

		// Mark event as sent for this pixel to avoid duplicated events.
		$provider->store_extra_data(
			$data['transaction_id'],
			array(
				'_wpcode_pixel_sent_snapchat_purchase' => time(),
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
			'content_type'   => 'product',
			'currency'       => $purchase_data['currency'],
			'price'          => $purchase_data['total'],
			'transaction_id' => $purchase_data['order_id'],
		);

		foreach ( $purchase_data['products'] as $product ) {
			$data['item_ids'][] = $product['id'];
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
snaptr( 'track', '{$this->get_event_name( 'add_to_cart' )}', {
	item_ids: [id],
	number_items: quantity,
	currency: '%CURRENCY%',
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
		$event_sent = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_sent_snapchat_purchase' );

		// If the event for this specific order and pixel was already sent, bail.
		if ( $event_sent ) {
			return;
		}

		$event_data = array(
			'event_type'     => $this->get_event_name( 'purchase' ),
			'transaction_id' => $data['order_id'],
			'price'          => $data['total'],
			'currency'       => $data['currency'],
			'contents'       => array(),
			'item_ids'       => array(),
		);

		$quantities = array();

		foreach ( $data['products'] as $product ) {
			$event_data['item_ids'][] = $product['id'];
			$quantities[]             = $product['quantity'];
		}

		$event_data['number_items'] = implode( ',', $quantities );

		$event_data = array_merge( $event_data, $this->get_user_data( $data ) );

		$event_data['uuid_c1'] = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_scid' );

		$this->send_server_event( $event_data );

		// Mark event as sent for this pixel to avoid duplicated events.
		$provider->store_extra_data(
			$data['order_id'],
			array(
				'_wpcode_pixel_sent_snapchat_purchase' => time(),
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
			'event_type'    => $this->get_event_name( 'add_to_cart' ),
			'item_category' => $this->get_product_category( $data ),
			'currency'      => $data['currency'],
			'number_items'  => $data['quantity'],
			'price'         => $data['price'],
			'item_ids'      => array( (string) $data['product_id'] ),
		);

		$event_data = array_merge( $event_data, $this->get_user_data( $data ) );

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
			'hashed_ip_address'     => $this->hash( $data['user_ip'] ),
			'hashed_email'          => $this->hash( $data['email'] ),
			'hashed_phone_number'   => $this->hash( $data['phone'] ),
			'user_agent'            => $data['user_agent'],
			'uuid_c1'               => $this->get_scid(),
			'hashed_city_sha'       => $this->hash( $data['city'] ),
			'hashed_state_sha'      => $this->hash( $data['state'] ),
			'country'               => $data['country'],
			'hashed_zip'            => $this->hash( $data['zip'] ),
			'hashed_first_name_sha' => $this->hash( $data['first_name'] ),
			'hashed_last_name_sha'  => $this->hash( $data['last_name'] ),
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

		$scid = $this->get_scid();
		if ( ! empty( $scid ) ) {
			$extra_data['_wpcode_pixel_scid'] = $scid;
		}

		return $extra_data;
	}

	/**
	 * Get Snapchat click id from the browser cookie.
	 *
	 * @return string
	 */
	public function get_scid() {
		if ( isset( $_COOKIE['_scid'] ) ) {
			return sanitize_text_field( $_COOKIE['_scid'] );
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
			'event_type' => $this->get_event_name( 'begin_checkout' ),
			'currency'   => $data['currency'],
			'price'      => $data['total'],
			'item_ids'   => array(),
		);

		$quantities = array();

		foreach ( $data['products'] as $product ) {
			$event_data['item_ids'][] = $product['id'];
			$quantities[]             = $product['quantity'];
		}
		$event_data['number_items'] = implode( ',', $quantities );

		$event_data = array_merge( $event_data, $this->get_user_data( $data ) );

		$this->send_server_event( $event_data );
	}
}

new WPCode_Pixel_Auto_Insert_Snapchat();
