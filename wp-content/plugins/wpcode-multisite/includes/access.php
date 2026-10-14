<?php
/**
 * Handles logic related to custom capabilities in the network context.
 *
 * @package WPCode_Multisite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wpcode_get_option', 'wpcode_mu_maybe_get_access_network_option', 10, 2 );

add_filter( 'map_meta_cap', 'wpcode_mu_map_meta_cap', 15, 4 );

/**
 * Override custom capabilities at network level if the addon is used.
 *
 * @param mixed  $value The value of the option.
 * @param string $option_name The name of the option.
 *
 * @return mixed
 */
function wpcode_mu_maybe_get_access_network_option( $value, $option_name ) {
	$capabilities = wpcode_custom_capabilities_keys();
	// Include the PHP setting.
	$capabilities[] = 'completely_disable_php';
	if ( in_array( $option_name, $capabilities, true ) ) {
		remove_filter( 'wpcode_get_option', 'wpcode_mu_maybe_get_access_network_option' );
		$value = wpcode_multisite()->settings->get_option( $option_name, $value );
		add_filter( 'wpcode_get_option', 'wpcode_mu_maybe_get_access_network_option', 10, 2 );
	}

	return $value;
}

/**
 * @param $caps
 * @param $cap
 * @param $user_id
 * @param $args
 *
 * @return mixed|string[]
 */
function wpcode_mu_map_meta_cap( $caps, $cap, $user_id, $args ) {

	$disable_access_for_regular_administrators = wpcode_multisite()->settings->get_option( 'mu_disable_administrators' );

	$capabilities   = wpcode_custom_capabilities_keys();
	$capabilities[] = 'wpcode_edit_snippets';
	$capabilities[] = 'wpcode_activate_snippets';
	$capabilities[] = 'wpcode_manage_settings';

	if ( $disable_access_for_regular_administrators && in_array( $cap, $capabilities, true ) ) {
		$caps = array( 'wpcode_mu_not_allowed' );
	}

	return $caps;
}
