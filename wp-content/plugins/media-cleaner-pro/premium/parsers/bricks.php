<?php


add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_bricks', 10, 2 );


  /**
   * Runs for each postmeta of any post type.
   * Scans and collects image IDs and URLs from post meta.
   *
   * @param int $id The post ID.
   */
  function wpmc_scan_postmeta_bricks( $id ) {
    global $wpmc;
    $postmeta_images_ids = array();
    $postmeta_images_urls = array();
  
    // Fetch data from post meta with key '_bricks_page_content_2'
    $data = get_post_meta( $id, '_bricks_page_content_2' );
    $attributes = [ 'url', 'id' ];
    
    // Get images from post meta data
    $wpmc->get_from_meta( $data, $attributes, $postmeta_images_ids, $postmeta_images_urls );
  
    // Add image references to the Media Cleaner
    $wpmc->add_reference_id( $postmeta_images_ids, 'Bricks (ID)', $id );
    $wpmc->add_reference_url( $postmeta_images_urls, 'Bricks (URL)', $id );
  }
  

?>