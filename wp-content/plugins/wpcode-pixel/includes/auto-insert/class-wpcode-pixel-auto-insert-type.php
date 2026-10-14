<?php

/**
 * Class used for pixel-specific auto-insert types.
 */
abstract class WPCode_Pixel_Auto_Insert_Type {

	/**
	 * The name of the auto-insert type. This is the label that is displayed in the admin.
	 *
	 * @var string
	 */
	public $name;
	/**
	 * The type of pixel.
	 *
	 * @var string
	 */
	public $type = '';
	/**
	 * The events enabled for this pixel.
	 *
	 * @var array
	 */
	public $events;

	/**
	 * @var string
	 */
	public $events_option_name;

	/**
	 * Key used to store the api token in the WPCode settings.
	 *
	 * @var string
	 */
	public $api_token_key;

	/**
	 * The pixel api token, if any.
	 *
	 * @var string
	 */
	public $api_token;

	/**
	 * @var WPCode_Pixel_Server
	 */
	public $server_handler;

	/**
	 * @var string
	 */
	public $server_handler_name;

	/**
	 * Array of event names.
	 *
	 * @var array
	 */
	public $event_names = array();

	/**
	 * Array of standard events.
	 *
	 * @var array
	 */
	public $standard_events = array();

	/**
	 * Register the type when the class is instantiated.
	 */
	public function __construct() {
		$this->register_type();
		$this->require_base_server_handler();
		$this->require_server_handler();
	}

	/**
	 * Register the type in the main plugin instance.
	 *
	 * @return void
	 */
	public function register_type() {
		wpcode_pixel()->auto_insert->register_type( $this );
	}

	/**
	 * Load the base pixel server handler.
	 *
	 * @return void
	 */
	public function require_base_server_handler() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/server/class-wpcode-pixel-server.php';
	}

	/**
	 * Require the server handler class.
	 *
	 * @return void
	 */
	public function require_server_handler() {

	}

	/**
	 * Load the snippets for a location.
	 *
	 * @param string $location The auto-insert location from the main WPCode plugin.
	 *
	 * @return array
	 */
	public function get_snippets( $location ) {
		if ( 'site_wide_header' === $location ) {
			return $this->get_global_header_snippets();
		}

		$providers = wpcode_pixel()->auto_insert->providers;
		foreach ( $providers as $provider ) {
			/**
			 * @var WPCode_Pixel_Provider $provider
			 */
			$event = $provider->get_event_for_location( $location );
			if ( $event ) {
				return $this->get_snippets_for_event( $event, $provider );
			}
		}
	}

	/**
	 * Get snippets for the global header.
	 *
	 * @return array
	 */
	public function get_global_header_snippets() {
		return array();
	}

	/**
	 * Get the snippets for a specific event.
	 *
	 * @param string $event
	 *
	 * @return array
	 */
	public function get_snippets_for_event( $event, $provider ) {
		if ( method_exists( $this, $event ) ) {
			return call_user_func( array( $this, $event ), $provider );
		}
	}

	/**
	 * Get a basic snippet data for adding to the locations used by WPCode.
	 *
	 * @param array $data Data to override the defaults.
	 *
	 * @return WPCode_Snippet
	 */
	public function get_basic_snippet( $data ) {

		$snippet     = wpcode_pixel()->auto_insert->get_basic_snippet( $data );
		$snippet->id = 'pixel_' . $this->type;

		return $snippet;
	}

	/**
	 * Cart event code specific to the pixel.
	 *
	 * @return string
	 */
	public function get_cart_event_code() {
		return '';
	}

	/**
	 * Get the first category name for a product from a provider array.
	 *
	 * @param array $product_data Product data from the provider.
	 *
	 * @return string
	 */
	public function get_product_category( $product_data ) {
		if ( empty( $product_data['categories'] ) || ! is_array( $product_data['categories'] ) ) {
			return '';
		}

		return $product_data['categories'][0];
	}

	/**
	 * Check if the event is enabled and if so attempt to execute the function for it specific to this pixel.
	 *
	 * @param string                $event_name The event name.
	 * @param array                 $data The event data from the provider.
	 * @param WPCode_Pixel_Provider $provider The provider instance.
	 *
	 * @return void
	 */
	public function handle_server_event( $event_name, $data, $provider ) {
		// If the api token is not set, bail early.
		if ( ! $this->has_api_token() ) {
			return;
		}
		// If the event is not enabled, bail early.
		if ( ! $this->has_event( $event_name ) ) {
			return;
		}
		// For clarity, server events are named server_[event_name].
		$event_name = 'server_' . $event_name;
		// If the pixel has a function for the event, call it.
		if ( method_exists( $this, $event_name ) ) {
			call_user_func( array( $this, $event_name ), $data, $provider );
		}
	}

	/**
	 * If the API token is set or not.
	 *
	 * @return bool
	 */
	public function has_api_token() {
		return ! empty( $this->get_api_token() );
	}

	/**
	 * Get the API token for this pixel type.
	 *
	 * @return string
	 */
	public function get_api_token() {
		if ( ! isset( $this->api_token ) ) {
			$this->api_token = wpcode()->settings->get_option( $this->api_token_key, '' );
		}

		return $this->api_token;
	}

	/**
	 * Is the event enabled?
	 *
	 * @param string $event_name The FB event name.
	 *
	 * @return bool
	 */
	public function has_event( $event_name = 'page_view' ) {
		$events = $this->get_events();

		return ! empty( $events[ $event_name ] );
	}

	/**
	 * Grab all the events that are enabled.
	 *
	 * @return mixed
	 */
	public function get_events() {
		if ( ! isset( $this->events ) ) {
			$this->events = wpcode()->settings->get_option( $this->events_option_name, array() );
		}

		return $this->events;
	}

	/**
	 * Use server handler instance to send a server-side event using the API, if any.
	 *
	 * @param array $data Event data specific to the event.
	 *
	 * @return void
	 */
	public function send_server_event( $data ) {
		$server_handler = $this->get_server_handler();

		if ( $server_handler ) {
			$server_handler->send_event( $data );
		}
	}

	/**
	 * @return mixed|WPCode_Pixel_Server
	 */
	public function get_server_handler() {
		if ( ! isset( $this->server_handler ) ) {
			$this->server_handler = $this->init_server_handler();
		}

		return $this->server_handler;
	}

	public function init_server_handler() {
		$handler_name = $this->server_handler_name;

		return new $handler_name();
	}

	/**
	 * Hash a value with sha256.
	 *
	 * @param string $value The value to hash,
	 *
	 * @return string
	 */
	public function hash( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return hash( 'sha256', $value );
	}

	/**
	 * @return array
	 */
	public function get_extra_order_data( $order_id ) {
		return array();
	}

	/**
	 * Get the event name to use for a specific event, by the event key.
	 * This adds a filter to allow customizing events for different steps of the funnel.
	 *
	 * @param string $event_key The event key.
	 *
	 * @return string
	 */
	public function get_event_name( $event_key = 'view_content' ) {
		$event_name = '';
		if ( isset( $this->event_names[ $event_key ] ) ) {
			$event_name = $this->event_names[ $event_key ];
		}

		return apply_filters( "wpcode_pixel_event_name_{$this->type}", $event_name, $event_key );
	}

	/**
	 * Process data from the vendor in a format specific to this pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider instance.
	 *
	 * @return array
	 */
	public function process_product_data( $provider ) {
		return array();
	}

	/**
	 * Process data from the vendor in a format specific to this pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider instance.
	 *
	 * @return array
	 */
	public function process_checkout_data( $provider ) {
		return array();
	}

	/**
	 * Process data from the vendor in a format specific to this pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider instance.
	 *
	 * @return array
	 */
	public function process_purchase_data( $provider ) {
		return array();
	}

	/**
	 * Get the data for a product from a provider and filter it after processing it for this pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider instance (woo, edd, memberpress, etc).
	 *
	 * @return array
	 */
	public function get_product_data( $provider ) {
		$product_data = $this->process_product_data( $provider );

		return apply_filters( "wpcode_pixel_product_data_{$this->type}", $product_data, $provider );
	}

	/**
	 * Get checkout data specific to the pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array
	 */
	public function get_checkout_data( $provider ) {
		$checkout_data = $this->process_checkout_data( $provider );

		return apply_filters( "wpcode_pixel_checkout_data_{$this->type}", $checkout_data, $provider );
	}

	/**
	 * Get purchase data specific to the pixel.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider.
	 *
	 * @return array
	 */
	public function get_purchase_data( $provider ) {
		$purchase_data = $this->process_purchase_data( $provider );

		return apply_filters( "wpcode_pixel_purchase_data_{$this->type}", $purchase_data, $provider );
	}

	/**
	 * Get a list of standard events for this pixel.
	 *
	 * @return array
	 */
	public function get_standard_events() {
		return $this->standard_events;
	}
}
