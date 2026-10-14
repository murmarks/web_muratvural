<?php
/**
 * Register custom post type and taxonomies.
 *
 * @package WPCode-Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wpcode_mu_register_post_type', - 5 );
add_action( 'init', 'wpcode_mu_register_taxonomies', - 5 );
add_filter( 'wp_import_post_data_raw', 'wpcode_mu_prevent_wp_importer_import' );

/**
 * Register the post type for snippets.
 *
 * @return void
 */
function wpcode_mu_register_post_type() {
	register_post_type(
		'wpcode_mu_snippet',
		array(
			'public'     => false,
			'show_ui'    => false,
			'can_export' => false,
		)
	);
}

/**
 * Prevent the WP Importer plugin from importing snippets.
 *
 * @param array $post_data The post data.
 *
 * @return array
 */
function wpcode_mu_prevent_wp_importer_import( $post_data ) {
	if ( 'wpcode_mu_snippet' === $post_data['post_type'] ) {
		$post_data['post_type']    = '';
		$post_data['post_content'] = '';
	}

	return $post_data;
}

/**
 * Extend the taxonomies for our multisite post type.
 *
 * @return void
 */
function wpcode_mu_register_taxonomies() {
	register_taxonomy_for_object_type( 'wpcode_type', 'wpcode_mu_snippet' );
	register_taxonomy_for_object_type( 'wpcode_location', 'wpcode_mu_snippet' );
	register_taxonomy_for_object_type( 'wpcode_tags', 'wpcode_mu_snippet' );
}
