<?php
/**
 * Handles all the WPCode settings.
 *
 * @package WPCode_Multisite
 */

/**
 * Class WPCode_Settings.
 */
class WPCode_MU_Settings extends WPCode_Settings {

	/**
	 * The key used for storing settings in the db.
	 *
	 * @var string
	 */
	protected $settings_key = 'wpcode_mu_settings';

	/**
	 * Get an option by name with an optional default value.
	 *
	 * @param string $option_name The option name.
	 * @param mixed  $default The default value (optional).
	 *
	 * @return mixed
	 * @see get_option
	 */
	public function get_option( $option_name, $default = false ) {
		return apply_filters( "wpcode_mu_get_option_{$option_name}", parent::get_option( $option_name, $default ) );
	}

	/**
	 * Load the options from the db.
	 *
	 * @return array
	 */
	protected function load_options() {
		return get_site_option( $this->settings_key, array() );
	}

	/**
	 * Save the current options object to the db.
	 *
	 * @return void
	 */
	protected function save_options() {
		update_site_option( $this->settings_key, (array) $this->options );
	}
}
