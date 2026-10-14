<?php
/**
 * WPCode Multisite Admin Page Loader.
 *
 * @package WPCode-Multisite
 */

/**
 * WPCode Multisite Admin Page Loader class.
 */
class WPCode_MU_Admin_Page_Loader extends WPCode_Admin_Page_Loader_Pro {

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		remove_action( 'network_admin_menu', array( wpcode()->admin_page_loader, 'register_network_admin_menu' ), 9 );

		add_action( 'network_admin_menu', array( $this, 'register_network_admin_menu' ), 9 );
	}

	/**
	 * Require pro-specific files.
	 *
	 * @return void
	 */
	public function require_files() {
		parent::require_files();
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/trait-wpcode-mu-admin-page.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-admin-page-code-snippets.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-admin-page-snippet-manager.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-admin-page-revisions.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-admin-page-headers-footers.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-admin-page-settings.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-admin-page-library.php';
	}

	/**
	 * Override network-specific pages.
	 *
	 * @return void
	 */
	public function prepare_pages() {
		$this->pages = array();

		$this->pages['code_snippets']   = 'WPCode_MU_Admin_Page_Code_Snippets';
		$this->pages['snippet_manager'] = 'WPCode_MU_Admin_Page_Snippet_Manager';
		$this->pages['revisions']       = 'WPCode_MU_Admin_Page_Revisions';
		$this->pages['headers_footers'] = 'WPCode_MU_Admin_Page_Headers_Footers';
		$this->pages['library']         = 'WPCode_MU_Admin_Page_Library';
		$this->pages['settings']        = 'WPCode_MU_Admin_Page_Settings';
	}

	/**
	 * Register network admin menu.
	 *
	 * @return void
	 */
	public function register_network_admin_menu() {
		$this->add_main_menu_item();

		$this->load_pages();
	}
}
