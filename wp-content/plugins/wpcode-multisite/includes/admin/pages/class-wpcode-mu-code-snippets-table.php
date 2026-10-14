<?php
/**
 * Table of snippets for the admin list.
 *
 * @package WPCode
 */

/**
 * Generate the table for the list of code snippets.
 */
class WPCode_Code_MU_Snippets_Table extends WPCode_Code_Snippets_Table {


	/**
	 * Load the snippet for the columns.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return WPCode_Snippet
	 */
	public function get_snippet( $post ) {
		return new WPCode_MU_Snippet( $post );
	}

	/**
	 * Override the admin url method in the network context.
	 *
	 * @param string $path The path to append to the admin URL.
	 *
	 * @return string
	 */
	public function admin_url( $path ) {
		return network_admin_url( $path );
	}
}
