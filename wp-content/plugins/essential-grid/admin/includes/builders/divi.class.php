<?php
/**
 * @package   Essential_Grid
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.essential-grid.com/
 * @copyright 2025 ThemePunch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Add Divi support
 */
class Essential_Grid_Builders_Divi {

	public function __construct() {
		add_action( 'divi_extensions_init', [ $this, 'init' ] );
	}

	/**
	 * init Divi Module
	 *
	 * @return void
	 */
	public function init() {
		// require_once( ESG_PLUGIN_ADMIN_PATH . '/includes/builders/divi/includes/EsgDivi.php' );
	}

}
