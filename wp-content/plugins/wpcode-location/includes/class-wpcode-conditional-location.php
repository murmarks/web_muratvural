<?php
/**
 * Class that handles conditional logic based on location.
 *
 * @package WPCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The WPCode_Conditional_Location class.
 */
class WPCode_Conditional_Location extends WPCode_Conditional_Type {

	/**
	 * The location provider.
	 *
	 * @var WPCode_Geo_Location
	 */
	public $location_provider;

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'location';

	/**
	 * The category of the type.
	 *
	 * @var string
	 */
	public $category = 'who';

	/**
	 * Hooks specific to this type.
	 */
	public function hooks() {
		include_once WPCODE_LOCATION_PLUGIN_PATH . 'includes/class-wpcode-geo-location.php';
	}

	/**
	 * Set the type options for the admin mainly.
	 *
	 * @return void
	 */
	public function load_type_options() {
		$this->options = array(
			'country'   => array(
				'label'           => __( 'Country', 'wpcode-location' ),
				'description'     => __( 'Limit loading the snippet based on the visitor\'s country.', 'wpcode-location' ),
				'type'            => 'select',
				'options'         => $this->get_countries(),
				'multiple'        => true,
				'callback'        => array( $this, 'get_user_country' ),
				'operator_labels' => array(
					'='  => __( 'Is one of', 'wpcode-location' ),
					'!=' => __( 'Is not one of', 'wpcode-location' ),
				),
			),
			'continent' => array(
				'label'           => __( 'Continent', 'wpcode-location' ),
				'description'     => __( 'Target entire continents with ease.', 'wpcode-location' ),
				'type'            => 'select',
				'options'         => $this->get_continents(),
				'multiple'        => true,
				'callback'        => array( $this, 'get_user_continent' ),
				'operator_labels' => array(
					'='  => __( 'Is one of', 'wpcode-location' ),
					'!=' => __( 'Is not one of', 'wpcode-location' ),
				),
			),
		);
		if ( is_admin() ) {
			if ( ! wpcode()->license->get() ) {
				foreach ( $this->options as $key => $options ) {
					$this->options[ $key ]['upgrade'] = array(
						'title'  => __( 'Location Rules are a Pro Feature', 'wpcode-location' ),
						'text'   => __( 'Please add your license key in the Settings Panel to unlock all pro features.', 'wpcode-location' ),
						'link'   => add_query_arg(
							array(
								'page' => 'wpcode-settings',
							),
							admin_url( 'admin.php' )
						),
						'button' => __( 'Add License Key Now', 'wpcode-location' ),
					);
				}
			}
		}
	}

	/**
	 * Get the countries.
	 *
	 * @return array
	 */
	public function get_countries() {

		$countries         = WPCode_Geo_Location::get_countries();
		$countries_options = array();

		foreach ( $countries as $country_code => $country_name ) {
			$countries_options[] = array(
				'label' => $country_name,
				'value' => $country_code,
			);
		}

		return $countries_options;
	}

	/**
	 * Get the continents for the admin options.
	 *
	 * @return array
	 */
	public function get_continents() {
		$continents_options = array();
		$continents         = WPCode_Geo_Location::get_continents();
		foreach ( $continents as $continent_code => $continent ) {
			$continents_options[] = array(
				'label' => $continent['name'],
				'value' => $continent_code,
			);
		}

		return $continents_options;
	}

	/**
	 * Get the user's country.
	 *
	 * @return string
	 */
	public function get_user_country() {
		$country = $this->get_user_location( 'country' );;

		return $country;
	}

	/**
	 * Get the user's location.
	 *
	 * @param string $type The type of location to get.
	 *
	 * @return string
	 */
	public function get_user_location( $type ) {
		$location = $this->get_location_provider()->get_location();

		if ( ! isset( $location[ $type ] ) ) {
			return '';
		}

		return $location[ $type ];

	}

	/**
	 * Get the location provider.
	 *
	 * @return WPCode_Geo_Location
	 */
	public function get_location_provider() {
		if ( ! isset( $this->location_provider ) ) {
			$this->location_provider = new WPCode_Geo_Location();
		}

		return $this->location_provider;
	}

	/**
	 * Get the user's continent.
	 *
	 * @return string
	 */
	public function get_user_continent() {
		$country = $this->get_user_location( 'country' );

		return $this->get_location_provider()->get_continent( $country );
	}

	/**
	 * Set the translatable label.
	 *
	 * @return void
	 */
	protected function set_label() {
		$this->label = __( 'Location', 'wpcode-location' );
	}
}

new WPCode_Conditional_Location();
