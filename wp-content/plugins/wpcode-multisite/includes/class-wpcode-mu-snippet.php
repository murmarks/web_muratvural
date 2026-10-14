<?php
/**
 * WPCode snippets stored in a multisite context.
 *
 * @package WPCode-Multisite
 */

/**
 * Class WPCode_MU_Snippet.
 */
class WPCode_MU_Snippet extends WPCode_Snippet {

	/**
	 * The post type for the snippet.
	 *
	 * @var string
	 */
	public $post_type = 'wpcode_mu_snippet';

	public function rebuild_cache() {
		wpcode_multisite()->cache->cache_all_loaded_snippets();
	}
}
