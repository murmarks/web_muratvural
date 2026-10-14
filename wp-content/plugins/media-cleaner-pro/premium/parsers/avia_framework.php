<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_enfold', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_avia_framework', 10, 2 );

function wpmc_scan_once_enfold() {
  global $wpmc;

  $avia_options_enfold = get_option( 'avia_options_enfold', '' );
  if ( !empty( $avia_options_enfold ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $avia_options_enfold, array( 'logo', 'preloader_logo' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'THEME (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'THEME (URL)' );
  }
}	

function wpmc_scan_html_avia_framework( $html, $id ) {
	global $wpmc;

	// $posts_images_urls = array();
	// $galleries_images_et = array();

	// // Modules with URL
	// preg_match_all( "/url=[\"']((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp|mp3))[\"']/", $html, $res );
	// if ( !empty( $res ) && isset( $res[1] ) ) {
	// 	foreach ( $res[1] as $url ) {
	// 		array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
	// 	}
	// }

	// // Modules with SRC
	// preg_match_all( "/src=[\"']((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp|mp3))[\"']/", $html, $res );
	// if ( !empty( $res ) && isset( $res[1] ) ) {
	// 	foreach ( $res[1] as $url ) {
	// 		array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
	// 	}
	// }

	// // Galleries
	// preg_match_all( "/ids=[\"']([0-9,]+)[\"']/", $html, $res );
	// if ( !empty( $res ) && isset( $res[1] ) ) {
	// 	foreach ( $res[1] as $r ) {
	// 		$ids = explode( ',', $r );
	// 		$galleries_images_et = array_merge( $galleries_images_et, $ids );
	// 	}
	// }



	$ids = [];
	$urls = [];

	$nodes = $wpmc->nested_shortcodes_to_array( $html );
	$wpmc->array_to_ids_or_urls( $nodes, $ids, $urls, true, [ "image", "id", "ids", "src", "url", "attachment" ] );

	$wpmc->add_reference_url( $urls, 'ENFOLD (URL)', $id );
	$wpmc->add_reference_id( $ids, 'ENFOLD (ID)', $id );


}

?>