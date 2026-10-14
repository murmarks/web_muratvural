<?php

/**
 * Add the pixel code for Google Analytics & Ads in the auto-insert locations.
 */
class WPCode_Pixel_Auto_Insert_Google extends WPCode_Pixel_Auto_Insert_Type {

	/**
	 * The type of pixel.
	 *
	 * @var string
	 */
	public $type = 'google';

	/**
	 * The name of the auto-insert type. This is the label that is displayed in the admin.
	 *
	 * @var string
	 */
	public $name = 'Google';

	/**
	 * The Google Analytics id.
	 *
	 * @var string
	 */
	public $analytics_id;

	/**
	 * The Google Ads id.
	 *
	 * @var string
	 */
	public $ads_id;

	/**
	 * The Google Ads label.
	 *
	 * @var string
	 */
	public $ads_label;

	/**
	 * @var string
	 */
	public $events_option_name = 'google_pixel_events';

	/**
	 * Event names.
	 *
	 * @var string[]
	 */
	public $event_names = array(
		'view_item'      => 'view_item',
		'add_to_cart'    => 'add_to_cart',
		'begin_checkout' => 'begin_checkout',
		'purchase'       => 'purchase',
		'conversion'     => 'conversion',
	);

	/**
	 * @return array|WPCode_Snippet[]
	 */
	public function get_global_header_snippets() {
		$analytics_id = $this->get_analytics_id();
		$ads_id       = $this->get_ads_id();

		// If no id is set there's no point in adding the snippet.
		if ( empty( $analytics_id ) && empty( $ads_id ) ) {
			return array();
		}

		$snippet = $this->get_basic_snippet(
			array(
				'name'     => 'Gtag.js',
				'code'     => $this->get_gtag_snippet_code(),
				'location' => 'site_wide_header',
			)
		);

		return array( $snippet );
	}

	/**
	 * Get the Google Analytics id in a single place.
	 *
	 * @return string
	 */
	public function get_analytics_id() {
		if ( ! isset( $this->analytics_id ) ) {
			$this->analytics_id = wpcode()->settings->get_option( 'google_analytics_id', '' );
		}

		return $this->analytics_id;
	}

	/**
	 * Get the Google Ads id in a single place.
	 *
	 * @return string
	 */
	public function get_ads_id() {
		if ( ! isset( $this->ads_id ) ) {
			$this->ads_id = wpcode()->settings->get_option( 'google_ads_id', '' );
		}

		return $this->ads_id;
	}

	/**
	 * Get a single gtag snippet for both Analytics and Ads.
	 *
	 * @return string
	 */
	public function get_gtag_snippet_code() {
		$analytics_id = $this->get_analytics_id();
		$ads_id       = $this->get_ads_id();
		$url_id       = empty( $analytics_id ) ? $ads_id : $analytics_id;

		$analytics_config = wp_json_encode(
			$this->get_analytics_config()
		);

		$code = <<<CODE
\n<!-- gtag.js added by WPCode -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$url_id}"></script>\n
CODE;

		$code .= <<<CODE
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());\n
CODE;

		if ( ! empty( $analytics_id ) ) {
			$code .= <<<CODE
	gtag('config', '{$analytics_id}', {$analytics_config});\n
CODE;
		}
		if ( ! empty( $ads_id ) ) {
			$code .= <<<CODE
	gtag('config', '{$ads_id}');\n
CODE;
		}

		$code .= <<<CODE
</script>\n
<!-- /gtag.js added by WPCode -->\n
CODE;

		return $code;

	}

	/**
	 * Get the basic config option for configuring the GTAG object.
	 *
	 * @return array
	 */
	public function get_analytics_config() {
		$config = array(
			'send_page_view' => true,
		);

		// If the pageview event is enabled we don't need to add anything as it will send the pageview event automatically.
		if ( ! $this->has_event( 'page_view' ) ) {
			$config['send_page_view'] = false;
		}

		return $config;
	}

	/**
	 * Get cart event code for gtag.
	 *
	 * @return string
	 */
	public function get_cart_event_code() {
		if ( empty( $this->get_analytics_id() ) || ! $this->has_event( 'add_to_cart' ) ) {
			return '';
		}

		$send_to = wp_json_encode( $this->get_send_to() );

		return <<<CODE
gtag('event', '{$this->get_event_name( 'add_to_cart' )}', {
	currency: '%CURRENCY%',
	send_to: $send_to,
	'items': [
		{
			id,
			quantity
		}
	]
});
CODE;

	}

	/**
	 * Get the send_to parameter based on the settings.
	 *
	 * @return array
	 */
	public function get_send_to() {
		$send_to = array();
		if ( ! empty( $this->get_ads_id() ) ) {
			$send_to[] = $this->get_ads_id();
		}
		if ( ! empty( $this->get_analytics_id() ) ) {
			$send_to[] = $this->get_analytics_id();
		}

		return $send_to;
	}

	/**
	 * Get the code for the initiate checkout event for WooCommerce checkout.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function begin_checkout( $provider ) {

		// Let's check that we have a pixel id.
		if ( empty( $this->get_analytics_id() ) && empty( $this->get_ads_id() ) ) {
			return array();
		}

		if ( ! $this->has_event( 'begin_checkout' ) ) {
			return array();
		}

		$data = $this->get_checkout_data( $provider );

		if ( empty( $data ) ) {
			return array();
		}

		$data['send_to'] = $this->get_send_to();
		// Gtag begin_checkout event code.
		$code = sprintf(
			"<script>gtag('event', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'begin_checkout' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'Google Pixel begin_checkout Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'begin_checkout' ),
			)
		);

		return array( $snippet );
	}

	/**
	 * Get checkout data specific to WooCommerce for the gtag call.
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
			'value'    => $checkout_data['total'],
			'items'    => array(),
		);

		if ( ! empty( $checkout_data['coupon'] ) ) {
			$data['coupon'] = $checkout_data['coupon'];
		}

		foreach ( $checkout_data['products'] as $product ) {
			$item_data       = array(
				'item_id'   => $product['id'],
				'item_name' => $product['name'],
				'discount'  => $product['discount'],
				'quantity'  => $product['quantity'],
				'price'     => $product['price'],
			);
			$item_data       = array_merge( $item_data, $this->get_product_categories_formatted( $product['categories'] ) );
			$data['items'][] = $item_data;
		}

		return $data;

	}

	/**
	 * Get a WC product categories for the gtag call.
	 *
	 * @param array $product_categories Array of product categories names.
	 *
	 * @return array to be merged with other item properties.
	 */
	public function get_product_categories_formatted( $product_categories ) {

		$item_data = array();

		if ( ! empty( $product_categories ) ) {
			$key   = 'item_category';
			$count = 2;
			foreach ( $product_categories as $category_name ) {
				if ( $count > 5 ) {
					break;
				}

				$item_data[ $key ] = $category_name;
				$key               = 'item_category' . $count;
				$count ++;
			}
		}

		return $item_data;
	}

	/**
	 * Get the code for the purchase event for WooCommerce checkout.
	 *
	 * @param $provider WPCode_Pixel_Provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function purchase( $provider ) {
		$purchase   = $this->get_purchase_event( $provider );
		$conversion = $this->get_wc_conversion_event( $provider );

		return array_merge(
			$purchase,
			$conversion
		);
	}

	/**
	 * Get the code for tracking a purchase event.
	 *
	 * @param $provider WPCode_Pixel_Provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function get_purchase_event( $provider ) {
		// Let's check that we have a pixel id.
		if ( empty( $this->get_ads_id() ) && empty( $this->get_analytics_id() ) ) {
			return array();
		}

		if ( ! $this->has_event( 'purchase' ) ) {
			return array();
		}

		$data = $this->get_purchase_data( $provider );

		if ( empty( $data ) ) {
			return array();
		}

		$data['send_to'] = $this->get_send_to();

		$code = sprintf(
			"<script>gtag('event', '%1\$s', %2\$s )</script>",
			$this->get_event_name( 'purchase' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'gtag Purchase Event',
				'code'     => $code,
				'location' => $provider->get_location_key( 'purchase' ),
			)
		);

		return array( $snippet );
	}

	/**
	 * Get purchase data specific to the gtag call.
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
			'transaction_id' => $purchase_data['order_id'],
			'value'          => $purchase_data['total'],
			'tax'            => $purchase_data['tax'],
			'shipping'       => $purchase_data['shipping'],
			'currency'       => $purchase_data['currency'],
			'items'          => array(),
		);

		if ( ! empty( $purchase_data['coupon'] ) ) {
			$data['coupon'] = $purchase_data['coupon'];
		}

		foreach ( $purchase_data['products'] as $i => $product ) {
			$item_data = array(
				'item_id'   => $product['id'],
				'item_name' => $product['name'],
				'quantity'  => $product['quantity'],
				'price'     => $product['price'],
				'index'     => $i,
				'discount'  => $product['discount'],
			);

			$item_data       = array_merge( $item_data, $this->get_product_categories_formatted( $product['categories'] ) );
			$data['items'][] = $item_data;
		}

		return $data;
	}

	/**
	 * Get the code for tracking a GAds conversion event.
	 *
	 * @param $provider WPCode_Pixel_Provider The data provider.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function get_wc_conversion_event( $provider ) {
		// Let's check that we have all the data needed.
		if ( empty( $this->get_ads_id() ) || empty( $this->get_ads_label() ) ) {
			return array();
		}

		if ( ! $this->has_event( 'conversion' ) ) {
			return array();
		}

		$conversion_data = $this->get_purchase_data( $provider );
		if ( empty( $conversion_data ) ) {
			return array();
		}

		$conversion_data['send_to'] = $this->get_ads_id() . '/' . $this->get_ads_label();

		$code = sprintf(
			"<script>gtag('event', '%1\$s', %2\$s )</script>",
			$this->get_event_name( 'conversion' ),
			wp_json_encode( $conversion_data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'gtag ads conversion Event',
				'location' => $provider->get_location_key( 'purchase' ),
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * Get the Google Ads label in a single place.
	 *
	 * @return string
	 */
	public function get_ads_label() {
		if ( ! isset( $this->ads_label ) ) {
			$this->ads_label = wpcode()->settings->get_option( 'google_ads_label', '' );
		}

		return $this->ads_label;
	}

	/**
	 * Get the code for the view content event.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider (WooCommerce, EDD, etc).
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function view_content( $provider ) {

		// Let's check that we have a pixel id.
		if ( empty( $this->get_ads_id() ) && empty( $this->get_analytics_id() ) ) {
			return array();
		}

		if ( ! $this->has_event( 'view_item' ) ) {
			return array();
		}

		$data = $this->get_product_data( $provider );
		if ( empty( $data ) ) {
			return array();
		}

		$data['send_to'] = $this->get_send_to();

		$code = sprintf(
			"<script>gtag('event', '%1\$s', %2\$s);</script>",
			$this->get_event_name( 'view_item' ),
			wp_json_encode( $data )
		);

		$snippet = $this->get_basic_snippet(
			array(
				'title'    => 'gtag view_item event',
				'location' => $provider->get_location_key( 'view_item' ),
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
			'currency' => $product_data['currency'],
			'value'    => $product_data['price'],
			'items'    => array(
				array_merge(
					array(
						'item_id'   => $product_data['id'],
						'item_name' => $product_data['name'],
						'quantity'  => 1,
						'price'     => $product_data['price'],
					),
					$this->get_product_categories_formatted( $product_data['categories'] )
				),
			),
		);
	}
}

new WPCode_Pixel_Auto_Insert_Google();
