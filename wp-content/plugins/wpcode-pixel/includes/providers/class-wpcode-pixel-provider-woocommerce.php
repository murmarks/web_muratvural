<?php

class WPCode_Pixel_Provider_WooCommerce extends WPCode_Pixel_Provider {

	/**
	 * Name of provider.
	 *
	 * @var string
	 */
	public $name = 'WooCommerce';

	/**
	 * The provider slug.
	 *
	 * @var string
	 */
	public $slug = 'woocommerce';

	/**
	 * Should this provider be loaded?
	 *
	 * @return bool
	 */
	public function should_load() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Array of frontend locations used for tracking specific to this
	 * provider.
	 * Keys are provider-specific location names, values are the
	 * functions of each pixel type that should be run in that location.
	 *
	 * @see WPCode_Pixel_Auto_Insert_Type::get_snippets()
	 * @see WPCode_Pixel_Auto_Insert::load_pixel_snippets()
	 *
	 * @var string[]
	 */
	public $frontend_locations = array(
		'wc_before_single_product'         => 'view_content',
		'woocommerce_before_checkout_form' => 'begin_checkout',
		'woocommerce_before_thankyou'      => 'purchase',
	);

	public $single_frontend_locations = array(
		'site_wide_footer'        => 'cart_event_snippet',
		'wc_after_single_product' => 'single_add_to_cart',
	);

	/**
	 * Server-side hooks for this provider.
	 *
	 * @return void
	 */
	public function add_server_hooks() {

		// When a product is added to the cart, more reliable way than frontend clicks.
		add_action( 'woocommerce_add_to_cart', array( $this, 'server_event_add_to_cart' ), 10, 6 );

		// When the checkout form is loaded, send a server event if the Token is set.
		add_action( 'woocommerce_after_checkout_form', array( $this, 'server_event_begin_checkout' ) );

		// Order completed event. Usually processing means payment went through but for some gateways "completed" skips processing.
		add_action( 'woocommerce_order_status_processing', array( $this, 'server_event_purchase' ), 10, 2 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'server_event_purchase' ), 10, 2 );

		// When the order is placed, attempt to save pixel-specific data to assign to the order when it later gets marked as completed.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'server_order_saved' ), 10, 2 );
	}

	/**
	 * Get product data specific to WooCommerce.
	 *
	 * @return array
	 */
	public function get_product_data() {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$product = wc_get_product();
		if ( ! $product ) {
			return array();
		}

		return array(
			'name'       => $product->get_name(),
			'id'         => $product->get_id(),
			'price'      => $product->get_price(),
			'currency'   => get_woocommerce_currency(),
			'categories' => $this->get_product_categories( $product ),
		);
	}

	/**
	 * Get checkout data specific to WooCommerce.
	 *
	 * @return array
	 */
	public function get_checkout_data() {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$data = array(
			'num_items' => WC()->cart->get_cart_contents_count(),
			'currency'  => get_woocommerce_currency(),
			'total'     => WC()->cart->get_cart_contents_total(),
			'products'  => array(),
		);

		$coupon = WC()->cart->get_applied_coupons();
		if ( ! empty( $coupon ) && ! empty( $coupon[0] ) ) {
			$data['coupon'] = $coupon[0];
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			/**
			 * @var WC_Product $product
			 */

			$data['products'][] = array(
				'id'         => $product->get_id(),
				'quantity'   => $cart_item['quantity'],
				'name'       => $product->get_name(),
				'discount'   => $cart_item['line_subtotal'] - $cart_item['line_total'],
				'price'      => $product->get_price(),
				'categories' => $this->get_product_categories( $product ),
			);
		}

		return $data;
	}

	/**
	 * Get the product categories.
	 *
	 * @param WC_Product $product The product to grab the categories from.
	 *
	 * @return array
	 */
	public function get_product_categories( $product ) {
		$product_categories = $product->get_category_ids();

		$categories = array();
		foreach ( $product_categories as $category_id ) {
			$category = get_term( $category_id );
			if ( ! empty( $category ) && ! is_wp_error( $category ) ) {
				$categories[] = $category->name;
			}
		}

		return $categories;
	}

	/**
	 * Get purchase data specific to WooCommerce.
	 *
	 * @return array
	 */
	public function get_purchase_data() {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$order_id = absint( get_query_var( 'order-received' ) );
		$order    = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return array();
		}

		$data = array(
			'num_items' => $order->get_item_count(),
			'currency'  => $order->get_currency(),
			'total'     => $order->get_total(),
			'tax'       => $order->get_tax_totals(),
			'shipping'  => $order->get_shipping_total(),
			'products'  => array(),
			'order_id'  => $order->get_id(),
		);

		$coupon = WC()->cart->get_applied_coupons();
		if ( ! empty( $coupon ) && ! empty( $coupon[0] ) ) {
			$data['coupon'] = $coupon[0];
		}

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			$data['products'][] = array(
				'id'         => $product->get_id(),
				'quantity'   => $item->get_quantity(),
				'name'       => $product->get_name(),
				'discount'   => $item->get_subtotal() - $item->get_total(),
				'price'      => $product->get_price(),
				'categories' => $this->get_product_categories( $product ),
			);
		}

		return $data;

	}

	/**
	 * Get the WOO currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return get_woocommerce_currency();
	}

	/**
	 * Create the snippet to listen for WooCommerce add to cart events.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function cart_event_snippet() {

		$cart_events = wpcode_pixel()->auto_insert->cart_events;

		if ( empty( $cart_events ) ) {
			return array();
		}

		$cart_events = $this->replace_code_currency( $cart_events );

		$code = sprintf(
			"
<script>
	var add_to_cart_buttons = document.querySelectorAll( '.add_to_cart_button' );
	for ( var i = 0; i < add_to_cart_buttons.length; i++ ) {
		add_to_cart_buttons[i].addEventListener( 'click', function() {
			var id = this.getAttribute( 'data-product_id' );
			var quantity = 1;
			%s
		} );
	}
</script>
		",
			implode( PHP_EOL, $cart_events )
		);

		$snippet = wpcode_pixel()->auto_insert->get_basic_snippet(
			array(
				'id'       => 'pixel_facebook',
				'title'    => 'WPCode Pixel Add To Cart Event',
				'location' => 'site_wide_footer',
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * Create the snippet to listen for WooCommerce add to cart events.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function single_add_to_cart() {

		$cart_events = wpcode_pixel()->auto_insert->cart_events;

		if ( empty( $cart_events ) ) {
			return array();
		}

		$cart_events = $this->replace_code_currency( $cart_events );

		$code = sprintf(
			"
<script>
	document.querySelector( '.cart' ).addEventListener( 'submit', function( e ) {
		var quantity = this.querySelector( 'input[name=\"quantity\"]' ).value;
		var id = this.querySelector( '[name=\"add-to-cart\"]' ).value;
			%s
		} );
</script>
		",
			implode( PHP_EOL, $cart_events )
		);

		$snippet = wpcode_pixel()->auto_insert->get_basic_snippet(
			array(
				'id'       => 'pixel_facebook',
				'title'    => 'WPCode Pixel Single Page Add To Cart Event',
				'location' => 'wc_after_single_product',
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * @param int      $order_id
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public function server_event_purchase( $order_id, $order ) {

		$skip_renewals = apply_filters( 'wpcode_pixel_skip_renewals', true );
		if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id, 'renewal' ) && $skip_renewals ) {
			return;
		}

		// Build purchase event data from order.
		$data = array(
			'order_id'   => $order_id,
			'num_items'  => $order->get_item_count(),
			'currency'   => $order->get_currency(),
			'total'      => $order->get_total(),
			'products'   => array(),
			'user_id'    => $order->get_user_id(),
			'user_ip'    => $order->get_customer_ip_address(),
			'phone'      => $order->get_billing_phone(),
			'email'      => $order->get_billing_email(),
			'zip'        => $order->get_billing_postcode(),
			'city'       => $order->get_billing_city(),
			'state'      => $order->get_billing_state(),
			'country'    => $order->get_billing_country(),
			'user_agent' => $order->get_customer_user_agent(),
			'first_name' => $order->get_billing_first_name(),
			'last_name'  => $order->get_billing_last_name(),
		);

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			$data['products'][] = array(
				'id'         => $product->get_id(),
				'quantity'   => $item->get_quantity(),
				'name'       => $product->get_name(),
				'discount'   => $item->get_subtotal() - $item->get_total(),
				'price'      => $product->get_price(),
				'categories' => $this->get_product_categories( $product ),
			);
		}


		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'purchase', $data, $this );
	}

	/**
	 * Store extra order data specific from each pixel.
	 *
	 * @param int   $order_id The order id to add meta to.
	 * @param array $data The array of key > value pairs to store.
	 *
	 * @return void
	 */
	public function store_extra_data( $order_id, $data ) {
		$order = wc_get_order( $order_id );
		foreach ( $data as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}
		$order->save();
	}

	/**
	 * Get order meta specific to WooCommerce.
	 * Improved compatibility by not calling get_post_meta directly.
	 *
	 * @param int    $order_id The order to get meta for.
	 * @param string $key The key to get meta by.
	 *
	 * @return array|mixed|string
	 */
	public function get_order_meta( $order_id, $key ) {
		$order = wc_get_order( $order_id );

		return $order->get_meta( $key );
	}

	/**
	 * @param string  $cart_id ID of the item in the cart.
	 * @param integer $product_id ID of the product added to the cart.
	 * @param integer $request_quantity Quantity of the item added to the cart.
	 * @param integer $variation_id Variation ID of the product added to the cart.
	 * @param array   $variation Array of variation data.
	 * @param array   $cart_item_data Array of other cart item data.
	 */
	public function server_event_add_to_cart( $cart_id = '', $product_id = 0, $request_quantity = 0, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {

		if ( ! empty( $variation_id ) ) {
			$product = wc_get_product( $variation_id );

			if ( empty( $product_id ) ) {
				$product_id = $variation_id;
			}
		} else {
			$product = wc_get_product( $product_id );
		}

		$data = array(
			'product_id' => $product_id,
			'quantity'   => $request_quantity,
			'name'       => $product->get_name(),
			'price'      => $product->get_price(),
			'categories' => $this->get_product_categories( $product ),
			'currency'   => $this->get_currency(),
			'user_ip'    => WC_Geolocation::get_ip_address(),
			'user_agent' => wc_get_user_agent(),
		);

		$data = array_merge( $data, $this->get_server_user_data() );

		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'add_to_cart', $data, $this );
	}

	/**
	 * Send a server event when the checkout page is viewed.
	 *
	 * @param WC_Checkout $checkout The checkout object.
	 */
	public function server_event_begin_checkout( $checkout ) {


		$data = $this->get_checkout_data();

		$user_data = array(
			'user_ip'    => WC_Geolocation::get_ip_address(),
			'user_agent' => wc_get_user_agent(),
		);

		$data = array_merge( $data, $user_data );
		$data = array_merge( $data, $this->get_server_user_data() );

		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'begin_checkout', $data, $this );
	}

	/**
	 * Get the user data for a logged-in user, if available.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_server_user_data() {
		$data = array();

		if ( is_user_logged_in() ) {
			$customer           = new WC_Customer( get_current_user_id() );
			$data['user_id']    = get_current_user_id();
			$data['email']      = $customer->get_billing_email();
			$data['city']       = $customer->get_billing_city();
			$data['state']      = $customer->get_billing_state();
			$data['country']    = $customer->get_billing_country();
			$data['zip']        = $customer->get_billing_postcode();
			$data['first_name'] = $customer->get_billing_first_name();
			$data['last_name']  = $customer->get_billing_last_name();
			$data['phone']      = $customer->get_billing_phone();
		}

		return $data;
	}

	/**
	 * Get the URL of the checkout page.
	 *
	 * @return string|void
	 */
	public function get_checkout_url() {
		return wc_get_checkout_url();
	}
}

new WPCode_Pixel_Provider_WooCommerce();
