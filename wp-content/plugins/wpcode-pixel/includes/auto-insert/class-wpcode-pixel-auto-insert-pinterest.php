<?php

/**
 * Add the pixel code for Pinterest in the auto-insert locations.
 */
class WPCode_Pixel_Auto_Insert_Pinterest extends WPCode_Pixel_Auto_Insert_Type {

	/**
	 * The type of pixel.
	 *
	 * @var string
	 */
	public $type = 'pinterest';

	/**
	 * The name of the auto-insert type. This is the label that is displayed in the admin.
	 *
	 * @var string
	 */
	public $name = 'Pinterest';

	/**
	 * The Pinterest pixel id.
	 *
	 * @var string
	 */
	public $tag_id;

	/**
	 * The option name for storing the events.
	 *
	 * @var string
	 */
	public $events_option_name = 'pinterest_pixel_events';

	/**
	 * The server handler name.
	 *
	 * @var string
	 */
	public $server_handler_name = 'WPCode_Pixel_Server_Pinterest';

	/**
	 * The api token key.
	 *
	 * @var string
	 */
	public $api_token_key = 'pinterest_conversion_token';

	/**
	 * The ad account id.
	 *
	 * @var string
	 */
	public $ad_account_id;

	/**
	 * Event names.
	 *
	 * @var string[]
	 */
	public $event_names = array(
		'pagevisit_product' => 'pagevisit',
		'add_to_cart'       => 'addtocart',
		'begin_checkout'    => 'pagevisit',
		'purchase'          => 'checkout',
	);

	/**
	 * Standard events.
	 *
	 * @var array
	 */
	public $standard_events = array(
		'PageVisit'    => array(),
		'ViewCategory' => array(
			'category',
		),
		'Search'       => array(
			'search_query',
		),
		'AddToCart'    => array(
			'value',
			'currency',
			'order_quantity',
			'line_items',
		),
		'Checkout'     => array(
			'value',
			'currency',
			'order_quantity',
			'line_items',
		),
		'WatchVideo'   => array(
			'video_title',
		),
		'Lead'         => array(),
		'Signup'       => array(),
		'Custom'       => array(),
	);


	/**
	 * Load the server pixel handler.
	 *
	 * @return void
	 */
	public function require_server_handler() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/server/class-wpcode-pixel-server-pinterest.php';
	}

	/**
	 * Get the pixel id in a single place.
	 *
	 * @return string
	 */
	public function get_tag_id() {
		if ( ! isset( $this->tag_id ) ) {
			$this->tag_id = wpcode()->settings->get_option( 'pinterest_id', '' );
		}

		return $this->tag_id;
	}

	public function get_ad_account_id() {
		if ( ! isset( $this->ad_account_id ) ) {
			$this->ad_account_id = wpcode()->settings->get_option( 'pinterest_ad_account_id', '' );
		}

		return $this->ad_account_id;
	}

	/**
	 * Add the pinterest pixel snippet to the array of snippets passed to the auto-insert function.
	 *
	 * @return WPCode_Snippet[]
	 */
	public function get_global_header_snippets() {
		$tag_id = $this->get_tag_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
			return array();
		}

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Pinterest Global Tag',
				'code'     => $this->get_pinterest_tag_code( $tag_id ),
				'location' => 'site_wide_header',
			)
		);

		return array( $snippet );
	}

	/**
	 * Get the Pinterest Pixel code with the pixel id set.
	 *
	 * @param string $tag_id The pixel id to use, defaults to the one saved in the settings.
	 *
	 * @return string
	 */
	public function get_pinterest_tag_code( $tag_id = '' ) {
		if ( empty( $tag_id ) ) {
			// If not set, default to the one in the settings.
			$tag_id = wpcode()->settings->get_option( 'pinterest_id' );
		}

		if ( empty( $tag_id ) ) {
			return '';
		}

		$code = <<<CODE
<!-- WPCode Pinterest Tag -->
<script>
	!function(e){if(!window.pintrk){window.pintrk = function () {
		window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
		n=window.pintrk;n.queue=[],n.version="3.0";var
		t=document.createElement("script");t.async=!0,t.src=e;var
		r=document.getElementsByTagName("script")[0];
		r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
	pintrk('load', '%1\$s');
	pintrk('page');
</script>
CODE;

		$code .= '<noscript><img height="1" width="1" style="display:none;" alt="" src="https://ct.pinterest.com/v3/?event=init&tid=%1$s&noscript=1" /></noscript>';

		return sprintf(
			$code,
			$tag_id
		);
	}

	/**
	 * Get the code for the view content event for this pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function view_content( $provider ) {
		$tag_id = $this->get_tag_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'pagevisit_product' ) ) {
			return array();
		}

		// Get the data.
		$data = $this->get_product_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$code = sprintf(
			"<script>pintrk('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'pagevisit_product' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Pinterest Product Page Visit Event',
				'location' => $provider->get_location_key( 'view_content' ),
				'code'     => $code,
			)
		);

		return array( $snippet );
	}


	/**
	 * Get the code for the view content event for checkout.
	 *
	 * @param WPCode_Pixel_Provider $provider The data vendor.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function begin_checkout( $provider ) {
		$tag_id = $this->get_tag_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'begin_checkout' ) ) {
			return array();
		}

		if ( $this->has_api_token() ) {
			// Don't send frontend event if we can send an API event.
			return array();
		}

		// Get the data.
		$data = $this->get_checkout_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$code = sprintf(
			"<script>pintrk('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'begin_checkout' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Pinterest Checkout Page Visit Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'begin_checkout' ),
			)
		);

		return array( $snippet );
	}

	/**
	 * Get product data specific to WooCommerce.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array
	 */
	public function process_product_data( $provider ) {
		$product_data = $provider->get_product_data_filtered();

		if ( empty( $product_data ) || ! is_array( $product_data ) ) {
			return array();
		}

		return array(
			'currency'   => $product_data['currency'],
			'line_items' => array(
				array(
					'product_name'     => $product_data['name'],
					'product_id'       => $product_data['id'],
					'product_price'    => $product_data['price'],
					'product_category' => $this->get_product_category( $product_data ),
				),
			),
		);
	}

	/**
	 * Get checkout data specific to this pixel using provider data.
	 *
	 * @param WPCode_Pixel_Provider $provider The data vendor.
	 *
	 * @return array
	 */
	public function process_checkout_data( $provider ) {
		$checkout_data = $provider->get_checkout_data_filtered();

		if ( empty( $checkout_data ) ) {
			return array();
		}

		$data = array(
			'currency'   => $checkout_data['currency'],
			'value'      => $checkout_data['total'],
			'line_items' => array(),
		);

		if ( ! empty( $checkout_data['coupon'] ) ) {
			$data['promo_code'] = $checkout_data['coupon'];
		}

		foreach ( $checkout_data['products'] as $product ) {
			$data['line_items'][] = array(
				/**
				 * @var WC_Product $product
				 */
				'product_name'     => $product['name'],
				'product_id'       => $product['id'],
				'product_quantity' => $product['quantity'],
				'product_price'    => $product['price'],
				'product_category' => $this->get_product_category( $product ),
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
		$tag_id = $this->get_tag_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'purchase' ) ) {
			return array();
		}

		// Get the data.
		$data = $this->get_purchase_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$code = sprintf(
			"<script>pintrk('track', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'purchase' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Pinterest Checkout Event',
				'location' => $provider->get_location_key( 'purchase' ),
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * Get purchase data specific to WooCommerce.
	 *
	 * @param $provider WPCode_Pixel_Provider The data provider.
	 *
	 * @return array
	 */
	public function process_purchase_data( $provider ) {
		$purchase_data = $provider->get_purchase_data_filtered();

		if ( empty( $purchase_data ) ) {
			return array();
		}

		$data = array(
			'currency'   => $purchase_data['currency'],
			'value'      => $purchase_data['total'],
			'order_id'   => $purchase_data['order_id'],
			'line_items' => array(),
			'event_id'   => 'wpc_checkout_' . $purchase_data['order_id'],
			// This is a unique id based on the order id so it will match the server one always and thus more data will be captured.
		);

		if ( ! empty( $purchase_data['coupon'] ) ) {
			$data['promo_code'] = $purchase_data['coupon'];
		}

		foreach ( $purchase_data['products'] as $product ) {
			$data['line_items'][] = array(
				'product_id'       => $product['id'],
				'product_quantity' => $product['quantity'],
				'product_name'     => $product['name'],
				'product_category' => $this->get_product_category( $product ),
				'product_price'    => $product['price'],
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
		if ( ! $this->has_event( 'add_to_cart' ) || empty( $this->get_tag_id() ) ) {
			return '';
		}

		if ( $this->has_api_token() ) {
			// Don't send frontend event if we can send an API event.
			return '';
		}

		return "
pintrk( 'track', '{$this->get_event_name('add_to_cart')}', {
	currency: '%CURRENCY%',
	line_items: [{
		product_id: id,
		product_quantity: quantity,
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

		return new $handler( $this->get_ad_account_id(), $this->get_api_token() );

	}

	/**
	 * Also check if we have the ad account id to make sure we can make server requests.
	 *
	 * @return bool
	 */
	public function has_api_token() {
		return ! empty( $this->get_ad_account_id() ) && ! empty( $this->get_api_token() );
	}

	/**
	 * Send a server-side event for begin checkout.
	 * This should not be called directly as it is called through send_server_event to ensure proper checks.
	 *
	 * @param array                 $data The event data from the provider.
	 * @param WPCode_Pixel_Provider $provider The provider instance.
	 *
	 * @return void
	 */
	public function server_begin_checkout( $data, $provider ) {

		$event_data = array(
			'event_name'       => $this->get_event_name( 'begin_checkout' ),
			'event_time'       => time(),
			'action_source'    => 'web',
			'event_source_url' => get_permalink(), // If we're on the checkout page this should work.
			'custom_data'      => array(
				'num_items'   => $data['num_items'],
				'currency'    => $data['currency'],
				'value'       => (string) $data['total'],
				'content_ids' => array(),
				'contents'    => array(),
			),
			'event_id'         => $this->get_event_id(),
		);

		foreach ( $data['products'] as $product ) {
			$event_data['custom_data']['content_ids'][] = (string) $product['id'];
			$event_data['custom_data']['contents'][]    = array(
				'item_price' => (string) $product['price'],
				'quantity'   => absint( $product['quantity'] ),
			);
		}

		$event_data['user_data'] = $this->get_user_data( $data );

		$this->send_server_event( $event_data );
	}

	/**
	 * @return string
	 */
	public function get_event_id() {
		return uniqid();
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
			'em'                => array(
				$this->hash( $data['email'] ),
			),
			'external_id'       => array( $this->hash( $data['user_id'] ) ),
			'ct'                => array( $this->hash( $data['city'] ) ),
			'st'                => array( $this->hash( $data['state'] ) ),
			'country'           => array( $this->hash( $data['country'] ) ),
			'zp'                => array( $this->hash( $data['zip'] ) ),
			'fn'                => array( $this->hash( $data['first_name'] ) ),
			'ln'                => array( $this->hash( $data['last_name'] ) ),
			'ph'                => array( $this->hash( $data['phone'] ) ),
		);

		foreach ( $user_data as $key => $value ) {
			if ( ! is_array( $value ) && '' === (string) $value ) {
				unset( $user_data[ $key ] );
			}
			if ( is_array( $value ) && isset( $value[0] ) && empty( $value[0] ) ) {
				unset( $user_data[ $key ][0] );
			}
		}

		return $user_data;
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
		$source_url = wp_get_referer();
		if ( false === $source_url ) {
			$source_url = get_permalink( $data['product_id'] );
		}

		$event_data = array(
			'event_name'       => $this->get_event_name( 'add_to_cart' ),
			'event_time'       => time(),
			'action_source'    => 'web',
			'event_source_url' => $source_url,
			'custom_data'      => array(
				'currency'    => $data['currency'],
				'value'       => $data['price'],
				'content_ids' => array(
					(string) $data['product_id'],
				),
				'contents'    => array(
					array(
						'quantity'   => absint( $data['quantity'] ),
						'item_price' => $data['price'],
					),
				),
			),
			'event_id'         => $this->get_event_id(),
		);

		$event_data['user_data'] = $this->get_user_data( $data );

		$this->send_server_event( $event_data );
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
		$event_sent = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_sent_pinterest_purchase' );

		// If the event for this specific order and pixel was already sent, bail.
		// Even though we use a unique event id, events sent more than 24hrs apart will not be deduplicated by the API
		// so we prevent sending the same event twice by adding a provider-specific order meta item.
		if ( $event_sent ) {
			return;
		}

		$event_data = array(
			'event_name'       => $this->get_event_name( 'purchase' ),
			'event_time'       => time(),
			'event_id'         => 'wpc_checkout_' . $data['order_id'], // This will be unique per order.
			'event_source_url' => $provider->get_checkout_url(),
			'action_source'    => 'web',
			'custom_data'      => array(
				'num_items'   => $data['num_items'],
				'currency'    => $data['currency'],
				'value'       => $data['total'],
				'order_id'    => (string) $data['order_id'],
				'contents'    => array(),
				'content_ids' => array(),
			),
		);

		foreach ( $data['products'] as $product ) {
			$event_data['custom_data']['content_ids'][] = (string) $product['id'];
			$event_data['custom_data']['contents'][]    = array(
				'item_price' => (string) $product['price'],
				'quantity'   => absint( $product['quantity'] ),
			);
		}

		$event_data['user_data'] = $this->get_user_data( $data );

		$this->send_server_event( $event_data );

		// Mark event as sent for this pixel to avoid duplicated events.
		$provider->store_extra_data(
			$data['order_id'],
			array(
				'_wpcode_pixel_sent_pinterest_purchase' => $event_data['event_time'],
			)
		);
	}
}

new WPCode_Pixel_Auto_Insert_Pinterest();
