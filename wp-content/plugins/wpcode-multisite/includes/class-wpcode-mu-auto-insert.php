<?php
/**
 * This class takes care of inserting network snippets on all sites.
 *
 * @package WPCode_Multisite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WPCode_MU_Auto_Insert class.
 */
class WPCode_MU_Auto_Insert {

	/**
	 * WPCode_MU_Auto_Insert constructor.
	 */
	public function __construct() {
		add_filter( 'wpcode_get_snippets_for_location', array( $this, 'insert_network_snippets' ), 10, 2 );
	}

	/**
	 * Insert network snippets into the list of snippets.
	 *
	 * @param array  $snippets The snippets.
	 * @param string $location The location.
	 *
	 * @return array
	 */
	public function insert_network_snippets( $snippets, $location ) {
		$mu_snippets = wpcode_multisite()->cache->get_cached_snippets();

		if ( ! empty( $mu_snippets[ $location ] ) ) {
			$mu_snippets_for_location = wpcode()->conditional_logic->check_snippets_conditions( $mu_snippets[ $location ] );
			$snippets                 = array_merge( $snippets, $mu_snippets_for_location );

			usort( $snippets, array( wpcode()->cache, 'priority_order' ) );
		}

		return $snippets;
	}
}
