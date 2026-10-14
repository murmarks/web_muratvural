<?php
/**
 * Code snippets admin main list page.
 *
 * @package WPCode
 */

/**
 * Class for the code snippets page.
 */
class WPCode_MU_Admin_Page_Code_Snippets extends WPCode_Admin_Page_Code_Snippets {

	use WPCode_MU_Admin_Page;

	/**
	 * Init the custom table for the snippets list.
	 *
	 * @return void
	 */
	public function init_table() {
		require_once WPCODE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-code-snippets-table.php';
		require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/admin/pages/class-wpcode-mu-code-snippets-table.php';

		$this->snippets_table = new WPCode_Code_MU_Snippets_Table();
	}

}
