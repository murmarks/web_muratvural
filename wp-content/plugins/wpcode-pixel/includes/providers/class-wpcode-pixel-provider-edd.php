<?php
/**
 * Class WPCode_Pixel_Provider_EDD
 *
 * @package wpcode-pixel
 */

/**
 * Class WPCode_Pixel_Provider_EDD
 *
 * @package wpcode-pixel
 */
class WPCode_Pixel_Provider_EDD extends WPCode_Pixel_Provider {

	/**
	 * Name of provider.
	 *
	 * @var string
	 */
	public $name = 'Easy Digital Downloads';

	/**
	 * The provider slug.
	 *
	 * @var string
	 */
	public $slug = 'edd';

	/**
	 * Check if this pixel provider should load.
	 *
	 * @return bool
	 */
	public function should_load() {
		return class_exists( 'Easy_Digital_Downloads' );
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
		'edd_before_download_content'    => 'view_content',
		'edd_before_checkout_cart'       => 'begin_checkout',
		'edd_order_receipt_before_table' => 'purchase',
	);

	/**
	 * Frontend locations for single products.
	 *
	 * @var string[]
	 */
	public $single_frontend_locations = array(
		'edd_after_download_content' => 'single_add_to_cart',
	);

	/**
	 * Server-side hooks for this provider.
	 *
	 * @return void
	 */
	public function add_server_hooks() {
		// When a product is added to the cart, more reliable way than frontend clicks.
		add_action( 'edd_post_add_to_cart', array( $this, 'server_event_add_to_cart' ), 10, 3 );

		// When the checkout cart is loaded, send a server event if the Token is set.
		add_action( 'edd_before_checkout_cart', array( $this, 'server_event_begin_checkout' ) );

		// When the order is placed, attempt to save pixel-specific data to assign to the order when it later gets marked as completed.
		add_action( 'edd_insert_payment', array( $this, 'server_order_saved' ), 10, 2 );

		// Order paid event.
		add_action( 'edd_update_payment_status', array( $this, 'server_event_purchase' ), 10, 3 );

		add_action( 'edd_order_receipt_before_table', array( $this, 'execute_thank_you_page_hook' ) );
	}

	/**
	 * Get product data specific to Easy Digital Downloads.
	 *
	 * @return array
	 */
	public function get_product_data() {
		// Let's check the function to get edd download data exists.
		if ( ! function_exists( 'edd_get_download' ) ) {
			return array();
		}

		$product = edd_get_download( get_the_ID() );

		if ( ! $product ) {
			return array();
		}

		return array(
			'name'       => $product->get_name(),
			'id'         => $product->get_id(),
			'price'      => $product->get_price(),
			'currency'   => $this->get_currency(),
			'categories' => $this->get_product_categories( $product ),
		);
	}

	/**
	 * Get checkout data specific to EDD.
	 *
	 * @return array
	 */
	public function get_checkout_data() {
		if ( ! function_exists( 'edd_get_download' ) ) {
			return array();
		}

		// Get edd cart data.
		$data = array(
			'num_items' => edd_get_cart_quantity(),
			'currency'  => $this->get_currency(),
			'total'     => edd_get_cart_total(),
			'products'  => array(),
		);

		$coupon = EDD()->cart->get_discounts();
		if ( ! empty( $coupon ) && ! empty( $coupon[0] ) ) {
			$data['coupon'] = $coupon[0];
		}

		$cart_contents = edd_get_cart_content_details();
		foreach ( $cart_contents as $item ) {

			$download = edd_get_download( $item['id'] );

			$data['products'][] = array(
				'id'         => $item['id'],
				'quantity'   => $item['quantity'],
				'name'       => $item['name'],
				'discount'   => $item['discount'],
				'price'      => $item['price'],
				'categories' => $this->get_product_categories( $download ),
			);
		}

		return $data;
	}

	/**
	 * Get the product categories.
	 *
	 * @param EDD_Download $product The product to grab the categories from.
	 *
	 * @return array
	 */
	public function get_product_categories( $product ) {
		$product_categories = wp_get_post_terms( $product->get_ID(), 'download_category' );

		if ( is_wp_error( $product_categories ) ) {
			return array();
		}

		$categories = array();
		foreach ( $product_categories as $category ) {
			if ( ! empty( $category ) && ! is_wp_error( $category ) ) {
				$categories[] = $category->name;
			}
		}

		return $categories;
	}

	/**
	 * Get purchase data specific to EDD.
	 *
	 * @return array
	 */
	public function get_purchase_data() {
		if ( ! function_exists( 'edd_get_download' ) ) {
			return array();
		}

		$page_id              = absint( get_the_ID() );
		$confirmation_page_id = absint( edd_get_option( 'confirmation_page', false ) );

		if ( $page_id !== $confirmation_page_id ) {
			return array();
		}

		global $edd_receipt_args;

		if ( empty( $edd_receipt_args['id'] ) ) {
			return array();
		}

		$order = edd_get_order( $edd_receipt_args['id'] );

		if ( empty( $order ) ) {
			return array();
		}

		$data = array(
			'num_items' => count( $order->get_items() ),
			'currency'  => edd_get_payment_currency_code( $order->id ),
			'total'     => edd_get_payment_amount( $order->id ),
			'tax'       => edd_get_payment_tax( $order->id ),
			'shipping'  => 0, // There's no shipping digital downloads.
			'products'  => array(),
			'order_id'  => $order->id,
		);

		$coupon = $order->get_discounts();
		if ( ! empty( $coupon ) && ! empty( $coupon[0] ) ) {
			$data['coupon'] = $coupon[0];
		}

		foreach ( $order->get_items() as $item ) {
			/**
			 * @var \EDD\Orders\Order_Item $item
			 */
			$download = edd_get_download( $item->product_id );

			$data['products'][] = array(
				'id'         => $item->product_id,
				'quantity'   => $item->quantity,
				'name'       => $item->product_name,
				'discount'   => $item->discount,
				'price'      => $download->get_price(),
				'categories' => $this->get_product_categories( $download ),
			);
		}

		return $data;

	}

	/**
	 * Get the Edd currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return edd_get_currency();
	}

	/**
	 * Create the snippet to listen for EDD add to cart events.
	 *
	 * @return array|WPCode_Snippet[]
	 */
	public function single_add_to_cart() {

		$cart_events = wpcode_pixel()->auto_insert->cart_events;

		if ( empty( $cart_events ) ) {
			return array();
		}

		$cart_events = $this->replace_code_currency( $cart_events );

		if ( edd_is_ajax_disabled() ) {
			$code = sprintf(
				"
<script>
	document.querySelector( '.edd_download_purchase_form' ).addEventListener( 'submit', function( e ) {
		var quantity_input = this.querySelector( 'input[name=\"edd_download_quantity\"]' );
		var quantity = quantity_input ? quantity_input.value : 1;
		var id = this.querySelector( '[name=\"download_id\"]' ).value;
			%s
		} );
</script>
		",
				implode( PHP_EOL, $cart_events )
			);
		} else {
			$code = sprintf(
				"
<script>
	var edd_add_to_cart_buttons = document.querySelectorAll( '.edd-add-to-cart' );
	for ( var i = 0; i < edd_add_to_cart_buttons.length; i++ ) {
		edd_add_to_cart_buttons[i].addEventListener( 'click', function() {
			var quantity_input = this.closest( '.edd_download_purchase_form' ).querySelector( 'input[name=\"edd_download_quantity\"]' );
			var id = this.getAttribute( 'data-download-id' );
			var quantity = quantity_input ? quantity_input.value : 1;
			%s
		} );
		
		}
		</script>
		",
				implode( PHP_EOL, $cart_events )
			);
		}

		$snippet = wpcode_pixel()->auto_insert->get_basic_snippet(
			array(
				'id'       => 'pixel_facebook',
				'title'    => 'WPCode Pixel Single Page Add To Cart Event',
				'location' => 'edd_after_download_content',
				'code'     => $code,
			)
		);

		return array( $snippet );
	}

	/**
	 * Add to cart event for the server.
	 *
	 * @param $download_id
	 * @param $options
	 * @param $items
	 *
	 * @return void
	 */
	public function server_event_add_to_cart( $download_id, $options, $items ) {

		$download = new EDD_Download( $download_id );

		$quantity = isset( $options['quantity'] ) ? absint( $options['quantity'] ) : 1;

		$data = array(
			'product_id' => $download_id,
			'quantity'   => $quantity,
			'name'       => $download->get_name(),
			'price'      => $download->get_price(),
			'categories' => $this->get_product_categories( $download ),
			'currency'   => $this->get_currency(),
			'user_ip'    => edd_get_ip(),
			'user_agent' => $this->get_user_agent(),
		);

		$data = array_merge( $data, $this->get_server_user_data() );

		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'add_to_cart', $data, $this );

	}

	/**
	 * Send a server begin_checkout event for EDD checkout.
	 *
	 * @return void
	 */
	public function server_event_begin_checkout() {

		$data = $this->get_checkout_data();

		$user_data = array(
			'user_ip'    => edd_get_ip(),
			'user_agent' => $this->get_user_agent(),
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
	 */
	public function get_server_user_data() {
		$data = array();
		if ( is_user_logged_in() ) {
			$user_id            = get_current_user_id();
			$user               = wp_get_current_user();
			$address            = edd_get_customer_address( $user_id );
			$data['user_id']    = get_current_user_id();
			$data['email']      = $user->user_email;
			$data['city']       = $address['city'];
			$data['state']      = $address['state'];
			$data['country']    = $address['country'];
			$data['zip']        = $address['zip'];
			$data['first_name'] = $user->first_name;
			$data['last_name']  = $user->last_name;
		}

		return $data;
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
		foreach ( $data as $key => $value ) {
			edd_add_order_meta( $order_id, $key, $value );
		}
		edd_add_order_meta( $order_id, '_wpcode_pixel_user_agent', $this->get_user_agent(), true );
	}


	/**
	 * Get order meta specific to EDD.
	 *
	 * @param int    $order_id The order to get meta for.
	 * @param string $key The key to get meta by.
	 *
	 * @return array|mixed|string
	 */
	public function get_order_meta( $order_id, $key ) {
		return edd_get_order_meta( $order_id, $key, true );
	}

	/**
	 * @param int    $order_id
	 * @param string $old_status The old order status.
	 * @param string $new_status The new order status.
	 *
	 * @return void
	 */
	public function server_event_purchase( $order_id, $new_status, $old_status ) {

		if ( 'publish' !== $new_status && 'edd_subscription' !== $new_status && 'complete' !== $new_status ) {
			return;
		}

		$skip_renewals = apply_filters( 'wpcode_pixel_skip_renewals', true );
		if ( 'edd_subscription' === $new_status && $skip_renewals ) {
			return;
		}

		$order = new EDD_Payment( $order_id );

		$address = $order->address;

		// Build purchase event data from order.
		$data = array(
			'order_id'   => $order_id,
			'num_items'  => count( $order->cart_details ),
			'currency'   => $order->currency,
			'total'      => $order->total,
			'products'   => array(),
			'user_id'    => $order->user_id,
			'user_ip'    => $order->ip,
			'phone'      => '',
			'email'      => $order->email,
			'zip'        => $address['zip'],
			'city'       => $address['city'],
			'state'      => $address['state'],
			'country'    => $address['country'],
			'user_agent' => $this->get_order_meta( $order_id, '_wpcode_pixel_user_agent' ),
			'first_name' => $order->first_name,
			'last_name'  => $order->last_name,
		);

		foreach ( $order->cart_details as $item ) {
			$product            = new EDD_Download( $item['id'] );
			$data['products'][] = array(
				'id'         => $item['id'],
				'quantity'   => $item['quantity'],
				'name'       => $item['name'],
				'discount'   => $item['discount'],
				'price'      => $item['price'],
				'categories' => $this->get_product_categories( $product ),
			);
		}

		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'purchase', $data, $this );
	}

	/**
	 * Backwards-compatible thank you page hook triggering.
	 *
	 * @return void
	 */
	public function execute_thank_you_page_hook() {
		wpcode()->auto_insert->types['edd']->output_location( 'edd_order_receipt_before_table' );
	}
}

new WPCode_Pixel_Provider_EDD();
