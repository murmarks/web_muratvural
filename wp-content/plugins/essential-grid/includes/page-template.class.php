<?php
/**
 * @package   Essential_Grid
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.essential-grid.com/
 * @copyright 2025 ThemePunch
 * @since: 3.1.9.3
 */

if (!defined('ABSPATH')) exit();

class Essential_Grid_Page_Template
{
	const TMPL = '../public/views/esg-blank-page-template.php';
	
	/**
	 * @var Essential_Grid_Page_Template
	 */
	protected static $instance = null;

	/**
	 * @var array  Array of templates that this plugin tracks
	 */
	protected $templates = [];

	/**
	 * @return Essential_Grid_Page_Template
	 */
	public static function get_instance()
	{
		if ( is_null( static::$instance ) ) {
			static::$instance = new Essential_Grid_Page_Template();
		}

		return static::$instance;
	}

	protected function __construct()
	{
		$this->templates = [ self::TMPL => 'Essential Grid Blank Template' ];
		$this->_add_hooks();
	}

	protected function _add_hooks()
	{
		// Add a filter to the attributes metabox to inject a template into the cache.
		add_filter( 'page_attributes_dropdown_pages_args', [ $this, 'register_project_templates' ] );

		// Add a filter to the save post to inject out template into the page cache
		add_filter( 'wp_insert_post_data',  [ $this, 'register_project_templates' ] );

		// Add a filter to the template include to determine if the page has our 
		// template assigned and return path
		add_filter( 'template_include', [ $this, 'view_project_template' ] );

		// Fix for WP 4.7
		add_filter( 'theme_page_templates', [ $this, 'add_post_templates' ] );

		// Add filters to the attributes metabox to inject templates to all posts
		$types = get_post_types( [], 'objects' );
		foreach ( $types as $type => $values ) {
			if ( isset( $type ) ) add_filter( 'theme_' . $type . '_templates', [ $this, 'add_post_templates' ] );
		}

		add_action('essgrid_custom_meta_box_save_after', [ $this, 'save_post' ], 10, 3);
	}

	/**
	 * Adds our template to the page cache to trick WordPress
	 * into thinking the template file exists where it doesn't really exist.
	 *
	 * @param array $args
	 * @return array
	 */
	public function register_project_templates( $args ) {
		$cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());
		wp_cache_delete($cache_key , 'themes');
		
		$templates = wp_get_theme()->get_page_templates();
		if(empty($templates)) $templates = [];
		$templates = array_merge($templates, $this->templates);

		wp_cache_add($cache_key, $templates, 'themes', 1800);

		return $args;
	}

	/**
	 * Checks if the template is assigned to the page
	 * 
	 * @param string $template
	 * @return string
	 */
	public function view_project_template($template) {
		global $post;

		if ( is_search() ) return $template;
		if ( !isset( $post->ID ) ) return $template;
		
		$_wp_page_template = get_post_meta( $post->ID, '_wp_page_template', true );
		if ( !isset( $this->templates[ $_wp_page_template ] ) ) return $template;

		return plugin_dir_path(__FILE__) . $_wp_page_template;
	}

	/**
	 * @param array $post_templates
	 * @return array
	 */ 
	public function add_post_templates( $post_templates ) {
		return array_merge($post_templates, $this->templates);
	}

	/**
	 * @param array $grids
	 * @return int
	 */
	public function create_grid_page( $grids )
	{
		$new_page_id = 0;

		if( ! is_array( $grids ) || empty( $grids ) ) return $new_page_id;

		$content = '';
		$page_id = get_option( 'tp_eg_import_page_id', 1 );

		foreach( $grids as $grid ) {
			$content .= '<!-- wp:themepunch/essgrid -->'."\n";
			$content .= '<div class="wp-block-themepunch-essgrid essgrid" data-gridtitle="' . esc_attr( $grid['name'] ) . '">';
			$content .= '[ess_grid alias="' . $grid['handle'] . '"][/ess_grid]';
			$content .= '</div>'."\n";
			$content .= '<!-- /wp:themepunch/essgrid -->'."\n";
			
			// TODO: check if any template(s) add more than one grid on page
			// currently add only main grid
			break;
		}

		if( $content !== '') {
			$new_page_id = wp_insert_post([
				'post_title'    => wp_strip_all_tags('Essential Grid Page '.$page_id),
				'post_content'  => $content,
				'post_type'   	=> 'page',
				'post_status'   => 'draft',
				'page_template' => self::TMPL,
			]);

			if ( is_wp_error( $new_page_id ) ) $new_page_id = 0;

			update_option( 'tp_eg_import_page_id', ++$page_id );
		}

		return $new_page_id;
	}
	
	public function save_post( $metas, $post_id, $ajax)
	{
		if ( isset( $metas['tp_eg_page_bg_color'] ) ) {
			update_post_meta($post_id, 'tp_eg_page_bg_color', $metas['tp_eg_page_bg_color']);

			if ( isset( $metas['tp_eg_blank_page'] ) ) {
				update_post_meta($post_id, '_wp_page_template',self::TMPL);
			} else {
				update_post_meta($post_id, '_wp_page_template','');
			}
		}
	}

}
