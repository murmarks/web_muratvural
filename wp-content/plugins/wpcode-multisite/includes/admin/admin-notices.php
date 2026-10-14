<?php
/**
 * Logic related to admin notices.
 *
 * @package WPCode_Multisite
 */

/**
 * Remove the license notices if this addon is active and license is set at network level.
 */
add_action( 'admin_init', function () {
	// Let's remove the license notices if this addon is active and license is set at network level.
	$license = wpcode()->license->get( true );

	if ( ! empty( $license ) ) {
		wpcode()->notice->notices = array();
	}
}, 11 );
