<?php
/**
 * WPCode Location Admin.
 *
 * @package WPCode_Location
 */

add_action( 'admin_enqueue_scripts', 'wpcode_location_admin_snippet_manager_data', 5 );
add_action( 'admin_enqueue_scripts', 'wpcode_location_admin_scripts', 15 );

/**
 * Add the location-based notice to the snippet manager.
 */
function wpcode_location_admin_snippet_manager_data() {
	$screen = get_current_screen();
	if ( 'code-snippets_page_wpcode-snippet-manager' !== $screen->id ) {
		return;
	}

	add_filter( 'wpcode_admin_js_data', 'wpcode_location_admin_js_data' );
}

/**
 * Load admin scripts here.
 *
 * @return void
 */
function wpcode_location_admin_scripts() {
	$screen = get_current_screen();
	if ( 'code-snippets_page_wpcode-snippet-manager' !== $screen->id ) {
		return;
	}

	$admin_asset_file = WPCODE_LOCATION_PLUGIN_PATH . 'build/admin.asset.php';

	if ( ! file_exists( $admin_asset_file ) ) {
		return;
	}

	$asset = require $admin_asset_file;
	wp_enqueue_script( 'wpcode-location-admin-js', WPCODE_LOCATION_PLUGIN_URL . 'build/admin.js', [ 'wpcode-admin-js' ], $asset['version'], true );
}

/**
 * Add the location-based notice to the admin JS data.
 *
 * @param array $data The admin JS data.
 *
 * @return array
 */
function wpcode_location_admin_js_data( $data ) {
	$data['location_based_notice'] = __( 'Please note: Location-based conditional logic works best on pages that are not cached (e.g., cart page, checkout page). Cached pages may cause the logic to not function as expected.', 'wpcode-location' );
	$data['local_notice']          = __( 'Please note: it appears that you are running a website locally so location-based checks will not work correctly since your network IP is not used.', 'wpcode-location' );

	return $data;
}

