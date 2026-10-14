<?php

/**
 * Extend the auto-insert functionality in the main plugin
 * with pixel-specific code.
 */
class WPCode_Pixel_Auto_Insert {

	/**
	 * Add to Cart events from different pixel vendors to be used with the WooCommerce hooks.
	 *
	 * @var array
	 */
	public $cart_events = array();

	/**
	 * The auto-insert types.
	 *
	 * @var array
	 */
	public $types = array();

	/**
	 * @var WPCode_Pixel_Provider[]
	 */
	public $providers = array();


	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * @param WPCode_Pixel_Auto_Insert_Type $type_instance
	 *
	 * @return void
	 */
	public function register_type( $type_instance ) {
		$this->types[] = $type_instance;
	}

	/**
	 * @param WPCode_Pixel_Provider $provider_instance
	 *
	 * @return void
	 */
	public function register_provider( $provider_instance ) {
		$this->providers[] = $provider_instance;
	}

	/**
	 * Load and initialize the different types of auto-insert types.
	 *
	 * @return void
	 */
	public function load_types() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/auto-insert/class-wpcode-pixel-auto-insert-type.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/auto-insert/class-wpcode-pixel-auto-insert-facebook.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/auto-insert/class-wpcode-pixel-auto-insert-google.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/auto-insert/class-wpcode-pixel-auto-insert-pinterest.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/auto-insert/class-wpcode-pixel-auto-insert-tiktok.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/auto-insert/class-wpcode-pixel-auto-insert-snapchat.php';
	}

	public function load_providers() {
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/providers/class-wpcode-pixel-provider.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/providers/class-wpcode-pixel-provider-woocommerce.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/providers/class-wpcode-pixel-provider-edd.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/providers/class-wpcode-pixel-provider-memberpress.php';
	}

	/**
	 * Add hooks for this class.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_types' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'load_providers' ), 2 );
		add_filter( 'wpcode_get_snippets_for_location', array( $this, 'load_pixel_snippets' ), 10, 2 );
	}

	/**
	 * Load dynamically generated snippets specific to the pixel plugin
	 * directly in the locations used by the WPCode plugin.
	 *
	 * @param array  $snippets The array of snippets loaded in the location.
	 * @param string $location The name of the location filtered.
	 *
	 * @return WPCode_Snippet[]
	 */
	public function load_pixel_snippets( $snippets, $location ) {

		foreach ( $this->types as $type ) {
			/**
			 * @var WPCode_Pixel_Auto_Insert_Type $type
			 */
			$type_snippets = $type->get_snippets( $location );
			if ( is_array( $type_snippets ) && ! empty( $type_snippets ) ) {
				$snippets = array_merge( $snippets, $type_snippets );
			}

			if ( 'everywhere' === $location ) {
				// Load the code for cart events specific to each pixel.
				$this->add_cart_event( $type->get_cart_event_code() );
			}
		}

		foreach ( $this->providers as $provider ) {
			/**
			 * Some snippets should be run in one place for all pixel types, like the add to cart frontend script.
			 *
			 * @var $provider WPCode_Pixel_Provider
			 */
			$snippets = array_merge( $snippets, $provider->single_location_snippet( $location ) );
		}

		return $snippets;
	}

	/**
	 * Add an add to cart event code to the array used for the WooCommerce add to cart handler.
	 *
	 * @param string $event_code
	 *
	 * @return void
	 */
	public function add_cart_event( $event_code = '' ) {
		if ( ! empty( $event_code ) ) {
			$this->cart_events[] = $event_code;
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
		$args = wp_parse_args( $data, array(
			'id'            => 0,
			'title'         => 'Facebook Pixel',
			'code'          => '',
			'code_type'     => 'html',
			'location'      => '',
			'auto_insert'   => 1,
			'insert_number' => 1,
			'use_rules'     => false,
			'rules'         => array(),
			'priority'      => 50,
		) );

		return new WPCode_Snippet( $args );
	}

	/**
	 * Give pixels a chance to add extra order data (like cookies) to the order.
	 * Pixels should call the provider method WPCode_Pixel_Provider::store_extra_data() to store the data.
	 *
	 * @param WPCode_Pixel_Provider $provider The data provider that sent is requesting extra data.
	 * @param int                   $order_id The order id as a reference to which object to add the data.
	 *
	 * @return void
	 */
	public function store_extra_order_data( $provider, $order_id ) {
		foreach ( $this->types as $type ) {
			/**
			 * @var WPCode_Pixel_Auto_Insert_Type $type
			 */
			// Don't store extra order data if there's no api token set.
			if ( ! $type->has_api_token() ) {
				continue;
			}
			$extra_data = $type->get_extra_order_data( $order_id );
			if ( empty( $extra_data ) ) {
				continue;
			}
			$provider->store_extra_data( $order_id, $extra_data );
		}
	}

	/**
	 * Loop through all registered pixel types and send a server event.
	 *
	 * @param string                $event_name The event name.
	 * @param array                 $data The event data from the provider.
	 * @param WPCode_Pixel_Provider $provider The provider instance.
	 *
	 * @return void
	 */
	public function send_server_event( $event_name, $data, $provider ) {
		foreach ( $this->types as $type ) {
			/**
			 * @var WPCode_Pixel_Auto_Insert_Type $type
			 */
			$type->handle_server_event( $event_name, $data, $provider );
		}
	}
}

new WPCode_Pixel_Auto_Insert();
