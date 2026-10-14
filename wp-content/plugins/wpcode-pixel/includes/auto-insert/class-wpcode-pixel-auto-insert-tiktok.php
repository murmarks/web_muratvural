<?php

/**
 * Add the pixel code for TikTok in the auto-insert locations.
 */
class WPCode_Pixel_Auto_Insert_TikTok extends WPCode_Pixel_Auto_Insert_Type {

	/**
	 * The type of pixel.
	 *
	 * @var string
	 */
	public $type = 'tiktok';

	/**
	 * The name of the auto-insert type. This is the label that is displayed in the admin.
	 *
	 * @var string
	 */
	public $name = 'TikTok';

	/**
	 * The TikTok pixel id.
	 *
	 * @var string
	 */
	public $pixel_id;

	/**
	 * The events option name.
	 *
	 * @var string
	 */
	public $events_option_name = 'tiktok_pixel_events';

	/**
	 * The server handler name.
	 *
	 * @var string
	 */
	public $server_handler_name = 'WPCode_Pixel_Server_TikTok';

	/**
	 * The api token key.
	 *
	 * @var string
	 */
	public $api_token_key = 'tiktok_access_token';

	/**
	 * Event names.
	 *
	 * @var string[]
	 */
	public $event_names = array(
		'view_content'   => 'ViewContent',
		'add_to_cart'    => 'AddToCart',
		'begin_checkout' => 'InitiateCheckout',
		'purchase'       => 'PlaceAnOrder',
	);

	/**
	 * Standard events.
	 *
	 * @var array
	 */
	public $standard_events = array(
		'AddPaymentInfo'       => array(),
		'AddToCart'            => array(
			'value',
			'currency',
			'content_type',
			'content_id',
			'content_name',
			'content_category',
			'content_ids',
		),
		'AddToWishlist'        => array(
			'value',
			'currency',
			'content_type',
			'content_id',
			'content_name',
			'content_category',
			'content_ids',
		),
		'ClickButton'          => array(),
		'CompletePayment'      => array(
			'value',
			'currency',
		),
		'CompleteRegistration' => array(
			'value',
			'currency',
		),
		'Contact'              => array(),
		'Download'             => array(),
		'Lead'                 => array(),
		'PlaceAnOrder'         => array(
			'value',
			'currency',
			'content_type',
			'content_id',
			'content_name',
			'content_category',
			'content_ids',
			'order_id',
		),
		'Search'               => array(
			'query',
		),
		'SubmitForm'           => array(),
		'Subscribe'            => array(),
		'ViewContent'          => array(),
	);


	/**
	 * Load the server pixel handler.
	 *
	 * @return void
	 */
	public function require_server_handler() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/server/class-wpcode-pixel-server-tiktok.php';
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
	 * Get the code for the view content event for WooCommerce checkout.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function begin_checkout( $provider ) {
		$tag_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'begin_checkout' ) ) {
			return array();
		}

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
			"<script>ttq.track('%1\$s', %2\$s);</script>",
			$this->get_event_name( 'begin_checkout' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'TikTok Initiate Checkout Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'begin_checkout' ),
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
			$this->pixel_id = wpcode()->settings->get_option( 'tiktok_pixel_id', '' );
		}

		return $this->pixel_id;
	}

	/**
	 * Get checkout data specific to WooCommerce.
	 *
	 * @return array
	 */
	public function process_checkout_data( $provider ) {

		$checkout_data = $provider->get_checkout_data_filtered();

		if ( empty( $checkout_data ) ) {
			return array();
		}

		$data = array(
			'currency'     => $checkout_data['currency'],
			'value'        => $checkout_data['total'],
			'contents'     => array(),
			'content_type' => 'product',
		);

		if ( ! empty( $checkout_data['coupon'] ) ) {
			$data['query'] = $checkout_data['coupon'];
		}

		foreach ( $checkout_data['products'] as $product ) {
			$data['contents'][] = array(
				'content_id'       => $product['id'],
				'content_name'     => $product['name'],
				'quantity'         => $product['quantity'],
				'price'            => $product['price'],
				'content_category' => $this->get_product_category( $product ),
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
		$tag_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
			return array();
		}

		if ( ! $this->has_event( 'purchase' ) ) {
			return array();
		}

		// If we have the API token, let's send the more precise event on the server-side hook instead.
		if ( $this->has_api_token() ) {
			return array();
		}

		// Get the data.
		$data = $this->get_purchase_data( $provider );

		// If we don't have any data, let's bail.
		if ( empty( $data ) ) {
			return array();
		}

		$code = sprintf(
			"<script>ttq.track('%1\$s', %2\$s);</script>",
			$this->get_event_name( 'purchase' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'TikTok Checkout Event',
				'location' => $provider->get_location_key( 'purchase' ),
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * Get purchase data.
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
			'currency'     => $purchase_data['currency'],
			'value'        => $purchase_data['total'],
			'contents'     => array(),
			'content_type' => 'product',
		);

		if ( ! empty( $purchase_data['coupon'] ) ) {
			$data['query'] = $purchase_data['coupon'];
		}

		foreach ( $purchase_data['products'] as $i => $product ) {
			$data['contents'][] = array(
				'content_id'       => $product['id'],
				'quantity'         => $product['quantity'],
				'content_name'     => $product['name'],
				'content_category' => $this->get_product_category( $product ),
				'price'            => $product['price'],
			);
		}

		return $data;

	}

	/**
	 * Add the TikTok pixel snippet to the array of snippets passed to the auto-insert function.
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
				'title'    => 'TikTok Global Tag',
				'code'     => $this->get_tiktok_tag_code( $pixel_id ),
				'location' => 'site_wide_header',
			)
		);

		return array( $snippet );
	}

	/**
	 * Get the Pinterest Pixel code with the pixel id set.
	 *
	 * @param string $pixel_id The pixel id to use, defaults to the one saved in the settings.
	 *
	 * @return string
	 */
	public function get_tiktok_tag_code( $pixel_id = '' ) {
		if ( empty( $pixel_id ) ) {
			// If not set, default to the one in the settings.
			$pixel_id = $this->get_pixel_id();
		}

		if ( empty( $pixel_id ) ) {
			return '';
		}

		$code = <<<CODE
<!-- WPCode TikTok Pixel -->
<script>
	!function (w, d, t) {
		w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++
		)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};

		ttq.load('%1\$s');
		ttq.page();
	}(window, document, 'ttq');
</script>
CODE;

		return sprintf(
			$code,
			$pixel_id
		);
	}

	/**
	 * Get the code for the view content event.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider (WooCommerce, EDD, etc).
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function view_content( $provider ) {
		$tag_id = $this->get_pixel_id();

		// Let's check that we have a pixel id.
		if ( empty( $tag_id ) ) {
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
			"<script>ttq.track('%1\$s', %2\$s);</script>",
			$this->get_event_name( 'view_content' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'TikTok Product Page Visit Event',
				'location' => $provider->get_location_key( 'view_content' ),
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * Get data for the view content event formatted for this pixel.
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
			'currency'         => $product_data['currency'],
			'content_type'     => 'product',
			'content_id'       => $product_data['id'],
			'content_category' => $this->get_product_category( $product_data ),
			'price'            => $product_data['price'],
			'content_name'     => $product_data['name'],
			'value'            => $product_data['price'],
		);
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
ttq.track('{$this->get_event_name( 'add_to_cart' )}', {
	content_type: 'product',
	currency: '%CURRENCY%',
	content_id: id,
	quantity: quantity,
} );
		";
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
			'event'      => $this->get_event_name( 'add_to_cart' ),
			'timestamp'  => date( 'c', time() ),
			'context'    => array(
				'page'       => array(
					'url' => $source_url,
				),
				'ad'         => array(
					'callback' => $this->get_ttclid(),
				),
				'ip'         => $data['user_ip'],
				'user_agent' => $data['user_agent'],
			),
			'properties' => array(
				'currency' => $data['currency'],
				'value'    => $data['price'],
				'contents' => array(
					array(
						'price'            => $data['price'],
						'quantity'         => $data['quantity'],
						'content_type'     => 'product',
						'content_id'       => (string) $data['product_id'],
						'content_category' => isset( $data['categories'][0] ) ? $data['categories'][0] : '',
						'content_name'     => $data['name'],
					),
				),
			),
			'event_id'   => $this->get_event_id(),
		);

		$event_data['context']['user'] = $this->get_user_data( $data );

		$this->send_server_event( $event_data );
	}

	/**
	 * @return string
	 */
	public function get_event_id() {
		return uniqid();
	}

	/**
	 * Get TikTok click id.
	 *
	 * @return string
	 */
	public function get_ttclid() {
		if ( isset( $_COOKIE['ttclid'] ) ) {
			return sanitize_text_field( $_COOKIE['ttclid'] );
		}

		return '';
	}

	/**
	 * Get TikTok _ttp.
	 *
	 * @return string
	 */
	public function get_ttp() {
		if ( isset( $_COOKIE['_ttp'] ) ) {
			return sanitize_text_field( $_COOKIE['_ttp'] );
		}

		return '';
	}

	/**
	 * Get user data specific to TikTok api.
	 *
	 * @param array $data Array of data from the provider.
	 *
	 * @return array
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
			'external_id'  => $this->hash( $data['user_id'] ),
			'email'        => $this->hash( $data['email'] ),
			'phone_number' => $this->hash( $data['phone'] ),
			'ttp'          => $this->get_ttp(),
		);

		foreach ( $user_data as $key => $value ) {
			if ( ! is_array( $value ) && '' === (string) $value ) {
				unset( $user_data[ $key ] );
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
	public function server_begin_checkout( $data, $provider ) {

		$event_data = array(
			'event'      => $this->get_event_name( 'begin_checkout' ),
			'timestamp'  => date_i18n( 'c' ),
			'context'    => array(
				'page'       => array(
					'url' => get_permalink(), // If we're on the checkout page this should work.
				),
				'ad'         => array(
					'callback' => $this->get_ttclid(),
				),
				'ip'         => $data['user_ip'],
				'user_agent' => $data['user_agent'],
			),
			'properties' => array(
				'currency' => $data['currency'],
				'value'    => $data['total'],
				'contents' => array(),
			),
			'event_id'   => $this->get_event_id(),
		);

		foreach ( $data['products'] as $product ) {
			$event_data['properties']['contents'][] = array(
				'price'            => $product['price'],
				'quantity'         => $product['quantity'],
				'content_type'     => 'product',
				'content_id'       => (string) $product['id'],
				'content_category' => isset( $product['categories'][0] ) ? $product['categories'][0] : '',
				'content_name'     => $product['name'],
			);
		}

		$event_data['context']['user'] = $this->get_user_data( $data );

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
		$event_sent = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_sent_tiktok_purchase' );

		// If the event for this specific order and pixel was already sent, bail.
		// Even though we use a unique event id, events sent more than 24hrs apart will not be deduplicated by the API
		// so we prevent sending the same event twice by adding a provider-specific order meta item.
		if ( $event_sent ) {
			return;
		}

		$event_data = array(
			'event'      => $this->get_event_name( 'purchase' ),
			'timestamp'  => date_i18n( 'c' ),
			'event_id'   => 'wpc_checkout_' . $data['order_id'], // This will be unique per order.
			'context'    => array(
				'page'       => array(
					'url' => $provider->get_checkout_url(),
				),
				'ad'         => array(
					'callback' => $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_ttclid' ),
				),
				'ip'         => $data['user_ip'],
				'user_agent' => $data['user_agent'],
			),
			'properties' => array(
				'currency' => $data['currency'],
				'value'    => $data['total'],
				'contents' => array(),
			),
		);

		foreach ( $data['products'] as $product ) {
			$event_data['properties']['contents'][] = array(
				'price'            => (string) $product['price'],
				'quantity'         => $product['quantity'],
				'content_type'     => 'product',
				'content_id'       => (string) $product['id'],
				'content_category' => isset( $product['categories'][0] ) ? $product['categories'][0] : '',
				'content_name'     => $product['name'],
			);
		}

		$event_data['user_data']        = $this->get_user_data( $data );
		$event_data['user_data']['ttp'] = $provider->get_order_meta( $data['order_id'], '_wpcode_pixel_ttp' );

		$this->send_server_event( $event_data );

		// Mark event as sent for this pixel to avoid duplicated events.
		$provider->store_extra_data(
			$data['order_id'],
			array(
				'_wpcode_pixel_sent_tiktok_purchase' => time(),
			)
		);
	}

	/**
	 * Look for tiktok-specific cookies and store them with the order for proper attribution when order is marked
	 * as complete.
	 *
	 * @param int $order_id The order id.
	 *
	 * @return array
	 */
	public function get_extra_order_data( $order_id ) {
		$extra_data = array();

		$ttclid = $this->get_ttclid();
		if ( ! empty( $ttclid ) ) {
			$extra_data['_wpcode_pixel_ttclid'] = $ttclid;
		}
		$ttp = $this->get_ttp();
		if ( ! empty( $ttp ) ) {
			$extra_data['_wpcode_pixel_ttp'] = $ttp;
		}

		return $extra_data;
	}
}

new WPCode_Pixel_Auto_Insert_TikTok();
