<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_divi', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_divi', 10, 2 );

function wpmc_scan_once_divi() {
  global $wpmc;

  $et_divi = get_option( 'et_divi', '' );
  if ( !empty( $et_divi ) ) {
    $postmeta_images_ids = array();
	$postmeta_images_urls = array();
    $wpmc->get_from_meta( $et_divi, array( 'divi_logo' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'THEME (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'THEME (URL)' );
  }
}	

function wpmc_scan_html_divi( $html, $id ) {
	global $wpmc;
	$posts_images_urls    = array();
	$galleries_images_ids = array();


	if ( empty( $html ) ) {
		return;
	}

	$nodes = $wpmc->nested_shortcodes_to_array( $html );
	$wpmc->array_to_ids_or_urls( $nodes, $galleries_images_ids, $posts_images_urls, true, [
		"src",
		"image",
		"url",
		"gallery_ids",
		"image__hover",
		"background_image"
	]);


	// All the media references ( IDs and URLs ) are using the srcset attribute. So let's SAFE them all.
	foreach( $galleries_images_ids as $key => $id ) {
		$urls = $wpmc->get_thumbnails_urls( $id );

		$wpmc->add_reference_url( $urls, 'DIVI (URL) {SAFE}', $id );
	}

	foreach( $posts_images_urls as $key => $url ) {
		$attachement_id = $wpmc->custom_attachment_url_to_postid( $url );
		$urls = $wpmc->get_thumbnails_urls_from_srcset( $attachement_id );

		$wpmc->add_reference_url( $urls, 'DIVI (URL) {SAFE}', $id );
	 }

	
	$wpmc->add_reference_url( $posts_images_urls, 'DIVI (URL)', $id );
	$wpmc->add_reference_id(  $galleries_images_ids, 'DIVI (ID)', $id );
}

?>