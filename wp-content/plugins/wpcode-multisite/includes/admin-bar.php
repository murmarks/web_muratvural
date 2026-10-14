<?php
/**
 * Logic related to pointing the admin bar menus to the network admin.
 *
 * @package WPCode_Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wpcode_admin_bar_edit_snippet_link', 'wpcode_mu_admin_bar_edit_snippet_link', 10, 2 );
add_action( 'admin_bar_menu', 'wpcode_mu_admin_bar_add_global_menu', 9991 );

/**
 * Point the admin bar edit snippet link to the network admin.
 *
 * @param string                           $link The link.
 * @param WPCode_Snippet|WPCode_MU_Snippet $snippet The snippet.
 *
 * @return string
 */
function wpcode_mu_admin_bar_edit_snippet_link( $link, $snippet ) {

	if ( 'wpcode_mu_snippet' === $snippet->post_type ) {
		return network_admin_url( 'admin.php?page=wpcode-snippet-manager&snippet_id=' . $snippet->get_id() );
	}

	return $link;
}

/**
 * Add the network-specific WPCode info to the admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
 */
function wpcode_mu_admin_bar_add_global_menu( $wp_admin_bar ) {
	$global_scripts_data = wpcode_mu_get_global_admin_bar_data();

	if ( ! empty( $global_scripts_data ) ) {

		$node = $wp_admin_bar->get_node( 'wpcode-global-scripts' );

		if ( empty( $node ) ) {
			return;
		}

		// Let's extract the current number from the node title in the format "Global Scripts (x)".
		$matches = array();
		preg_match( '/\((\d+)\)/', $node->title, $matches );
		$current_number = (int) $matches[1];

		$node->title = sprintf(
			// translators: %d is the total number of global scripts.
			esc_html__( 'Global Scripts (%d)', 'wpcode-premium' ),
			array_sum( wp_list_pluck( $global_scripts_data, 'count' ) ) + $current_number
		);

		$wp_admin_bar->add_node( $node );

		foreach ( $global_scripts_data as $id => $global_scripts_area ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wpcode-network-global-' . $id,
					'parent' => 'wpcode-global-scripts',
					'title'  => esc_html( $global_scripts_area['label'] ),
					'meta'   => array(
						'class' => 'wpcode-admin-bar-info-submenu',
					),
					'href'   => $global_scripts_area['href'],
				)
			);
		}
	}
}

/**
 * Get the admin bar data for the global Header & Footer network menu items.
 *
 * @return array
 */
function wpcode_mu_get_global_admin_bar_data() {
	$data = array();

	$global_locations = array(
		'header' => array(
			'label' => __( 'Network Global Header', 'insert-headers-and-footers' ),
		),
	);
	if ( function_exists( 'wp_body_open' ) && version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
		$global_locations['body'] = array(
			'label' => __( 'Network Global Body', 'insert-headers-and-footers' ),
		);
	}
	$global_locations['footer'] = array(
		'label' => __( 'Network Global Footer', 'insert-headers-and-footers' ),
	);

	foreach ( $global_locations as $location => $location_texts ) {
		$scripts = get_site_option( 'wpcode_insert_' . $location );
		$count   = ! empty( $scripts ) ? 1 : 0;

		$location_texts['label'] .= ' (' . $count . ')';

		$data[ $location ] = array(
			'label' => $location_texts['label'],
			'href'  => network_admin_url( 'admin.php?page=wpcode-headers-footers#wpcode-global-' . $location ),
			'count' => $count,
		);
	}

	return $data;
}
