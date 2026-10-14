<?php
/**
 * Pro-specific settings admin page.
 *
 * @package WPCode
 */

/**
 * Pro-specific settings admin page.
 */
class WPCode_MU_Admin_Page_Settings extends WPCode_Admin_Page_Settings_Pro {

	use WPCode_MU_Admin_Page;

	/**
	 * Page-specific hooks.
	 */
	public function page_hooks() {
		parent::page_hooks();

		wpcode()->settings = new WPCode_MU_Settings();

		add_action( 'admin_init', array( $this, 'save_mu_settings' ), 9 );
	}

	/**
	 * Remove the settings page added in the core plugin.
	 *
	 * @return void
	 */
	public function register_network_page() {
	}

	/**
	 * Output the PHP disable setting.
	 *
	 * @return void
	 */
	public function php_setting() {
		parent::php_setting();
		?>
		<h2><?php esc_html_e( 'Network settings', 'wpcode-multisite' ); ?></h2>
		<?php
		$this->metabox_row(
			esc_html__( 'Hide WPCode on subsites', 'wpcode-multisite' ),
			$this->get_checkbox_toggle(
				boolval( wpcode()->settings->get_option( 'mu_hide_menu' ) ),
				'mu_hide_menu',
				esc_html__( 'Enabling this option will hide the admin menu for WPCode on all subsites and WPCode will only be available at network level.', 'wpcode-multisite' )
			),
			'wpcode_mu_hide_menu'
		);
		$this->metabox_row(
			esc_html__( 'Disable access for Administrators', 'wpcode-multisite' ),
			$this->get_checkbox_toggle(
				boolval( wpcode()->settings->get_option( 'mu_disable_administrators' ) ),
				'mu_disable_administrators',
				esc_html__( 'Prevent users with the regular administrator role from managing WPCode at site level, you can still use the role-based settings above for granular access.', 'wpcode-multisite' )
			),
			'wpcode_mu_disable_administrators'
		);

		wp_nonce_field( 'wpcode_settings_mu_save', 'wpcode_settings_mu_nonce' );
	}

	/**
	 * Callback to handle saving mu settings.
	 *
	 * @return void
	 */
	public function save_mu_settings() {
		if ( ! isset( $_POST['wpcode_settings_mu_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wpcode_settings_mu_nonce'] ), 'wpcode_settings_mu_save' ) ) {
			return;
		}

		if ( ! is_super_admin( get_current_user_id() ) ) {
			return;
		}

		if ( isset( $_POST['mu_hide_menu'] ) ) {
			wpcode()->settings->update_option( 'mu_hide_menu', true );
		} else {
			wpcode()->settings->update_option( 'mu_hide_menu', false );
		}

		if ( isset( $_POST['mu_disable_administrators'] ) ) {
			wpcode()->settings->update_option( 'mu_disable_administrators', true );
		} else {
			wpcode()->settings->update_option( 'mu_disable_administrators', false );
		}
		// Don't do a redirect + exit here to allow the original access request to be saved.
	}
}
