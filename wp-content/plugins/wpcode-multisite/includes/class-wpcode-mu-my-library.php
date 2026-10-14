<?php
/**
 * Class used to handle requests to the personal library.
 *
 * @package WPCode
 */

/**
 * Class WPCode MU My Library.
 */
class WPCode_MU_My_Library extends WPCode_My_Library {

	/**
	 * Key for storing snippets in the cache.
	 *
	 * @var string
	 */
	protected $cache_key = 'mu-my-snippets';

	/**
	 * Library endpoint for loading all data.
	 *
	 * @var string
	 */
	protected $all_snippets_endpoint = 'mysnippets';

	/**
	 * Key for transient used to store already installed snippets.
	 *
	 * @var string
	 */
	protected $used_snippets_transient_key = 'wpcode_mu_used_my_cloud_snippets';
}
