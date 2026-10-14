<?php
/**
 * Plugin Name: WPCode Multisite
 * Plugin URI: https://www.wpcode.com/
 * Version: 1.0.3
 * Requires at least: 5.1
 * Requires PHP: 7.0
 * Tested up to: 6.4
 * Author: WPCode
 * Author URI: https://www.wpcode.com/
 * Description: Adds the ability to manage snippets across a multisite network.
 * Network: true
 * License: GPLv2 or later
 *
 * Text Domain:         wpcode-multisite
 * Domain Path:         /languages
 *
 * @package WPCode_Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WPCode_Multisite class.
 */
class WPCode_Multisite {
	/**
	 * Holds the instance of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var WPCode_Multisite The one true WPCode_Multisite.
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
	 * The cache object.
	 *
	 * @var WPCode_MU_Snippet_Cache
	 */
	public $cache;

	/**
	 * The auto insert object.
	 *
	 * @var WPCode_MU_Auto_Insert
	 */
	public $auto_insert;

	/**
	 * The admin page loader object.
	 *
	 * @var WPCode_MU_Admin_Page_Loader
	 */
	public $admin_page_loader;

	/**
	 * The settings object.
	 *
	 * @var WPCode_MU_Settings
	 */
	public $settings;

	/**
	 * Main instance of WPCode_Multisite.
	 *
	 * @return WPCode_Multisite
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPCode_Multisite ) ) {
			self::$instance = new WPCode_Multisite();
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
	}

	/**
	 * Set up global constants.
	 *
	 * @return void
	 */
	private function setup_constants() {

		define( 'WPCODE_MULTISITE_FILE', __FILE__ );

		$plugin_headers = get_file_data( WPCODE_MULTISITE_FILE, array( 'version' => 'Version' ) );

		define( 'WPCODE_MULTISITE_VERSION', $plugin_headers['version'] );
		define( 'WPCODE_MULTISITE_PLUGIN_BASENAME', plugin_basename( WPCODE_MULTISITE_FILE ) );
		define( 'WPCODE_MULTISITE_PLUGIN_URL', plugin_dir_url( WPCODE_MULTISITE_FILE ) );
		define( 'WPCODE_MULTISITE_PLUGIN_PATH', plugin_dir_path( WPCODE_MULTISITE_FILE ) );

		$this->version = WPCODE_MULTISITE_VERSION;
	}

	/**
	 * Require the files needed for the plugin.
	 *
	 * @return void
	 */
	private function includes() {
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/post-type.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-snippet.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-snippet-cache.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-auto-insert.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/overrides.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/global-output.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-settings.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/access.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/conditional-logic.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin-bar.php';

		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/class-wpcode-mu-admin-page-loader.php';
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/admin-notices.php';
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/admin-ajax-handlers.php';
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-library.php';
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-my-library.php';
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/class-wpcode-mu-library-auth.php';
			require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/admin-redirect.php';
		}

		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/loader.php';
	}

	/**
	 * Load components in the main plugin instance.
	 *
	 * @return void
	 */
	public function load_components() {
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			$this->admin_page_loader = new WPCode_MU_Admin_Page_Loader();
		}

		$this->cache       = new WPCode_MU_Snippet_Cache();
		$this->auto_insert = new WPCode_MU_Auto_Insert();
		$this->settings    = new WPCode_MU_Settings();

		if ( wpcode_mu_is_network() ) {
			wpcode()->settings = $this->settings;
			wpcode()->library  = new WPCode_MU_Library();
			wpcode()->my_library  = new WPCode_MU_My_Library();
			// This one will change the wpcode()->library_auth in the constructor after removing some parent actions.
			new WPCode_MU_Library_Auth();
		}
	}
}

/**
 * Deactivate the plugin.
 *
 * @since 1.0.0
 */
function wpcode_multisite_deactivation() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
}


/**
 * Admin notice for a minimum PHP version.
 *
 * @since 1.0.0
 */
function wpcode_multisite_fail_php_version() {

	echo '<div class="notice notice-error"><p>';
	printf(
		wp_kses( /* translators: %s - wpcode.com documentation page URL. */
			__( 'The WPCode Multisite Addon plugin has been deactivated. Your site is running an outdated version of PHP that is no longer supported and is not compatible with the Multisite addon. <a href="%s" target="_blank" rel="noopener noreferrer">Read more</a> for additional information.', 'wpcode-multisite' ),
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
function wpcode_multisite_required() {
	if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
		add_action( 'admin_init', 'wpcode_multisite_deactivation' );
		add_action( 'admin_notices', 'wpcode_multisite_fail_php_version' );

		return false;
	}

	if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Premium' ) ) {
		return false;
	}

	if ( is_admin() ) {
		if ( ! class_exists( 'WPCode_License' ) || ! in_array( WPCode()->license->type( true ), array(
				'elite',
				'bundle',
			), true ) ) {
			return false;
		}
	}

	return true;
}

/**
 * The main function that returns WPCode_Multisite.
 *
 * @return WPCode_Multisite
 * @since 1.0.0
 *
 */
function wpcode_multisite() {
	return WPCode_Multisite::instance();
}

/**
 * Load the provider class.
 *
 * @since 1.0.0
 */
function wpcode_multisite_load() {

	// Check requirements.
	if ( ! wpcode_multisite_required() ) {
		return;
	}

	// Load the plugin.
	wpcode_multisite();
}

add_action( 'wpcode_loaded', 'wpcode_multisite_load' );
