<?php
/**
 * Headers & Footers admin page.
 *
 * @package WPCode
 */

/**
 * Class for the headers & footers admin page.
 */
class WPCode_MU_Admin_Page_Headers_Footers extends WPCode_Admin_Page_Headers_Footers_Pro {
	use WPCode_MU_Admin_Page;

	/**
	 * The page slug to be used when adding the submenu.
	 *
	 * @var string
	 */
	public $snippet_id = 0;

	/**
	 * Add message to the top of the page.
	 *
	 * @return void
	 */
	public function output_content() {
		echo '<p>' . esc_html__( 'Code added on this page will be inserted to all the sites in the network before the site-level code.', 'wpcode-multisite' ) . '</p>';
		parent::output_content();
	}

	/**
	 * If the form is submitted attempt to save the values.
	 *
	 * @return void
	 */
	public function submit_listener() {
		if ( ! isset( $_REQUEST[ $this->nonce_name ] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST[ $this->nonce_name ] ), $this->action ) ) {
			// Nonce is missing, so we're not even going to try.
			return;
		}

		if ( $this->can_edit && isset( $_REQUEST['ihaf_insert_header'] ) && isset( $_REQUEST['ihaf_insert_footer'] ) ) {
			// If they are not allowed to edit the page these should not be processed but we still allow them to save to disable the simple mode.
			update_site_option( 'wpcode_insert_header', $_REQUEST['ihaf_insert_header'] );
			update_site_option( 'wpcode_insert_footer', $_REQUEST['ihaf_insert_footer'] );
			update_site_option( 'wpcode_insert_body', isset( $_REQUEST['ihaf_insert_body'] ) ? $_REQUEST['ihaf_insert_body'] : '' );
		}

		$this->set_success_message( __( 'Settings Saved. Please don\'t forget to clear the site cache if you are using a cache plugin, so that the changes will be reflected for all users.', 'insert-headers-and-footers' ) );
	}


	/**
	 * Get the value of an option.
	 *
	 * @param string $option The option name.
	 *
	 * @return mixed
	 */
	public function get_option( $option ) {
		return get_site_option( 'wpcode_insert_' . $option );
	}

	/**
	 * Disable the revisions box for now at network level.
	 *
	 * @return void
	 */
	public function revisions_box() {}
}
