<?php
/**
 * Load snippets from the wpcode.com snippet library.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Library.
 */
class WPCode_MU_Library extends WPCode_Library {

	/**
	 * Key for transient used to store already installed snippets.
	 *
	 * @var string
	 */
	protected $used_snippets_transient_key = 'wpcode_mu_used_library_snippets';

	/**
	 * Get the URL to edit a snippet.
	 *
	 * @param int $snippet_id The snippet id.
	 *
	 * @return string
	 */
	public function get_edit_snippet_url( $snippet_id ) {
		return add_query_arg(
			array(
				'page'       => 'wpcode-snippet-manager',
				'snippet_id' => absint( $snippet_id ),
			),
			network_admin_url( 'admin.php' )
		);
	}

	/**
	 * Get a direct link to install a snippet by its library URL.
	 *
	 * @param int $snippet_library_id The snippet ID on the WPCode library.
	 *
	 * @return string
	 */
	public function get_install_snippet_url( $snippet_library_id ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'snippet_library_id' => absint( $snippet_library_id ),
					'page'               => 'wpcode-library',
				),
				network_admin_url( 'admin.php' )
			),
			'wpcode_add_from_library'
		);
	}
}
