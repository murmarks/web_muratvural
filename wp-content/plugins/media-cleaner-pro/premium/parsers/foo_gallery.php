<?php

add_action('wpmc_scan_once', 'wpmc_scan_once_foo_gallery', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_foo_gallery', 10, 2);

function wpmc_scan_once_foo_gallery()
{
    global $wpdb, $wpmc;
    // Get from database all the foogallery attachments post meta
    $postmeta_images_ids = array();
    $postmeta_images_urls = array();

    $meta = $wpdb->get_results(
        "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'foogallery_attachments'"
    );

    foreach ( $meta as $item ) {
        $decoded = maybe_unserialize( $item->meta_value );
        if ( is_array( $decoded ) ) {
            $wpmc->array_to_ids_or_urls( $decoded, $postmeta_images_ids, $postmeta_images_urls );
        }
    }

    $postmeta_images_ids = array_unique( $postmeta_images_ids );

    // Foo Gallery uses the media full size URL by default, so let's add them to the reference, they might not be used in the content so we use the {SAFE} tag
    $postmeta_images_urls = array_map( function( $id ) use ( $wpmc ) {
        $url = wp_get_attachment_url( $id );
        $url = $wpmc->clean_url( $url );
        return $url;
    }, $postmeta_images_ids );

    $wpmc->add_reference_id( $postmeta_images_ids, 'Foo Gallery (ID)' );
    $wpmc->add_reference_url( $postmeta_images_urls, 'Foo Gallery (URL) {SAFE}' );
}

function wpmc_scan_html_foo_gallery( $html, $id )
{
    global $wpmc;

    if( !$wpmc->get_shortcode_analysis() ) return;

    $matches = array();
    $pattern = get_shortcode_regex( ['foogallery'] );
    preg_match_all( '/'. $pattern .'/s', $html, $matches );

    foreach( $matches[0] as $shortcode ) {
        $inner_html = do_shortcode( $shortcode );

        $urls = get_urls_from_inner_html_foo_gallery( $inner_html );
        $wpmc->add_reference_url( $urls, 'Foo Gallery (URL)', $id );
    }
}


function get_urls_from_inner_html_foo_gallery( $html ) {
    global $wpmc;

    $urls = array();
    $dom = new DOMDocument();
    @$dom->loadHTML( $html );

    $xpath = new DOMXPath($dom);
    $attributes = ['data-src-fg'];

    foreach ($attributes as $attr) {
        $nodes = $xpath->query('//@' . $attr);
        foreach ($nodes as $node) {
            
            $url = $node->nodeValue;
            $url = $wpmc->clean_url( $url );

            if ( !empty( $url ) ) {
                array_push( $urls, $url );
            }
        }
    }

    return $urls;
}

?>