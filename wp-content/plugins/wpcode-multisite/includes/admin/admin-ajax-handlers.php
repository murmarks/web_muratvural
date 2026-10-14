<?php

add_action( 'wp_ajax_wpcode_search_sites', 'wpcode_ajax_search_sites' );
/**
 * Ajax endpoint for searching through snippets.
 *
 * @return void
 */
function wpcode_ajax_search_sites() {
	check_ajax_referer( 'wpcode_admin' );

	if ( ! current_user_can( 'wpcode_edit_snippets' ) ) {
		wp_send_json_error();
	}

	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

	$sites = get_sites(
		array(
			'number' => 10,
			'search' => $term,
		)
	);

	$results = array();

	foreach ( $sites as $site ) {
		$results[] = array(
			'id'   => $site->blog_id,
			'text' => $site->blogname,
		);
	}

	wp_send_json(
		array(
			'results' => $results,
		)
	);
}
