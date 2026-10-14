<?php
/**
 * Network-specific snippet cache.
 *
 * @package WPCode_Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCode_MU_Snippet_Cache.
 */
class WPCode_MU_Snippet_Cache extends WPCode_Snippet_Cache {

	/**
	 * The option name used for storing data in the db.
	 *
	 * @var string
	 */
	protected $option_name = 'wpcode_mu_snippets';

	/**
	 * Update the option with the new data.
	 *
	 * @param array $data_for_cache The data to store in the option.
	 *
	 * @return bool
	 */
	public function update_option( $data_for_cache ) {
		return update_site_option( $this->option_name, $data_for_cache );
	}

	/**
	 * Get the option from the db.
	 *
	 * @return array
	 */
	public function get_option() {
		return (array) get_site_option( $this->option_name, array() );
	}

	/**
	 * Load a snippet by id, WP_Post or array.
	 *
	 * @param array|int|WP_Post $snippet_data Load a snippet by id, WP_Post or array.
	 *
	 * @return WPCode_MU_Snippet
	 */
	public function load_snippet( $snippet_data ) {
		return new WPCode_MU_Snippet( $snippet_data );
	}
}
