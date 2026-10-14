<?php defined( 'ABSPATH' ) || exit;
/*
	Plugin Name: WP Sheet Editor - Automations
	Description: Automate tasks in the spreadsheet app to make you more productive. Schedule exports, imports, bulk edits/formulas/php functions, manual edits in the cells, and more. Run tasks once or recurringly. This is an extension and not a standalone plugin.
	Version: 1.0.8
	Update URI: https://api.freemius.com
	Author:      WP Sheet Editor
	Author URI:  http://wpsheeteditor.com/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=automations
	Plugin URI: https://wpsheeteditor.com/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=automations
	License:     GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
	WC requires at least: 4.0
	WC tested up to: 9.3
	Text Domain: vg_sheet_editor_automations
	Domain Path: /lang
	@fs_premium_only
 */


if ( isset( $_GET['wpse_troubleshoot8987'] ) ) {
	return;
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/vg-plugin-sdk/index.php';
require_once __DIR__ . '/vendor/action-scheduler/action-scheduler.php';
require_once 'vendor/freemius/start.php';
require_once 'inc/freemius-init.php';

if ( ! class_exists( 'WPSE_Automations_Init' ) ) {

	class WPSE_Automations_Init {

		private static $instance               = null;
		public static $file                    = __FILE__;
		public static $dir                     = __DIR__;
		public $version                        = '1.0.8';
		public $buy_link                       = null;
		public $args                           = array();
		public $vg_plugin_sdk                  = null;
		public $extra_fields_for_sync_settings = array();

		private function __construct() {
		}

		function init_plugin_sdk() {
			$this->args          = array(
				'main_plugin_file'         => __FILE__,
				'show_welcome_page'        => true,
				'welcome_page_file'        => self::$dir . '/views/welcome-page-content.php',
				'website'                  => 'https://wpsheeteditor.com',
				'logo_width'               => 180,
				'logo'                     => plugins_url( '/assets/imgs/logo.svg', __FILE__ ),
				'buy_link'                 => $this->buy_link,
				'plugin_name'              => 'WP Sheet Editor - Automations',
				'plugin_prefix'            => 'wpsea_',
				'show_whatsnew_page'       => true,
				'whatsnew_pages_directory' => self::$dir . '/views/whats-new/',
				'plugin_version'           => $this->version,
				'plugin_options'           => null,
			);
			$this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK( $this->args );
		}
		function notify_wrong_core_version() {
			$plugin_data = get_plugin_data( __FILE__, false, false );
			?>
			<div class="notice notice-error">
				<p><?php _e( 'Please update the WP Sheet Editor plugin and all its extensions to the latest version. The features of the plugin "' . $plugin_data['Name'] . '" will be disabled temporarily because it is the newest version and it conflicts with old versions of other WP Sheet Editor plugins. The features will be enabled automatically after you install the updates.', 'vg_sheet_editor_automations' ); ?></p>
			</div>
			<?php
		}

		public function init() {
			$this->buy_link = wpsea_fs()->checkout_url();
			$this->init_plugin_sdk();

			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						$main_file  = __FILE__;
						$parent_dir = dirname( $main_file, 2 );
						$new_path   = str_replace( $parent_dir, '', $main_file );
						$new_path   = wp_normalize_path( ltrim( $new_path, '\\/' ) );
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $new_path, true );
					}
				}
			);

			if ( function_exists( 'VGSE' ) && version_compare( VGSE()->version, '2.25.14-beta.2' ) < 0 ) {
				add_action( 'admin_notices', array( $this, 'notify_wrong_core_version' ) );
				return;
			}

			if ( ! wpsea_fs()->can_use_premium_code__premium_only() ) {
				return;
			}
			$directories = array_unique( array_merge( array( 'core' ), glob( __DIR__ . '/modules/*', GLOB_ONLYDIR ) ) );
			if ( ! empty( $directories ) ) {
				$directories = array_map( 'basename', $directories );
			}

			foreach ( $directories as $directory ) {
				$php_files = glob( __DIR__ . '/modules/' . $directory . '/*.php' );
				foreach ( $php_files as $file_path ) {
					if ( file_exists( $file_path ) ) {
						require_once $file_path;
					}
				}
			}
			add_action( 'vg_sheet_editor/initialized', array( $this, 'after_wpse_init' ), 20 );
			add_action( 'vg_sheet_editor/after_enqueue_assets', array( $this, 'enqueue_assets' ) );
			add_action( 'init', array( $this, 'after_init' ) );

			add_action( 'admin_init', array( $this, 'db_update' ) );
		}

		public function db_update() {
			global $wpdb;

			if ( (int) get_option( 'vgse_automations_db_version' ) < 2 ) {
				$sql   = $wpdb->prepare(
					"
			ALTER TABLE %i
			CHANGE COLUMN `id` `id` INT(10) NOT NULL AUTO_INCREMENT,
			CHANGE COLUMN `author` `author` BIGINT(19) NOT NULL DEFAULT '0',
			CHANGE COLUMN `repeat_every` `repeat_every` TINYINT(3) NULL DEFAULT NULL,
			CHANGE COLUMN `page` `page` INT(10) NOT NULL DEFAULT 1,
			CHANGE COLUMN `runs_count` `runs_count` INT(10) NOT NULL DEFAULT 0
			",
					$wpdb->prefix . 'wpse_scheduled_tasks'
				);
				$query = $wpdb->query( $sql );
				update_option( 'vgse_automations_db_version', 2 );
			}
		}

		function after_init() {
			load_plugin_textdomain( 'vg_sheet_editor_automations', false, basename( __DIR__ ) . '/lang/' );
		}

		public function enqueue_assets() {
			$directories = array_unique( array_merge( array( 'core' ), glob( __DIR__ . '/modules/*', GLOB_ONLYDIR ) ) );
			if ( ! empty( $directories ) ) {
				$directories = array_map( 'basename', $directories );
			}

			foreach ( $directories as $directory ) {
				$js_file = __DIR__ . '/modules/' . $directory . '/init.js';
				if ( file_exists( $js_file ) ) {
					wp_enqueue_script( 'wp-sheet-editor-automations-' . $directory, plugins_url( '/init.js', $js_file ), array(), VGSE()->version, false );
				}
			}
		}

		public function after_wpse_init() {
			$this->extra_fields_for_sync_settings = apply_filters(
				'vg_sheet_editor/automations/extra_fields_for_sync_settings',
				array(
					'exports' => array( 'trash_wp_rows_missing_from_gs', 'incremental_export' ),
					'imports' => array( 'trash_wp_rows_missing_from_gs' ),
				)
			);
			add_filter( 'vg_sheet_editor/options_page/options', array( $this, 'add_settings_page_options' ) );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ) );
		}

		function register_toolbar_items( $editor ) {
			if ( ! WP_Sheet_Editor_Helpers::current_user_can( 'install_plugins' ) ) {
				return;
			}
			$editor->args['toolbars']->register_item(
				'wpse_automations_license',
				array(
					'type'                  => 'button',
					'content'               => __( 'Automations', 'vg_sheet_editor_automations' ),
					'url'                   => wpsea_fs()->get_account_url(),
					'toolbar_key'           => 'secondary',
					'extra_html_attributes' => ' target="_blank" ',
					'allow_in_frontend'     => false,
					'fs_id'                 => wpsea_fs()->get_id(),
					'toolbar_key'           => 'secondary',
					'parent'                => 'wpse_license',
				),
				$editor->args['provider']
			);
		}

		/**
		 * Add fields to options page
		 * @param array $sections
		 * @return array
		 */
		function add_settings_page_options( $sections ) {
			$sections['customize_features']['fields'][] = array(
				'id'      => 'be_rest_api_only',
				'type'    => 'switch',
				'title'   => __( 'Deactivate everything. Use the REST API only', 'vg_sheet_editor_automations' ),
				'desc'    => __( 'If you activate this option we will deactivate all the spreadsheets, settings pages, and the entire plugin will become invisible to the user. Only the REST API will remain active. This is useful for advanced scenarios when you only use our REST API to keep websites synchronized with external spreadsheets or systems. When this option is active, our settings page will moved under the general settings menu because our sheet editor menu will be removed', 'vg_sheet_editor_automations' ),
				'default' => false,
			);
			return $sections;
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPSE_Automations_Init();
				self::$instance->init();
			}
			return self::$instance;
		}

		public function __set( $name, $value ) {
			$this->name = $value;
		}

		public function __get( $name ) {
			return $this->name;
		}
	}
}

if ( ! function_exists( 'WPSE_Automations_Init_Obj' ) ) {
	function WPSE_Automations_Init_Obj() {
		return WPSE_Automations_Init::get_instance();
	}
}
WPSE_Automations_Init_Obj();

if ( ! function_exists( 'wpse_automations_create_tables' ) ) {
	function wpse_automations_create_tables() {
		global $wpdb;
		// @todo Create the table in all the subsites of the network when they network-activate the plugin?

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		$sql = "CREATE TABLE `{$wpdb->prefix}wpse_scheduled_tasks` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`author` BIGINT(19) NOT NULL DEFAULT '0',
			`sheet_key` VARCHAR(100) NOT NULL,
			`created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			`category` VARCHAR(100) NOT NULL,
			`job_id` VARCHAR(100) NULL DEFAULT NULL,
			`status` VARCHAR(100) NULL DEFAULT NULL,
			`settings` LONGTEXT NULL DEFAULT NULL,
			`task_args` LONGTEXT NULL,
			`repeat_unit` VARCHAR(100) NULL DEFAULT NULL,
			`repeat_every` TINYINT(3) NULL DEFAULT NULL,
			`date` DATETIME NULL DEFAULT NULL,
			`previous_run` DATETIME NULL DEFAULT NULL,
			`original_date` DATETIME NULL DEFAULT NULL,
			`name` VARCHAR(150) NULL DEFAULT NULL,
			`page` INT(10) NOT NULL DEFAULT 1,
			`runs_count` INT(10) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `job_id` (`job_id`)
		)           
		$collate";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'vgse_automations_db_version', 2 );
	}
	register_activation_hook( __FILE__, 'wpse_automations_create_tables' );
}
