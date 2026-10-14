<?php
/**
 * Plugin Name: WPCode Conversion Pixels
 * Plugin URI: https://www.wpcode.com/
 * Version: 1.1.9
 * Requires at least: 4.9
 * Requires PHP: 5.6
 * Tested up to: 6.6
 * Author: WPCode
 * Author URI: https://www.wpcode.com/
 * Description: Easily add conversion pixels to your WordPress site using the WPCode plugin.
 * License: GPLv2 or later
 *
 * Text Domain:         wpcode-pixel
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCode_Pixel {
	/**
	 * Holds the instance of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var WPCode_Pixel The one true WPCode_Pixel.
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
	 * Auto-insert functionality specific to this addon.
	 *
	 * @var WPCode_Pixel_Auto_Insert
	 */
	public $auto_insert;

	/**
	 * Main instance of WPCode_Pixel.
	 *
	 * @return WPCode_Pixel
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPCode_Pixel ) ) {
			self::$instance = new WPCode_Pixel();
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

		define( 'WPCODE_PIXEL_FILE', __FILE__ );

		$plugin_headers = get_file_data( WPCODE_PIXEL_FILE, array( 'version' => 'Version' ) );

		define( 'WPCODE_PIXEL_VERSION', $plugin_headers['version'] );
		define( 'WPCODE_PIXEL_PLUGIN_BASENAME', plugin_basename( WPCODE_PIXEL_FILE ) );
		define( 'WPCODE_PIXEL_PLUGIN_URL', plugin_dir_url( WPCODE_PIXEL_FILE ) );
		define( 'WPCODE_PIXEL_PLUGIN_PATH', plugin_dir_path( WPCODE_PIXEL_FILE ) );

		$this->version = WPCODE_PIXEL_VERSION;
	}

	/**
	 * Require the files needed for the plugin.
	 *
	 * @return void
	 */
	private function includes() {
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/admin/admin-page-loader.php';
			require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-pixel-admin-page-pixel.php';
		}

		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/class-wpcode-pixel-auto-insert.php';
		require_once WPCODE_PIXEL_PLUGIN_PATH . 'includes/frontend-tracking.php';
	}

	/**
	 * Load components in the main plugin instance.
	 *
	 * @return void
	 */
	public function load_components() {
		$this->auto_insert = new WPCode_Pixel_Auto_Insert();
	}

	/**
	 * Load the plugin translations.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		if ( is_user_logged_in() ) {
			unload_textdomain( 'wpcode-pixel' );
		}

		load_plugin_textdomain( 'wpcode-pixel', false, dirname( plugin_basename( WPCODE_PIXEL_FILE ) ) . '/languages/' );
	}
}

/**
 * Deactivate the plugin.
 *
 * @since 1.0.0
 */
function wpcode_pixel_deactivation() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
}


/**
 * Admin notice for a minimum PHP version.
 *
 * @since 1.0.0
 */
function wpcode_pixel_fail_php_version() {

	echo '<div class="notice notice-error"><p>';
	printf(
		wp_kses( /* translators: %s - wpcode.com documentation page URL. */
			__( 'The WPCode Conversion Pixels plugin has been deactivated. Your site is running an outdated version of PHP that is no longer supported and is not compatible with the Conversion Pixels addon. <a href="%s" target="_blank" rel="noopener noreferrer">Read more</a> for additional information.', 'wpcode-pixel' ),
			[
				'a' => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
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
function wpcode_pixel_required() {
	if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
		add_action( 'admin_init', 'wpcode_pixel_deactivation' );
		add_action( 'admin_notices', 'wpcode_pixel_fail_php_version' );

		return false;
	}

	if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Premium' ) ) {
		return false;
	}

	if ( is_admin() ) {
		if ( ! class_exists( 'WPCode_License' ) || ! in_array( WPCode()->license->type(), array(
				'plus',
				'pro',
				'elite',
				'bundle'
			), true ) ) {
			return false;
		}
	}

	return true;
}

/**
 * The main function that returns WPCode_Pixel.
 *
 * @since 1.0.0
 *
 * @return WPCode_Pixel
 */
function wpcode_pixel() {
	return WPCode_Pixel::instance();
}

/**
 * Load the provider class.
 *
 * @since 1.0.0
 */
function wpcode_pixel_load() {

	// Check requirements.
	if ( ! wpcode_pixel_required() ) {
		return;
	}

	// Load the plugin.
	wpcode_pixel();
}

add_action( 'wpcode_loaded', 'wpcode_pixel_load' );
