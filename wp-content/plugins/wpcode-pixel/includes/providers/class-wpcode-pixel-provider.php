<?php
/**
 * Abstract class for pixel providers.
 *
 * @package WPCode\Pixel
 */

/**
 * Abstract class for pixel providers.
 */
abstract class WPCode_Pixel_Provider {

	/**
	 * Name of provider
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The provider slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Array of unsupported events. By default, all providers support all events.
	 *
	 * @var string[]
	 */
	public $unsupported_events = array();

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
	public $frontend_locations = array();

	/**
	 * Similar to the above only used in a single place for all pixel types.
	 * Keys are provider-specific location names, values are the functions
	 * in this provider that should be run in that location which will load
	 * pixel-specific event code through the main auto-insert class.
	 *
	 * @var string[]
	 */
	public $single_frontend_locations = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Filter to prevent a provider from being loaded entirely.
		 *
		 * @param bool   $prevent_loading Should we prevent the provider from being loaded.
		 * @param string $provider_name The name of the provider.
		 */
		if ( ! $this->should_load() || apply_filters( 'wpcode_pixel_prevent_loading_provider', false, $this->get_name() ) ) {
			return;
		}
		wpcode_pixel()->auto_insert->register_provider( $this );

		$this->add_server_hooks();
	}

	/**
	 * Get the event for a frontend location.
	 *
	 * @param string $location The frontend location.
	 *
	 * @return false|string
	 */
	public function get_event_for_location( $location ) {
		if ( array_key_exists( $location, $this->frontend_locations ) ) {
			return $this->frontend_locations[ $location ];
		}

		return false;
	}

	/**
	 * Should this provider be loaded?
	 *
	 * @return bool
	 */
	public function should_load() {
		return false;
	}

	/**
	 * Get the name of the data provider.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the product data for a view content event.
	 *
	 * @return array
	 */
	public function get_product_data() {
		return array();
	}

	/**
	 * Get the product data but give others a chance to modify the data.
	 *
	 * @return array
	 */
	public function get_product_data_filtered() {
		return apply_filters( "wpcode_pixel_product_data_{$this->slug}", $this->get_product_data() );
	}

	/**
	 * Get the checkout data but give others a chance to modify the data.
	 *
	 * @return array
	 */
	public function get_checkout_data_filtered() {
		return apply_filters( "wpcode_pixel_checkout_data_{$this->slug}", $this->get_checkout_data() );
	}

	/**
	 * Get the purchase data but give others a chance to modify the data.
	 *
	 * @return array
	 */
	public function get_purchase_data_filtered() {
		return apply_filters( "wpcode_pixel_purchase_data_{$this->slug}", $this->get_purchase_data() );
	}

	/**
	 * Get the checkout data for begin checkout event.
	 *
	 * @return array
	 */
	public function get_checkout_data() {
		return array();
	}

	/**
	 * Get the purchase data after the order is placed specific to the provider.
	 *
	 * @return array
	 */
	public function get_purchase_data() {
		return array();
	}

	/**
	 * Create the snippet to listen for frontend add to cart events.
	 *
	 * @return array
	 */
	public function cart_event_snippet() {
		return array();
	}

	/**
	 * Snippet to run on the single product page for add to cart.
	 *
	 * @return array
	 */
	public function single_add_to_cart() {
		return array();
	}

	/**
	 * Get the provider-specific currency.
	 *
	 * @return string
	 */
	abstract public function get_currency();

	/**
	 * Replace the %CURRENCY% placeholder with the provider-specific currency code.
	 *
	 * @param array $code_array An array of code with the placeholder in it.
	 *
	 * @return array
	 */
	public function replace_code_currency( $code_array ) {
		foreach ( $code_array as $key => $code ) {
			$code_array[ $key ] = str_replace( '%CURRENCY%', $this->get_currency(), $code );
		}

		return $code_array;
	}

	/**
	 * Load snippets that should run in the same place for all pixels, like add to cart events.
	 *
	 * @param string $location The location to load for.
	 *
	 * @return array
	 */
	public function single_location_snippet( $location ) {
		if ( ! empty( $this->single_frontend_locations[ $location ] ) && method_exists( $this, $this->single_frontend_locations[ $location ] ) ) {
			return call_user_func( array( $this, $this->single_frontend_locations[ $location ] ) );
		}

		return array();
	}

	/**
	 * Add vendor-specific hooks for server-side API requests.
	 *
	 * @return void
	 */
	public function add_server_hooks() {

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
			update_post_meta( $order_id, $key, $value );
		}
		update_post_meta( $order_id, '_wpcode_pixel_user_agent', $this->get_user_agent(), true );
	}

	/**
	 * Get the user agent.
	 *
	 * @return string
	 */
	public function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : ''; // @codingStandardsIgnoreLine
	}

	/**
	 * Get the current user ip.
	 *
	 * @return string
	 */
	public function get_user_ip() {
		if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && function_exists( 'rest_is_ip_address' ) ) {
			return (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return '';
	}

	/**
	 * Get order meta data based on the provider.
	 *
	 * @param int    $order_id The order id.
	 * @param string $key The key for the meta.
	 *
	 * @return mixed
	 */
	public function get_order_meta( $order_id, $key ) {
		return get_post_meta( $order_id, $key, true );
	}

	/**
	 * Get a default value for the checkout URL.
	 *
	 * @return string|void
	 */
	public function get_checkout_url() {
		return home_url();
	}

	/**
	 * This hook is used to give pixels a chance to associate user cookies with the order
	 * for proper attribution of the purchase event that gets triggered separately.
	 *
	 * @return void
	 */
	public function server_order_saved( $order_id, $data = array() ) {
		wpcode_pixel()->auto_insert->store_extra_order_data( $this, $order_id );
	}

	/**
	 * Get the location key for the event.
	 *
	 * @param string $event The event name.
	 *
	 * @return string
	 */
	public function get_location_key( $event ) {
		if ( ! in_array( $event, $this->frontend_locations, true ) ) {
			return '';
		}

		// If it's in the array, the location is the key.
		return array_search( $event, $this->frontend_locations, true );
	}
}
