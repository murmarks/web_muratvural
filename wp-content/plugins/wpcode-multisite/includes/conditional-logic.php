<?php
/**
 * Filter out site-specific conditional logic rules in the multisite context to avoid confusion.
 *
 * @package WPCode_Multisite
 */

add_filter( 'wpcode_conditional_logic_admin_options', 'wpcode_mu_network_disable_site_specific_cl_options', 20 );
add_filter( 'wpcode_conditional_logic_admin_options', 'wpcode_mu_network_move_site_options_top', 21 );

/**
 * Filter out site-specific conditional logic rules in the multisite context to avoid confusion.
 *
 * @param array $options The conditional logic options.
 *
 * @return array
 */
function wpcode_mu_network_disable_site_specific_cl_options( $options ) {
	// Only run this in the network admin.
	if ( ! wpcode_mu_is_network() ) {
		return $options;
	}

	$site_specific_options = array(
		'page'        => array(
			'taxonomy_term',
			'post_id',
		),
		'woocommerce' => array(
			'wc_cart',
		),
		'memberpress' => array(
			'memberpress_user',
		),
		'snippet'     => array(
			'snippet_loaded',
		),
	);

	foreach ( $site_specific_options as $logic_type => $logic_options ) {
		foreach ( $logic_options as $option ) {
			if ( isset( $options[ $logic_type ]['options'][ $option ] ) ) {
				$options[ $logic_type ]['options'][ $option ]['upgrade'] = array(
					// Translators: %s is the name of the conditional logic option type.
					'title'  => sprintf( __( '%s Rules are not available', 'wpcode-multisite' ), $options[ $logic_type ]['options'][ $option ]['label'] ),
					'text'   => __( 'These type of rules rely on content that is specific to a subsite and cannot be used at network level. Please create a snippet at subsite level to use site-specific conditional logic rules.', 'wpcode-multisite' ),
					'link'   => false,
					'button' => 'OK',
				);
			}
		}
	}

	return $options;
}

/**
 * Move the network-specific conditional logic rules to the top of the list.
 *
 * @param array $options The conditional logic options.
 *
 * @return array
 */
function wpcode_mu_network_move_site_options_top( $options ) {
	// Let's move the options under the "site" type to the top of the list.
	if ( isset( $options['network'] ) ) {
		$site_options = $options['network'];
		unset( $options['network'] );
		$options = array_merge( array( 'network' => $site_options ), $options );
	}

	return $options;
}
