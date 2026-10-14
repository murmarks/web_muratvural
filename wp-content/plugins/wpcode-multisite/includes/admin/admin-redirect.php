<?php
/**
 * Make sure we redirect to the correct page when the addon is activated from the WPCode settings page in the admin.
 *
 * @package WPCode_Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'wpcode_mu_redirect_settings_page' );
add_action( 'network_admin_menu', 'wpcode_mu_register_pro_settings_page' );
add_filter( 'parent_file', 'wpcode_mu_hide_settings_submenu', 1025 );

/**
 * Register the settings page.
 *
 * @return void
 */
function wpcode_mu_redirect_settings_page() {
	if ( ! is_network_admin() ) {
		return;
	}
	if ( ! isset( $_GET['page'] ) || 'wpcode-mu-settings' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_safe_redirect( network_admin_url( 'admin.php?page=wpcode-settings' ) );
}

/**
 * Register the settings page from the core plugin, so we can redirect.
 *
 * @return void
 */
function wpcode_mu_register_pro_settings_page() {
	add_submenu_page(
		'settings.php',
		'WPCode Settings',
		'WPCode Settings',
		'wpcode_edit_snippets',
		'wpcode-mu-settings',
		'__return_empty_string'
	);
}

/**
 * Hide the settings submenu.
 *
 * @return void
 */
function wpcode_mu_hide_settings_submenu() {
	remove_submenu_page( 'settings.php', 'wpcode-mu-settings' );
}
