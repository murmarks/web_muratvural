<?php
/**
 * These functions override pluggable functions.
 *
 * @package WPCode Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The check we perform to see if we're in the network admin or if our ajax request is for a multisite and the action starts with wpcode_.
 *
 * @return bool
 */
function wpcode_mu_is_network() {
	return is_multisite() && is_network_admin() || wp_doing_ajax() && isset( $_POST['action'] ) && 'wpcode_' === substr( sanitize_text_field( wp_unslash( $_POST['action'] ) ), 0, 7 ) && isset( $_POST['multisite'] ) && $_POST['multisite']; // phpcs:ignore
}

if ( ! function_exists( 'wpcode_get_snippet' ) ) {
	/**
	 * Load a snippet by id, WP_Post or array.
	 *
	 * @param array|int|WP_Post $snippet Load a snippet by id, WP_Post or array.
	 *
	 * @return WPCode_Snippet
	 */
	function wpcode_get_snippet( $snippet ) {
		// Let's check if we're in the network admin or if our ajax request is for a multisite and the action starts with wpcode_.
		if ( wpcode_mu_is_network() ) {
			return new WPCode_MU_Snippet( $snippet );
		}

		return new WPCode_Snippet( $snippet );
	}
}

if ( ! function_exists( 'wpcode_get_post_type' ) ) {
	/**
	 * Get the post type we use for snippets.
	 *
	 * @return string
	 */
	function wpcode_get_post_type() {
		if ( wpcode_mu_is_network() ) {
			return 'wpcode_mu_snippet';
		} else {
			return 'wpcode';
		}
	}
}

add_filter( 'wpcode_admin_js_data', 'wpcode_multisite_admin_js_data' );

/**
 * Add multisite data to the admin js data.
 *
 * @param array $data The admin js data.
 *
 * @return array
 */
function wpcode_multisite_admin_js_data( $data ) {
	$data['multisite'] = is_multisite() && is_network_admin();

	return $data;
}

add_action( 'admin_init', 'wpcode_mu_settings_page_overrides', 15 );

/**
 * Hide access controls at site level when using the multisite addon.
 *
 * @return void
 */
function wpcode_mu_settings_page_overrides() {
	if ( isset( wpcode()->admin_page_loader->pages['settings']->views ) ) {
		unset( wpcode()->admin_page_loader->pages['settings']->views['access'] );
	}
}