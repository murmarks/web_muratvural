<?php
/**
 * Class that handles conditional logic by site in a network context.
 *
 * @package WPCode_Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WPCode_Conditional_Site class.
 */
class WPCode_Conditional_Network extends WPCode_Conditional_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'network';

	/**
	 * The category of this type.
	 *
	 * @var string
	 */
	public $category = 'where';

	/**
	 * Set the translatable label.
	 *
	 * @return void
	 */
	protected function set_label() {
		$this->label = __( 'Multisite Network', 'wpcode-multisite' );
	}

	/**
	 * Set the type options for the admin mainly.
	 *
	 * @return void
	 */
	public function load_type_options() {
		$this->options = array(
			'site_id' => array(
				'label'           => __( 'Site', 'wpcode-multisite' ),
				'description'     => __( 'Check on which site within a multisite installation the code will be applied.', 'wpcode-multisite' ),
				'type'            => 'ajax',
				'options'         => 'wpcode_search_sites',
				'callback'        => array( $this, 'get_current_site' ),
				'labels_callback' => array( $this, 'get_sites_labels' ),
				'multiple'        => true,
				'operator_labels' => array(
					'='  => __( 'Is one of', 'wpcode-multisite' ),
					'!=' => __( 'Is not one of', 'wpcode-multisite' ),
				),
			),
		);
		if ( is_admin() ) {
			if ( ! wpcode()->license->get() ) {
				$this->options['site_id']['upgrade'] = array(
					'title'  => __( 'Network Site Rules are a Pro Feature', 'wpcode-multisite' ),
					'text'   => __( 'Please add your license key in the Settings Panel to unlock all pro features.', 'wpcode-multisite' ),
					'link'   => add_query_arg(
						array(
							'page' => 'wpcode-settings',
						),
						network_admin_url( 'admin.php' )
					),
					'button' => __( 'Add License Key Now', 'wpcode-multisite' ),
				);
			}
		}
	}

	/**
	 * Get the id of the current site.
	 *
	 * @param WPCode_Snippet $loaded_snippet The snippet we are evaluating the rules for.
	 *
	 * @return int
	 */
	public function get_current_site( $loaded_snippet ) {
		return get_current_blog_id();
	}

	/**
	 * Get the label to display selected site in the admin.
	 *
	 * @param array $sites_ids The site ids.
	 *
	 * @return array
	 */
	public function get_sites_labels( $sites_ids ) {

		$labels = array();

		foreach ( $sites_ids as $site_id ) {
			$site     = get_blog_details( $site_id );
			$labels[] = array(
				'value' => $site_id,
				'label' => $site->blogname,
			);
		}

		return $labels;
	}
}

new WPCode_Conditional_Network();
