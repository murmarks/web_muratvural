<?php

add_action( 'plugins_loaded', 'wpcode_mu_includes_plugins_loaded', 9 );

/**
 * Include the multisite components that need to be loaded later.
 *
 * @return void
 */
function wpcode_mu_includes_plugins_loaded() {
	require_once WPCODE_MULTISITE_PLUGIN_PATH . 'includes/conditional-logic/class-wpcode-conditional-network.php';
}
