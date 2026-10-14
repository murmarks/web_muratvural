<?php

add_action( 'wp_enqueue_scripts', 'wpcode_pixel_enqueue_scripts' );

/**
 * Enqueue scripts for the frontend.
 *
 * @return void
 */
function wpcode_pixel_enqueue_scripts() {

	// Let's see if we have any click events registered.
	$click_events = wpcode()->settings->get_option( 'clicks_data' );
	if ( empty( $click_events ) ) {
		return;
	}

	// If we have click events, let's enqueue the script.
	$asset_file = WPCODE_PIXEL_PLUGIN_PATH . 'build/click.asset.php';

	// If the asset file doesn't exist, we can't enqueue the script.
	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = include_once $asset_file;

	$click_events_data = array(
		'click_events' => $click_events,
	);

	// Let's see if any of the click events has google assigned in the platforms array.
	$google_click_events = array_filter(
		$click_events,
		function ( $click_event ) {
			return in_array( 'google', $click_event['platforms'], true );
		}
	);
	if ( $google_click_events ) {
		// Let's loop through the auto-insert options in wpcode_pixel()->auto_insert and find the google instance.
		$google_auto_insert = array_filter(
			wpcode_pixel()->auto_insert->types,
			function ( $auto_insert ) {
				return 'google' === $auto_insert->type;
			}
		);
		if ( ! empty( $google_auto_insert ) ) {
			$google_auto_insert = array_values( $google_auto_insert );
			// Let's use the send_to value.
			$click_events_data['google_ads_id']       = $google_auto_insert[0]->get_ads_id();
			$click_events_data['google_analytics_id'] = $google_auto_insert[0]->get_analytics_id();
		}
	}

	$click_events_data = apply_filters( 'wpcode_pixel_click_events_data', $click_events_data );

	wp_enqueue_script( 'wpcode-pixel-frontend', plugins_url( 'build/click.js', WPCODE_PIXEL_FILE ), $asset['dependencies'], $asset['version'], true );
	wp_localize_script( 'wpcode-pixel-frontend', 'wpcodeClickEvents', $click_events_data );
}
