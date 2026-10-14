<?php
/**
 * Load the pixel-specific admin page.
 */

add_action( 'wpcode_before_admin_pages_loaded', 'wpcode_pixel_load_admin_pages', 10 );

/**
 * Replace the pixel class with the one specific to the addon.
 *
 * @param array $pages The loaded pages.
 *
 * @return void
 */
function wpcode_pixel_load_admin_pages( $pages ) {
	wpcode()->admin_page_loader->pages['pixel'] = 'WPCode_Pixel_Admin_Page_Pixel';
}
