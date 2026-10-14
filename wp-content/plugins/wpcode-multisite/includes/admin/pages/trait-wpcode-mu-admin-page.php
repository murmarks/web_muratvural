<?php
/**
 * WPCode Multisite Admin Page trait.
 *
 * @package WPCode-Multisite
 */

/**
 * WPCode Multisite Admin Page trait.
 */
trait WPCode_MU_Admin_Page {

	/**
	 * Network hooks.
	 *
	 * @return void
	 */
	public function network_hooks() {
		add_action( 'network_admin_menu', array( $this, 'add_page' ), 15 );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Only load if we are actually on the desired page.
		if ( $this->page_slug !== $page ) {
			return;
		}
		add_action( 'wpcode_mu_admin_page', array( $this, 'output' ) );
		add_action( 'wpcode_mu_admin_page', array( $this, 'output_footer' ) );
		add_action( 'admin_head', array( $this, 'inline_styles' ) );
	}

	/**
	 * Page-specific hooks.
	 *
	 * @var string
	 */
	public function hooks() {
		$this->network_hooks();
		parent::hooks();
		remove_action( 'admin_menu', array( wpcode()->admin_page_loader, 'add_page' ) );
	}

	/**
	 * Add a page to the menu in the multisite context, this overrides the parent method.
	 *
	 * @return void
	 * @see WPCode_Admin_Page::add_page()
	 *
	 */
	public function add_page() {
		add_submenu_page(
			'wpcode',
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->page_slug,
			array(
				wpcode_multisite()->admin_page_loader,
				'admin_menu_page',
			)
		);
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

	/**
	 * Add inline styles in network admin pages.
	 *
	 * @return void
	 */
	public function inline_styles() {
		?>
		<style>
			.wpcode-toggle-testing-mode-wrap {
				display: none;
			}
		</style>
		<?php
	}
}
