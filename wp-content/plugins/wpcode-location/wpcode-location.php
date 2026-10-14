<?php
/**
 * Plugin Name: WPCode Location
 * Plugin URI: https://www.wpcode.com/
 * Version: 1.0.2
 * Requires at least: 5.5
 * Requires PHP: 5.6
 * Tested up to: 6.6
 * Author: WPCode
 * Author URI: https://www.wpcode.com/
 * Description: Use location-based data for your snippets conditional logic.
 * License: GPLv2 or later
 *
 * Text Domain:         wpcode-location
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCode_Location {
	/**
	 * Holds the instance of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var WPCode_Location The one true WPCode_Location.
	 */
	private static $instance;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '';

	/**
	 * Main instance of WPCode_Pixel.
	 *
	 * @return WPCode_Location
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPCode_Location ) ) {
			self::$instance = new WPCode_Location();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_constants();
		$this->includes();
		$this->load_components();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 15 );
	}

	/**
	 * Set up global constants.
	 *
	 * @return void
	 */
	private function setup_constants() {

		define( 'WPCODE_LOCATION_FILE', __FILE__ );

		$plugin_headers = get_file_data( WPCODE_LOCATION_FILE, array( 'version' => 'Version' ) );

		define( 'WPCODE_LOCATION_VERSION', $plugin_headers['version'] );
		define( 'WPCODE_LOCATION_PLUGIN_BASENAME', plugin_basename( WPCODE_LOCATION_FILE ) );
		define( 'WPCODE_LOCATION_PLUGIN_URL', plugin_dir_url( WPCODE_LOCATION_FILE ) );
		define( 'WPCODE_LOCATION_PLUGIN_PATH', plugin_dir_path( WPCODE_LOCATION_FILE ) );

		$this->version = WPCODE_LOCATION_VERSION;
	}

	/**
	 * Require the files needed for the plugin.
	 *
	 * @return void
	 */
	private function includes() {
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			include_once WPCODE_LOCATION_PLUGIN_PATH . 'includes/admin/admin.php';
		}

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_includes' ) );
	}

	/**
	 * Include the files needed for the plugin that should be loaded later to give other components a chance to load.
	 *
	 * @return void
	 */
	public function plugins_loaded_includes() {
		include_once WPCODE_LOCATION_PLUGIN_PATH . 'includes/class-wpcode-conditional-location.php';
	}

	/**
	 * Load components in the main plugin instance.
	 *
	 * @return void
	 */
	public function load_components() {
	}

	/**
	 * Load the plugin translations.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		if ( is_user_logged_in() ) {
			unload_textdomain( 'wpcode-location' );
		}

		load_plugin_textdomain( 'wpcode-location', false, dirname( plugin_basename( WPCODE_LOCATION_FILE ) ) . '/languages/' );
	}
}

/**
 * Deactivate the plugin.
 *
 * @since 1.0.0
 */
function wpcode_location_deactivation() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
}


/**
 * Admin notice for a minimum PHP version.
 *
 * @since 1.0.0
 */
function wpcode_location_fail_php_version() {

	echo '<div class="notice notice-error"><p>';
	printf(
		wp_kses( /* translators: %s - wpcode.com documentation page URL. */
			__( 'The WPCode Location addon has been deactivated. Your site is running an outdated version of PHP that is no longer supported and is not compatible with the Location addon. <a href="%s" target="_blank" rel="noopener noreferrer">Read more</a> for additional information.', 'wpcode-location' ),
			array(
				'a' => array(
					'href'   => array(),
					'rel'    => array(),
					'target' => array(),
				),
			)
		),
		'https://wpcode.com/docs/supported-php-version/'
	);
	echo '</p></div>';

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

/**
 * Check addon requirements.
 *
 * @return bool
 */
function wpcode_location_required() {
	if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
		add_action( 'admin_init', 'wpcode_location_deactivation' );
		add_action( 'admin_notices', 'wpcode_location_fail_php_version' );

		return false;
	}

	if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Premium' ) ) {
		return false;
	}

	if ( is_admin() ) {
		if ( ! class_exists( 'WPCode_License' ) || ! in_array( WPCode()->license->type(), array(
				'pro',
				'elite',
				'bundle',
			), true ) ) {
			return false;
		}
	}

	return true;
}

/**
 * The main function that returns WPCode_Pixel.
 *
 * @return WPCode_Location
 * @since 1.0.0
 *
 */
function wpcode_location() {
	return WPCode_Location::instance();
}

/**
 * Load the provider class.
 *
 * @since 1.0.0
 */
function wpcode_location_load() {

	// Check requirements.
	if ( ! wpcode_location_required() ) {
		return;
	}

	// Load the plugin.
	wpcode_location();
}

add_action( 'wpcode_loaded', 'wpcode_location_load' );
