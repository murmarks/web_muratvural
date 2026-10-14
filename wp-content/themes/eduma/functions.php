<?php
/**
 * thim functions and definitions
 *
 * @package thim
 */
update_site_option( 'thim_core_product_registration_themes', [ 'eduma' => [ 'site_key' => '**********' ] ] );
add_filter( 'thim_core_url_download_private_plugin', function( $source, $plugin ) {
return get_template_directory_uri() . '/plugins/' . $plugin->get_slug() . '.zip';
}, 20, 2 );
define( 'THIM_DIR', trailingslashit( get_template_directory() ) );
define( 'THIM_URI', trailingslashit( get_template_directory_uri() ) );
const THIM_THEME_VERSION = '5.4.2';
/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 640; /* pixels */
}
/**
 * Translation ready
 */

load_theme_textdomain( 'eduma', get_template_directory() . '/languages' );

function thim_eduma_get_current_url() {
	$schema = is_ssl() ? 'https://' : 'http://';

	return $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

if ( ! function_exists( 'thim_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function thim_setup() {

		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on thim, use a find and replace
		 * to change 'eduma' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'eduma', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary' => esc_html__( 'Primary Menu', 'eduma' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5', array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
		/* Add WooCommerce support */
		add_theme_support( 'woocommerce' );
		add_theme_support( 'thim-core' );

		//		add_theme_support( 'eduma-demo-data' );
		add_theme_support( 'thim-full-widgets' );
		/*
		* Enable support for Post Formats.
		* See http://codex.wordpress.org/Post_Formats
		*/
		add_theme_support(
			'post-formats', array(
				'aside',
				'image',
				'video',
				//				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );

		// Editor color palette.
		add_theme_support(
			'editor-color-palette', array(
				array(
					'name'  => esc_html__( 'Primary Color', 'eduma' ),
					'slug'  => 'primary',
					'color' => get_theme_mod( 'thim_body_primary_color', '#ffb606' ),
				),
				array(
					'name'  => esc_html__( 'Title Color', 'eduma' ),
					'slug'  => 'title',
					'color' => get_theme_mod( 'thim_font_title_color', '#333' ),
				),
				array(
					'name'  => esc_html__( 'Sub Title Color', 'eduma' ),
					'slug'  => 'sub-title',
					'color' => '#999',
				),
				array(
					'name'  => esc_html__( 'Border Color', 'eduma' ),
					'slug'  => 'border-input',
					'color' => '#ddd',
				),
			)
		);

		// Add custom editor font sizes.
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name'      => __( 'Small', 'eduma' ),
					'shortName' => __( 'S', 'eduma' ),
					'size'      => 13,
					'slug'      => 'small',
				),
				array(
					'name'      => __( 'Normal', 'eduma' ),
					'shortName' => __( 'M', 'eduma' ),
					'size'      => 15,
					'slug'      => 'normal',
				),
				array(
					'name'      => __( 'Large', 'eduma' ),
					'shortName' => __( 'L', 'eduma' ),
					'size'      => 28,
					'slug'      => 'large',
				),
				array(
					'name'      => __( 'Huge', 'eduma' ),
					'shortName' => __( 'XL', 'eduma' ),
					'size'      => 36,
					'slug'      => 'huge',
				),
			)
		);
		// don't enqueue file css when save customizer
		add_filter( 'thim_core_enqueue_file_css_customizer', '__return_false' );
		// remove wp_global_styles_render_svg_filters
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		add_filter( 'thim_prefix_folder_download_data_demo', function () {
			return 'eduma';
		} );
	}
endif; // thim_setup
add_action( 'after_setup_theme', 'thim_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
if ( ! function_exists( 'thim_widgets_inits' ) ) {
	function thim_widgets_inits() {
		register_sidebar(
			array(
				'name'          => esc_html__( 'Sidebar', 'eduma' ),
				'id'            => 'sidebar',
				'description'   => esc_html__( 'Right Sidebar', 'eduma' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);
		if ( get_theme_mod( 'thim_toolbar_show', 'true' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Toolbar', 'eduma' ),
					'id'            => 'toolbar',
					'description'   => esc_html__( 'Toolbar Header', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}
		register_sidebar(
			array(
				'name'          => esc_html__( 'Menu Right', 'eduma' ),
				'id'            => 'menu_right',
				'description'   => esc_html__( 'Menu Right', 'eduma' ),
				'before_widget' => '<li id="%1$s" class="widget %2$s">',
				'after_widget'  => '</li>',
				'before_title'  => '<h4>',
				'after_title'   => '</h4>',
			)
		);
		if ( 'header_v2' == get_theme_mod( 'thim_header_style', 'header_v1' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Menu Top', 'eduma' ),
					'id'            => 'menu_top',
					'description'   => esc_html__( 'Menu top only display with header version 2', 'eduma' ),
					'before_widget' => '<li id="%1$s" class="widget %2$s">',
					'after_widget'  => '</li>',
					'before_title'  => '<h4>',
					'after_title'   => '</h4>',
				)
			);
		}

		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer Top', 'eduma' ),
				'id'            => 'footer_top',
				'description'   => esc_html__( 'Footer Top Sidebar', 'eduma' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s footer_bottom_widget">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);

		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer', 'eduma' ),
				'id'            => 'footer',
				'description'   => esc_html__( 'Footer Sidebar', 'eduma' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s footer_widget">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);

		if ( 'new-1' != get_theme_mod( 'thim_layout_content_page', 'normal' ) || 'header_v4' != get_theme_mod( 'thim_header_style', 'header_v1' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Footer Bottom', 'eduma' ),
					'id'            => 'footer_bottom',
					'description'   => esc_html__( 'Footer Bottom Sidebar', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s footer_bottom_widget">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}

		if ( get_theme_mod( 'thim_copyright_show', 'true' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Copyright', 'eduma' ),
					'id'            => 'copyright',
					'description'   => esc_html__( 'Copyright', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}

		if ( class_exists( 'WooCommerce' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Sidebar Shop', 'eduma' ),
					'id'            => 'sidebar_shop',
					'description'   => esc_html__( 'Sidebar Shop', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}

		if ( class_exists( 'LearnPress' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Sidebar Courses', 'eduma' ),
					'id'            => 'sidebar_courses',
					'description'   => esc_html__( 'Sidebar Courses', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}

		if ( class_exists( 'TP_Event' ) || class_exists( 'WPEMS' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Sidebar Events', 'eduma' ),
					'id'            => 'sidebar_events',
					'description'   => esc_html__( 'Sidebar Events', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}
		if ( 'header_v3' == get_theme_mod( 'thim_header_style', 'header_v1' ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Header', 'eduma' ),
					'id'            => 'header',
					'description'   => esc_html__( 'Sidebar display on header version 3', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s footer_bottom_widget">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}
		/**
		 * Feature create sidebar in wp-admin.
		 * Do not remove this.
		 */
		$sidebars = apply_filters( 'thim_core_list_sidebar', array() );
		if ( count( $sidebars ) > 0 ) {
			foreach ( $sidebars as $sidebar ) {
				$new_sidebar = array(
					'name'          => $sidebar['name'],
					'id'            => $sidebar['id'],
					'description'   => esc_html__( 'Custom widgets area.', 'eduma' ),
					'before_widget' => '<aside id="%1$s" class="widget %2$s footer_bottom_widget">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				);

				register_sidebar( $new_sidebar );
			}
		}
	}
}

add_action( 'widgets_init', 'thim_widgets_inits' );

/**
 * Enqueue styles.
 */
// remove font-awesome in elementor
add_action(
	'elementor/frontend/after_register_styles', function () {
	foreach ( [ 'solid', 'regular', 'brands' ] as $style ) {
		wp_deregister_style( 'elementor-icons-fa-' . $style );
		wp_deregister_style( 'font-awesome' );
	}
}, 20
);

/**
 * thim_get_option_var_css
 */
function thim_get_theme_option( $name = '', $value_default = '' ) {
	$data = get_theme_mods();
	if ( isset( $data[$name] ) ) {
		return $data[$name];
	} else {
		return $value_default;
	}
}

function thim_get_option_var_css() {
	$css           = '';
	$theme_options = array(
		// hearder
		'thim_body_primary_color'   => '#ffb606',
		'thim_body_secondary_color' => '#4caf50',
		'thim_button_text_color'    => '#333',
		'thim_button_hover_color'   => '#e6a303',
		'thim_border_color'         => '#eee',
		'top_info_course'           => array(
			'background_color' => '#273044',
			'text_color'       => '#fff',
		),

		'thim_footer_font_title'                 => array(
			'variant'        => get_theme_mod( 'thim_footer_font_title_font_weight', 700 ),
			'font-size'      => '14px',
			'line-height'    => '40px',
			'text-transform' => 'uppercase',
		),
		'thim_top_heading_title_align'           => 'left',
		'thim_top_heading_title_font'            => array(
			'size-desktop'   => '48px',
			'size-mobile'    => '35px',
			'text-transform' => 'uppercase',
			'weight'         => 'bold',
		),
		'thim_top_heading_padding'               => array(
			'top'           => '90px',
			'bottom'        => '90px',
			'top-mobile'    => '50px',
			'bottom-mobile' => '50px',
		),
		'thim_breacrumb_font_size'               => '1em',
		'thim_breacrumb_color'                   => '#666',
		'thim_breacrumb_bg_color'                => '',
		'thim_breacrumb_border_color'            => '',
		'thim_course_price_color'                => '#f24c0a',
		// Thim Logo
		'thim_width_logo'                        => '155px',
		// Thim ToolBar
		'thim_bg_color_toolbar'                  => '#111',
		'thim_text_color_toolbar'                => '#ababab',
		'thim_link_color_toolbar'                => '#fff',
		'thim_link_hover_color_toolbar'          => '#fff',
		'thim_toolbar'                           => array(
			'variant'        => get_theme_mod( 'thim_toolbar_font_weight', 600 ),
			'font-size'      => '12px',
			'line-height'    => '30px',
			'text-transform' => 'none',
		),
		//		'thim_toolbar_font_weight'               => '600',
		'thim_toolbar_border_type'               => 'dashed',
		'thim_toolbar_border_size'               => '1px',
		'thim_link_color_toolbar_border_button'  => '#ddd',
		// Main Menu
		'thim_bg_main_menu_color'                => 'rgba(255,255,255,0)',
		'thim_main_menu'                         => array(
			'variant'        => get_theme_mod( 'thim_main_menu_font_weight', 600 ),
			'font-size'      => '14px',
			'line-height'    => '1.3em',
			'text-transform' => 'uppercase',
		),
		'thim_main_menu_font_weight'             => '600',
		'thim_main_menu_text_color'              => '#fff',
		'thim_main_menu_text_hover_color'        => '#fff',
		// Sticky Menu
		'thim_sticky_bg_main_menu_color'         => '#fff',
		'thim_sticky_main_menu_text_color'       => '#333',
		'thim_sticky_main_menu_text_hover_color' => '#333',
		// Sub Menu
		'thim_sub_menu_bg_color'                 => '#fff',
		'thim_sub_menu_border_color'             => 'rgba(43,43,43,0)',
		'thim_sub_menu_text_color'               => '#999',
		'thim_sub_menu_text_color_hover'         => '#333',

		// Mobile Menu
		'thim_bg_mobile_menu_color'              => '#232323',
		'thim_mobile_menu_text_color'            => '#777',
		'thim_mobile_menu_text_hover_color'      => '#fff',
		// Footer
		'thim_footer_font_size'                  => '14px',

		'thim_bg_switch_layout_style'      => '#f5f5f5',
		'thim_padding_switch_layout_style' => '10px',

		'thim_font_body'             => array(
			'font-family' => 'Roboto',
			'variant'     => '400',
			'font-size'   => '15px',
			'line-height' => '1.7em',
			'color'       => '#666666',
		),
		'thim_font_title'            => array(
			'font-family' => 'Roboto Slab',
			'color'       => '#333333',
			'variant'     => '700',
		),
		'thim_font_h1'               => array(
			'font-size'      => '36px',
			'line-height'    => '1.6em',
			'text-transform' => 'none',
		),
		'thim_font_h2'               => array(
			'font-size'      => '28px',
			'line-height'    => '1.6em',
			'text-transform' => 'none',
		),
		'thim_font_h3'               => array(
			'font-size'      => '24px',
			'line-height'    => '1.6em',
			'text-transform' => 'none',
		),
		'thim_font_h4'               => array(
			'font-size'      => '18px',
			'line-height'    => '1.6em',
			'text-transform' => 'none',
		),
		'thim_font_h5'               => array(
			'font-size'      => '16px',
			'line-height'    => '1.6em',
			'text-transform' => 'none',
		),
		'thim_font_h6'               => array(
			'font-size'      => '16px',
			'line-height'    => '1.4em',
			'text-transform' => 'none',
		),
		'thim_font_title_sidebar'    => array(
			'font-size'      => '18px',
			'line-height'    => '1.4em',
			'text-transform' => 'uppercase',
		),
		'thim_font_button'           => array(
			'variant'        => 'regular',
			'font-size'      => '13px',
			'line-height'    => '1.6em',
			'text-transform' => 'uppercase',
		),
		'thim_preload_style'         => array(
			'background' => '#fff',
			'color'      => '#333333',
		),
		'thim_footer_bg_color'       => '#111',
		'thim_footer_color'          => array(
			'title' => '#ffffff',
			'text'  => '#ffffff',
			'link'  => '#ffffff',
			'hover' => '#ffb606',
		),
		'thim_padding_content'       => array(
			'pdtop-desktop'    => '60px',
			'pdbottom-desktop' => '60px',
			'pdtop-mobile'     => '40px',
			'pdbottom-mobile'  => '40px'
		),
		'thim_content_course_border' => false,
		'thim_border_radius'         => array(
			'item'     => '4px',
			'item-big' => '10px',
			'button'   => '4px',
		),

		'thim_copyright_bg_color'     => '#111',
		'thim_copyright_text_color'   => '#999',
		'thim_copyright_border_color' => '#222',

		'thim_bg_pattern'            => THIM_URI . 'images/patterns/pattern1.png',
		'thim_bg_upload'             => '',
		'thim_bg_repeat'             => 'no-repeat',
		'thim_bg_position'           => 'center',
		'thim_bg_attachment'         => 'inherit',
		'thim_bg_size'               => 'inherit',
		'thim_footer_background_img' => '',
		'thim_footer_bg_repeat'      => 'no-repeat',
		'thim_footer_bg_position'    => 'center',
		'thim_footer_bg_size'        => 'inherit',
		'thim_footer_bg_attachment'  => 'inherit',
		'thim_body_bg_color'         => '#fff'
	);

	if ( get_theme_mod( 'thim_content_course_border', false ) == false ) {
		unset( $theme_options['thim_border_radius'] );
	}

	foreach ( $theme_options as $key => $val ) {
		$val_opt = thim_get_theme_option( $key, $val );
		if ( is_array( $val_opt ) ) {
			// get options default
			foreach ( $val as $attr => $value ) {
				$val_ar = isset( $val_opt[$attr] ) ? $val_opt[$attr] : $value;
				$css    .= '--' . str_replace( '_', '-', $key ) . '-' . $attr . ':' . $val_ar . ';';
			}
		} else {
			if ( $val_opt != '' ) {
				if ( in_array( $key, array( 'thim_bg_pattern', 'thim_footer_background_img', 'thim_bg_upload' ) ) ) {
					$val_opt = 'url("' . $val_opt . '")';
				}

				$css .= '--' . str_replace( '_', '-', $key ) . ':' . $val_opt . ';';
				// convert primary color to rga
				if ( $key == 'thim_main_menu_text_color' || $key == 'thim_sticky_main_menu_text_color' || $key == 'thim_mobile_menu_text_color' ) {
					if ( $val_opt[0] == '#' ) {
						list( $r, $g, $b ) = sscanf( $val_opt, "#%02x%02x%02x" );
						$css .= '--' . $key . '_rgb: ' . $r . ',' . $g . ',' . $b . ';';
					} else {
						$rgba    = explode( ',', $val_opt );
						$rgba_rr = explode( '(', $rgba['0'] );
						$css     .= '--' . $key . '_rgb: ' . $rgba_rr['1'] . ',' . $rgba['1'] . ',' . $rgba['2'] . ';';
					}
				}
			}
		}

		// get data for on type is image
	}

	return apply_filters( 'thim_get_var_css_customizer', $css );
}

function thim_custom_color_header_single_page() {
	$css = '';
	if ( is_page() || is_single() ) {
		$bg_main_menu_color         = get_post_meta( get_the_ID(), 'thim_mtb_bg_main_menu_color', true );
		$main_menu_text_color       = get_post_meta( get_the_ID(), 'thim_mtb_main_menu_text_color', true );
		$main_menu_text_hover_color = get_post_meta( get_the_ID(), 'thim_mtb_main_menu_text_hover_color', true );
		$var_css                    = $bg_main_menu_color ? '--thim-bg-main-menu-color:' . $bg_main_menu_color . ';' : '';
		$var_css                    .= $bg_main_menu_color ? '--thim-main-menu-text-color:' . $main_menu_text_color . ';' : '';
		$var_css                    .= $bg_main_menu_color ? '--thim-main-menu-text-hover-color:' . $main_menu_text_hover_color . ';' : '';
		$css                        .= $var_css ? '#masthead{' . $var_css . '}' : '';
	}

	if ( thim_lp_style_single_course() == 'layout_style_3' ) {
		$top_info_color    = get_post_meta( get_the_ID(), 'thim_mtb_text_top_info_course', true );
		$bg_top_info_color = get_post_meta( get_the_ID(), 'thim_mtb_bg_top_info_course', true );
		$var_css           = $bg_top_info_color ? '--top-info-course-background_color:' . $bg_top_info_color . ';' : '';
		$var_css           .= $top_info_color ? '--top-info-course-text_color:' . $top_info_color . ';' : '';
		$css               .= $var_css ? '.postid-' . get_the_ID() . '.single-lp_course .course-info-top{' . $var_css . '}' : '';
	}

	return $css;
}

if ( ! function_exists( 'thim_styles' ) ) {
	function thim_styles() {
		$v_asset = THIM_THEME_VERSION;
		if ( class_exists( 'LP_Debug' ) && LP_Debug::is_debug() ) {
			$v_asset = uniqid();
		}
		wp_deregister_style( 'font-awesome' );
		if ( ! class_exists( 'TP' ) ) {
			wp_enqueue_style( 'thim-fontgoogle-default', 'https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap', array(), THIM_THEME_VERSION );
		}
		// deregister font awesome in LP
		wp_enqueue_style( 'font-awesome-5-all', THIM_URI . 'assets/css/all.min.css', array(), THIM_THEME_VERSION );
		wp_enqueue_style( 'font-awesome-4-shim', THIM_URI . 'assets/css/v4-shims.min.css', array(), THIM_THEME_VERSION );

		wp_register_style( 'ionicons', THIM_URI . 'assets/css/ionicons.min.css' );
		wp_register_style( 'font-pe-icon-7', THIM_URI . 'assets/css/font-pe-icon-7.css' );
		wp_register_style( 'flaticon', THIM_URI . 'assets/css/flaticon.css' );
		wp_deregister_style( 'thim-ekit-font-icon' );

		wp_register_style( 'thim-portfolio', THIM_URI . 'assets/css/libs/portfolio.css', array(), THIM_THEME_VERSION );

		wp_enqueue_style( 'elementor-icons-thim-ekits-fonts', THIM_URI . 'assets/css/thim-ekits-icons.min.css', array(), THIM_THEME_VERSION );

		wp_enqueue_style( 'thim-style', get_stylesheet_uri(), array(), $v_asset );

		// css inline
		$css_line = ':root{' . preg_replace(
				array( '/\s*(\w)\s*{\s*/', '/\s*(\S*:)(\s*)([^;]*)(\s|\n)*;(\n|\s)*/', '/\n/', '/\s*}\s*/' ),
				array( '$1{ ', '$1$3;', "", '} ' ), thim_get_option_var_css()
			) . '}';
		$css_line .= trim( thim_custom_color_header_single_page() );
		$css_line .= apply_filters( 'thim_custom_internal_css', '' );
		wp_add_inline_style(
			'thim-style', $css_line
		);

		// fix font icon for child theme
		if ( apply_filters( 'learn_press_child_in_parrent_template_path', '' ) ) {
			wp_enqueue_style( 'ionicons' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'thim_styles', 1001 );

/**
 * Enqueue scripts.
 */
if ( ! function_exists( 'thim_scripts' ) ) {
	function thim_scripts() {
		$v_asset = THIM_THEME_VERSION;
		$min     = '.min';
		if ( class_exists( 'LP_Debug' ) && LP_Debug::is_debug() ) {
			$v_asset = uniqid();
			$min     = '';
		}
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		//				$min     = '';
		// New script update
		wp_register_script( 'thim-content-slider', THIM_URI . 'assets/js/thim-content-slider.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'flexslider', THIM_URI . 'assets/js/jquery.flexslider-min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'magnific-popup', THIM_URI . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'mb-commingsoon', THIM_URI . 'assets/js/mb-commingsoon.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'isotope', THIM_URI . 'assets/js/isotope.pkgd.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'thim_simple_slider', THIM_URI . 'assets/js/thim_simple_slider.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'thim-portfolio-appear', THIM_URI . 'assets/js/jquery.appear.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'thim-portfolio-widget', THIM_URI . 'assets/js/portfolio.min.js', array( 'jquery', 'isotope' ), THIM_THEME_VERSION, true );
		wp_register_script( 'search-course-widget', THIM_URI . 'assets/js/search-course' . $min . '.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'waypoints', THIM_URI . 'assets/js/jquery.waypoints.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_register_script( 'thim-CountTo', THIM_URI . 'assets/js/jquery.countTo.min.js', array( 'jquery' ), THIM_THEME_VERSION, true );
		wp_enqueue_script( 'thim-main', THIM_URI . 'assets/js/main.min.js', array( 'jquery', 'imagesloaded' ), THIM_THEME_VERSION, true );

		// thim archive api v2
		if ( thim_is_new_learnpress( '4.1.6' ) ) {
			if ( class_exists( 'LP_Page_Controller' ) && LP_PAGE_COURSES === LP_Page_Controller::page_current() ) {
                wp_enqueue_script( 'thim-scripts-course-filter', THIM_URI . 'assets/js/thim-course-filter-v2' . $min . '.js', array( 'jquery', 'wp-api-fetch', 'lp-courses', 'wp-hooks' ), $v_asset, true );
			}
		} else {
            wp_enqueue_script( 'thim-scripts-course-filter', THIM_URI . 'assets/js/thim-course-filter' . $min . '.js', array( 'jquery' ), $v_asset, true );
		}

		wp_enqueue_script( 'thim-scripts', THIM_URI . 'assets/js/thim-scripts' . $min . '.js', array( 'jquery' ), $v_asset, true );

		if ( get_post_type() == 'portfolio' && ( is_category() || is_archive() || is_singular( 'portfolio' ) ) ) {
			wp_enqueue_script( 'thim-portfolio-appear' );
			wp_enqueue_script( 'thim-portfolio-widget' );
			wp_enqueue_style( 'thim-portfolio' );
		}

		wp_dequeue_script( 'framework-bootstrap' );
		wp_dequeue_script( 'bootstrap' );

		// Remove some scripts LearnPress
		wp_dequeue_style( 'lpr-print-rate-css' );
		wp_dequeue_style( 'tipsy' );
		wp_dequeue_style( 'certificate' );
		wp_dequeue_style( 'fib' );
		wp_dequeue_style( 'sorting-choice' );
		wp_dequeue_style( 'course-wishlist-style' );
		wp_dequeue_script( 'tipsy' );
		wp_dequeue_script( 'lpr-print-rate-js' );
		wp_dequeue_script( 'course-wishlist-script' );
		wp_dequeue_style( 'learn-press-pmpro-style' );
		wp_dequeue_style( 'learn-press-jalerts' );

		if ( is_front_page() ) {
			wp_dequeue_script( 'webfont' );
			wp_dequeue_script( 'fabric-js' );
			wp_dequeue_script( 'certificate' );
		}

		wp_dequeue_style( 'mo_openid_admin_settings_style' );
		wp_dequeue_style( 'mo_openid_admin_settings_phone_style' );
		//wp_dequeue_style( 'mo-wp-bootstrap-social' );
		wp_dequeue_style( 'mo-wp-bootstrap-main' );
		wp_dequeue_style( 'mo-wp-font-awesome' );

		//Miniorange-login
		wp_dequeue_script( 'js-cookie-script' );
		wp_dequeue_script( 'mo-social-login-script' );

		if ( ! thim_use_bbpress() ) {
			wp_dequeue_style( 'bbp-default' );
			wp_dequeue_script( 'bbpress-editor' );
		}

		//LearnPress 2.0
		wp_dequeue_style( 'owl_carousel_css' );
		wp_dequeue_style( 'learn-press-coming-soon-course' );
		wp_dequeue_script( 'learn-press-jquery-mb-coming-soon' );
		//library css event
		$dequeue_style_event = true;
		if ( get_post_type() == 'tp_event' && is_single() ) {
			$dequeue_style_event = false;
		}

		if ( $dequeue_style_event ) {
			wp_dequeue_style( 'wpems-countdown-css' );
			wp_dequeue_style( 'wpems-owl-carousel-css' );
			wp_dequeue_style( 'wpems-fronted-css' );
			wp_dequeue_style( 'wpems-magnific-popup-css' );
			wp_dequeue_script( 'wpems-magnific-popup-js' );
			wp_dequeue_script( 'wpems-countdown-plugin-js' );
			wp_dequeue_script( 'wpems-countdown-js' );
			wp_dequeue_script( 'wpems-owl-carousel-js' );
			wp_dequeue_script( 'wpems-frontend-js' );
		}

		if ( class_exists( 'WooCommerce' ) && ! is_woocommerce() && ! is_shop() && ! is_product_category() && ! is_product() && ! is_cart() && ! is_checkout() ) {
			wp_dequeue_style( 'wc-blocks-vendors-style' );
			wp_dequeue_style( 'wc-blocks-style' );
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_script( 'woocommerce' );
			wp_dequeue_script( 'jquery-blockui' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'thim_scripts', 1000 );
function thim_custom_admin_scripts() {
	wp_enqueue_script( 'thim-admin-custom-script', THIM_URI . 'assets/js/admin-custom-script.js', array( 'jquery' ), THIM_THEME_VERSION, true );
	wp_enqueue_style( 'thim-admin-theme-style', THIM_URI . 'assets/css/thim-admin.css', array(), THIM_THEME_VERSION );

	wp_register_style( 'thim-admin-font-icon7', THIM_URI . 'assets/css/font-pe-icon-7.css', array(), THIM_THEME_VERSION );
	wp_register_style( 'thim-admin-font-flaticon', THIM_URI . 'assets/css/flaticon.css', array(), THIM_THEME_VERSION );
	wp_register_style( 'thim-admin-ionicons', THIM_URI . 'assets/css/ionicons.min.css', array(), THIM_THEME_VERSION );

	$thim_mod                 = get_theme_mods();
	$thim_page_builder_chosen = ! empty( $thim_mod['thim_page_builder_chosen'] ) ? $thim_mod['thim_page_builder_chosen'] : '';
	wp_localize_script( 'thim-admin-custom-script', 'thim_theme_mods', array(
			'thim_page_builder_chosen' => $thim_page_builder_chosen,
		)
	);
}

add_action( 'admin_enqueue_scripts', 'thim_custom_admin_scripts' );


if ( defined( 'THIM_CORE_VERSION' ) && version_compare( THIM_CORE_VERSION, '2.2.4', '<' ) ) {
	require_once THIM_DIR . 'inc/libs/down_image_size.php';
}
// Custom functions.
require_once get_template_directory() . '/inc/custom-functions.php';

include_once THIM_DIR . '/inc/register-functions.php';

/**
 * Custom template tags for this theme.
 */
require_once THIM_DIR . 'inc/template-tags.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once THIM_DIR . 'woocommerce/woocommerce.php';
}

if ( class_exists( 'WPEMS' ) ) {
	require_once THIM_DIR . 'wp-events-manager/events.php';
}

if ( class_exists( 'BuddyPress' ) ) {
	require_once THIM_DIR . 'buddypress/bp-custom.php';
}

//logo
require_once THIM_DIR . 'inc/header/logo.php';

//custom logo mobile
require_once THIM_DIR . 'inc/header/logo-mobile.php';

// Remove references to SiteOrigin Premium
add_filter( 'siteorigin_premium_upgrade_teaser', '__return_false' );

//For use thim-core
require_once THIM_DIR . 'inc/thim-core-function.php';

add_filter( 'thim_ekit/mega_menu/menu_container/class', function () {
	return 'header .thim-nav-wrapper .tm-table';
} );

if ( ! function_exists( 'thim_enable_upload_svg' ) ) {
	function thim_enable_upload_svg( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';

		return $mimes;
	}

	add_filter( 'upload_mimes', 'thim_enable_upload_svg' );
}

add_action( 'template_redirect', function () {
	if ( is_page('giris') && isset($_GET['action']) && $_GET['action'] === 'register' ) {
		wp_redirect( home_url( '/kayit' ) );
		exit;
	}
});







// === ÖZEL GİRİŞ / ÇIKIŞ / ETKİNLEŞTİRME YÖNLENDİRMELERİ ===

// Giriş sayfasını özelleştir (logout'u engellemeden)
add_filter( 'wp_login_url', function( $login_url, $redirect ) {
    return home_url( '/giris' );
}, 10, 2 );

add_action( 'init', function () {
    if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ) {
        $action = isset($_GET['action']) ? $_GET['action'] : 'login';

        // GİRİŞ EKRANI TALEBİ
        if ( $action === 'login' ) {
            wp_redirect( home_url( '/giris' ) );
            exit;
        }

        // KAYIT EKRANI TALEBİ
        if ( $action === 'register' ) {
            wp_redirect( home_url( '/kayit' ) );
            exit;
        }
    }
});


// Çıkış yaptıktan sonra yönlendirilecek sayfa
// add_filter( 'logout_url', function( $logout_url, $redirect ) {
//     return home_url( '/giris' );
// }, 10, 2 );

// Kayıt sayfası yönlendirmesi
// add_filter( 'register_url', function( $register_url ) {
//     return home_url( '/kayit' );
// });

// Şifre sıfırlama sayfası yönlendirmesi (eğer özel sayfan varsa)
// add_filter( 'lostpassword_url', function( $lostpassword_url, $redirect ) {
//     return home_url( '/sifre-sifirla' );
// }, 10, 2 );

// Kullanıcıyı aktivasyon linkine tıkladığında hesap-etkinlestirme sayfasına yönlendirme
add_action( 'template_redirect', function() {
	if ( isset($_GET['wpforms_activate']) ) {
		wp_redirect( home_url( '/hesap-etkinlestirme' ) );
		exit;
	}
});

// Kullanıcı zaten giriş yapmışsa aktivasyon linkine tıkladığında yönlendirme
add_action( 'init', function () {
	if ( is_user_logged_in() && isset($_GET['wpforms_activate']) ) {
		wp_redirect( home_url( '/giris' ) );
		exit;
	}
});




// === ÖZEL KAYIT OL BUTONU ===

// Fonksiyon: Satır içine yerleşen KAYIT OL butonu
function shortcode_kayit_butonu() {
	ob_start(); ?>
	<style>
		.kayit-buton-inline {
			display: inline-block;
			background-color: #f7a600;
			color: #fff !important;
			padding: 12px 24px;
			border-radius: 6px;
			font-weight: bold;
			text-decoration: none;
			box-shadow: 0 2px 6px rgba(0,0,0,0.15);
			transition: background-color 0.3s ease;
			margin-top: 10px;
			vertical-align: middle;
			white-space: nowrap;
		}
		.kayit-buton-inline:hover {
			background-color: #d98c00;
		}
	</style>
	<a href="<?php echo esc_url( home_url('/kayit') ); ?>" class="kayit-buton-inline">Sign Up</a>
	<?php
	return ob_get_clean();
}
add_shortcode('kayit_butonu', 'shortcode_kayit_butonu');



// SAYFALARI LOGIN DUVARI ARDINA KOYMAK iÇiN
add_action('template_redirect', function () {
    // Eğer kullanıcı giriş yapmamışsa VE korunan sayfalardan birine giriyorsa
    if ( !is_user_logged_in() && is_page(array('randevu-olustur', 'ilk-ders-randevusu', 'odeme-detay', 'odeme-bilgisi')) ) {
        // Burada yönlendirilecek özel uyarı sayfanın linkini veriyorsun
        wp_redirect(site_url('/once-giris-yap'));
        exit;
    }
});



// Shop SLUG'ını /yakında/ SLUG'ına yönlendirme
add_action('template_redirect', function () {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ( preg_match('#^/shop(/|$)#', $path) ) {
        wp_redirect( home_url('/yakinda'), 301 );
        exit;
    }
});


// İstenen sayfalarda SIDEBAR YANBAR engelleme
function hide_sidebar_specific_page( $classes ) {
    if ( is_page( array( 21756, 22510, 24738, 24642 ) ) ) {
        $classes[] = 'no-sidebar';
    }
    return $classes;
}
add_filter( 'body_class', 'hide_sidebar_specific_page' );




// DERS ÜCRETİNİ ADMİN'den GİRMEK ve SİTE SAYFALARINDA KULLANMAK İÇİN KOD

// Admin panelde Saatlik Ücret ayarı alanı ekle
function wp_saatlik_ucret_ayari() {
    add_options_page(
        'Ücret Ayarı',
        'Saatlik Ücret',
        'manage_options',
        'saatlik-ucret-ayari',
        'wp_saatlik_ucret_ayari_sayfasi'
    );
}
add_action('admin_menu', 'wp_saatlik_ucret_ayari');

// Ayar sayfasının içeriği
function wp_saatlik_ucret_ayari_sayfasi() {
    ?>
    <div class="wrap">
        <h1>Saatlik Ücret Ayarı</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('saatlik_ucret_ayarlari');
            do_settings_sections('saatlik-ucret-ayari');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Ayarı kaydet
function wp_saatlik_ucret_ayarlarini_baslat() {
    register_setting('saatlik_ucret_ayarlari', 'saatlik_ucret');
    add_settings_section('sec1', '', null, 'saatlik-ucret-ayari');
    add_settings_field(
        'saatlik_ucret',
        'Saatlik Ücret (TL)',
        'wp_saatlik_ucret_alani',
        'saatlik-ucret-ayari',
        'sec1'
    );
}
add_action('admin_init', 'wp_saatlik_ucret_ayarlarini_baslat');

// Giriş kutusu
function wp_saatlik_ucret_alani() {
    $deger = get_option('saatlik_ucret', '490');
    echo '<input type="number" name="saatlik_ucret" value="' . esc_attr($deger) . '" step="1" />';
}

// === Admin paneldeki saatlik ücreti JS'e aktaran kod ===
function wp_saatlik_ucret_verisini_gecir() {
    $saatlik_ucret = get_option('saatlik_ucret', '490');
    echo "<script>window.saatlikUcret = " . intval($saatlik_ucret) . ";</script>";
}
add_action('wp_head', 'wp_saatlik_ucret_verisini_gecir');



// Formlar İçin Sayfalarda Ad-Soyad Ayırma

// {user_display_name} = {user_first_name} + {user_last_name}
add_filter( 'wpforms_field_value_user_first_name', function() {
    return is_user_logged_in() ? wp_get_current_user()->user_firstname : '';
});

add_filter( 'wpforms_field_value_user_last_name', function() {
    return is_user_logged_in() ? wp_get_current_user()->user_lastname : '';
});





// Python ile Blog Sayfası Eklemek İçin Çıkmayan Menü Kısmını Çıkmaya Zorlamak İçin

add_filter( 'wp_is_application_passwords_available', '__return_true' );



//  ***********************************************
//     <!-- EN - HTML AKORDİYON KODU BAŞLANGICI -->
//  ***********************************************

function custom_business_services_shortcode() {
    ob_start();
    ?>


    <!-- *** HTML AKORDİYON KODU BAŞLANGICI *** -->



<div class="services-accordion">
    <h2 class="services-title">Our Services</h2>
    
    <!-- 1. New Business Setup -->
    <div class="accordion-item level-1">
        <div class="accordion-header" onclick="handleHeaderClick(event, this, 1)">
            <span class="accordion-icon icon-black"></span>
            <div>
                <h3><a href="https://pebuc.site/menu-a" target="_blank" class="accordion-link">1. New Business Setup</a></h3>
                <p class="header-description">Launch your venture with expert guidance to ensure rapid setup and market success. Our tailored solutions minimize risks and maximize growth potential.</p>
            </div>
            <span class="toggle-icon">+</span>
        </div>
        <div class="accordion-content">
            
            <!-- 1.1 Business Development -->
            <div class="accordion-item level-2">
                <div class="accordion-header" onclick="handleHeaderClick(event, this, 2)">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a1" target="_blank" class="accordion-link">1.1 Global Business Expansion & Development</a></h4>
                        <p class="header-description">Expand globally with strategic plans that navigate international markets. We connect you with opportunities to scale efficiently and profitably.</p>
                    </div>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="accordion-content">
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-a11" target="_blank" class="accordion-link">1.1.1 Export Strategy & Partnering</a></h5>
                                <p class="header-description">Boost exports with customized strategies and trusted global partners. We streamline your path to new markets for maximum revenue growth.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-a12" target="_blank" class="accordion-link">1.1.2 Import Strategy & Partnering</a></h5>
                                <p class="header-description">Simplify imports with optimized strategies and reliable suppliers. Our solutions reduce costs and ensure a steady supply chain.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Other Level 2 items -->
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a2" target="_blank" class="accordion-link">1.2 Market Research & Opportunity Analysis</a></h4>
                        <p class="header-description">Identify high-potential markets with in-depth research and analytics. Our insights empower you to seize opportunities and outpace competitors.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a3" target="_blank" class="accordion-link">1.3 Strategic Marketing Plan Development</a></h4>
                        <p class="header-description">Craft data-driven marketing plans to engage your target audience. Our strategies drive brand visibility and accelerate sales growth.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a4" target="_blank" class="accordion-link">1.4 Business Plan Creation & Financial Modeling</a></h4>
                        <p class="header-description">Build compelling business plans and accurate financial models to attract investors. We ensure your vision is clear and financially viable.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a5" target="_blank" class="accordion-link">1.5 Recruitment and Administrative Structuring</a></h4>
                        <p class="header-description">Recruit top talent and establish efficient administrative systems. Our solutions create a strong foundation for sustainable growth.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a6" target="_blank" class="accordion-link">1.6 Sales & Marketing Team Building & Recruitment</a></h4>
                        <p class="header-description">Assemble high-performing sales and marketing teams tailored to your goals. We recruit and train to drive exceptional results.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a7" target="_blank" class="accordion-link">1.7 Legal Setup & Product Registration</a></h4>
                        <p class="header-description">Navigate complex legal requirements and register products seamlessly. Our expertise ensures compliance and fast market entry.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-a8" target="_blank" class="accordion-link">1.8 Logistics and Supply Chain Setup</a></h4>
                        <p class="header-description">Optimize logistics and supply chains for seamless operations. Our solutions enhance efficiency and reduce operational costs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 2. Performance Enhancement & Expansion -->
    <div class="accordion-item level-1">
        <div class="accordion-header" onclick="handleHeaderClick(event, this, 1)">
            <span class="accordion-icon icon-orange"></span>
            <div>
                <h3><a href="https://pebuc.site/menu-b" target="_blank" class="accordion-link">2. Performance Enhancement & Expansion</a></h3>
                <p class="header-description">Transform your business with strategies to boost efficiency and growth. We help you surpass targets through targeted optimizations.</p>
            </div>
            <span class="toggle-icon">+</span>
        </div>
        <div class="accordion-content">
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b1" target="_blank" class="accordion-link">2.1 Business Analysis & Productivity Audit</a></h4>
                        <p class="header-description">Uncover inefficiencies with detailed audits to enhance productivity. Our insights drive measurable improvements across your operations.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b2" target="_blank" class="accordion-link">2.2 Marketing Plan Review & Enhancement</a></h4>
                        <p class="header-description">Refine your marketing plans to maximize ROI and customer engagement. We optimize strategies for higher conversions and brand impact.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b3" target="_blank" class="accordion-link">2.3 HR Structure & Workforce Optimization</a></h4>
                        <p class="header-description">Streamline HR processes and optimize your workforce for peak performance. Our solutions boost employee engagement and efficiency.</p>
                    </div>
                </div>
            </div>
            
            <!-- 2.4 Sales & Marketing Team Performance -->
            <div class="accordion-item level-2">
                <div class="accordion-header" onclick="handleHeaderClick(event, this, 2)">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b4" target="_blank" class="accordion-link">2.4 Sales & Marketing Team Performance Optimization</a></h4>
                        <p class="header-description">Elevate your sales and marketing teams with targeted performance strategies. We drive results through customized optimization plans.</p>
                    </div>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="accordion-content">
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b41" target="_blank" class="accordion-link">2.4.1 Team Performance Diagnostics</a></h5>
                                <p class="header-description">Diagnose team weaknesses with precise analytics to unlock potential. Our insights pave the way for significant performance gains.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b42" target="_blank" class="accordion-link">2.4.2 Performance Management System Design</a></h5>
                                <p class="header-description">Implement tailored performance systems to drive consistent results. Our designs align teams with your strategic objectives.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b43" target="_blank" class="accordion-link">2.4.3 Performance Monitoring & KPIs</a></h5>
                                <p class="header-description">Track progress with customized KPIs for sustained business growth. Our monitoring solutions keep your teams on target.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header" onclick="handleHeaderClick(event, this, 3)">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b44" target="_blank" class="accordion-link">2.4.4 Coaching & Counseling Programs</a></h5>
                                <p class="header-description">Transform teams with personalized coaching to boost skills and morale. Our programs drive measurable performance improvements.</p>
                            </div>
                            <span class="toggle-icon">+</span>
                        </div>
                        <div class="accordion-content">
                            <div class="accordion-item level-4">
                                <div class="accordion-header">
                                    <div>
                                        <h6><a href="https://pebuc.site/menu-b441" target="_blank" class="accordion-link">- For Sales Representatives</a></h6>
                                        <p class="header-description">Empower sales reps with expert coaching to close more deals. Our tailored programs boost confidence and sales outcomes.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item level-4">
                                <div class="accordion-header">
                                    <div>
                                        <h6><a href="https://pebuc.site/menu-b442" target="_blank" class="accordion-link">- For Field Sales Managers</a></h6>
                                        <p class="header-description">Develop field managers into inspiring leaders with targeted coaching. Our programs enhance team performance and results.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item level-4">
                                <div class="accordion-header">
                                    <div>
                                        <h6><a href="https://pebuc.site/menu-b443" target="_blank" class="accordion-link">- For Marketing Managers</a></h6>
                                        <p class="header-description">Strengthen marketing managers with strategic coaching for impactful campaigns. We drive creativity and measurable ROI.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b5" target="_blank" class="accordion-link">2.5 Legal & Logistics Efficiency Review</a></h4>
                        <p class="header-description">Streamline legal and logistics processes to cut costs and boost efficiency. Our reviews ensure compliance and operational excellence.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 3. Training Services -->
    <div class="accordion-item level-1">
        <div class="accordion-header" onclick="handleHeaderClick(event, this, 1)">
            <span class="accordion-icon icon-green"></span>
            <div>
                <h3><a href="https://pebuc.site/menu-c" target="_blank" class="accordion-link">3. Training Services</a></h3>
                <p class="header-description">Equip your team with cutting-edge skills to dominate your industry. Our training programs drive performance and competitive advantage.</p>
            </div>
            <span class="toggle-icon">+</span>
        </div>
        <div class="accordion-content">
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-c1" target="_blank" class="accordion-link">3.1 Basic Technical Training</a></h4>
                        <p class="header-description">Master industry-specific technical skills with our targeted training. We empower your team to excel in operational tasks.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-c2" target="_blank" class="accordion-link">3.2 Product Training</a></h4>
                        <p class="header-description">Deepen product knowledge to enhance sales and customer trust. Our training ensures your team communicates value effectively.</p>
                    </div>
                </div>
            </div>
            
            <!-- 3.3 Sales Techniques Training -->
            <div class="accordion-item level-2">
                <div class="accordion-header" onclick="handleHeaderClick(event, this, 2)">
                    <div>
                        <h4><a href="https://pebuc.site/menu-c3" target="_blank" class="accordion-link">3.3 Sales Techniques Training</a></h4>
                        <p class="header-description">Transform your sales team with proven techniques to close more deals. Our training maximizes conversion rates and revenue.</p>
                    </div>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="accordion-content">
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b31" target="_blank" class="accordion-link">3.3.1 Beginner Level</a></h5>
                                <p class="header-description">Equip beginners with core sales skills to build confidence and results. Our program lays a strong foundation for success.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b32" target="_blank" class="accordion-link">3.3.2 Intermediate Level</a></h5>
                                <p class="header-description">Advance your sales team with strategies to strengthen client relationships. We boost conversions through refined techniques.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b33" target="_blank" class="accordion-link">3.3.3 Advanced Level</a></h5>
                                <p class="header-description">Master complex sales environments with expert strategies. Our training positions your team as industry leaders.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 3.4 Marketing Strategy & Execution Training -->
            <div class="accordion-item level-2">
                <div class="accordion-header" onclick="handleHeaderClick(event, this, 2)">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b4" target="_blank" class="accordion-link">3.4 Marketing Strategy & Execution Training</a></h4>
                        <p class="header-description">Empower your team to create and execute high-impact marketing campaigns. Our training drives brand growth and measurable ROI.</p>
                    </div>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="accordion-content">
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b41" target="_blank" class="accordion-link">3.4.1 Beginner Level</a></h5>
                                <p class="header-description">Learn essential marketing skills to launch effective campaigns. Our program builds confidence for new marketers.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b42" target="_blank" class="accordion-link">3.4.2 Intermediate Level</a></h5>
                                <p class="header-description">Enhance campaigns with advanced digital and traditional marketing skills. We help your team achieve greater market impact.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item level-3">
                        <div class="accordion-header">
                            <div>
                                <h5><a href="https://pebuc.site/menu-b43" target="_blank" class="accordion-link">3.4.3 Advanced Level</a></h5>
                                <p class="header-description">Lead markets with data-driven, strategic marketing expertise. Our training delivers global brand success.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b5" target="_blank" class="accordion-link">3.5 Leadership & Management Training</a></h4>
                        <p class="header-description">Develop visionary leaders to inspire and drive team success. Our training fosters strategic thinking and effective management.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b6" target="_blank" class="accordion-link">3.6 Coaching & Counseling Skills for Managers</a></h4>
                        <p class="header-description">Equip managers with coaching skills to unlock team potential. Our programs boost morale and drive performance.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-b7" target="_blank" class="accordion-link">3.7 Train-the-Trainer Programs</a></h4>
                        <p class="header-description">Empower your trainers to deliver world-class in-house training. Our programs ensure engaging and effective learning.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 4. Remote & On-Demand Support -->
    <div class="accordion-item level-1">
        <div class="accordion-header" onclick="handleHeaderClick(event, this, 1)">
            <span class="accordion-icon icon-blue"></span>
            <div>
                <h3><a href="https://pebuc.site/menu-d" target="_blank" class="accordion-link">4. Remote & On-Demand Support</a></h3>
                <p class="header-description">Seamlessly manage operations with flexible, on-demand remote solutions. Our services ensure efficiency and scalability anywhere.</p>
            </div>
            <span class="toggle-icon">+</span>
        </div>
        <div class="accordion-content">
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-d1" target="_blank" class="accordion-link">4.1 Virtual Office & Contact Center Setup</a></h4>
                        <p class="header-description">Modernize customer service with virtual office and contact center solutions. We ensure seamless communication and satisfaction.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-d2" target="_blank" class="accordion-link">4.2 Remote Office & Operations Management</a></h4>
                        <p class="header-description">Run remote operations smoothly with expert management solutions. Our services enhance productivity and flexibility.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-d3" target="_blank" class="accordion-link">4.3 Remote Customer Engagement Solutions</a></h4>
                        <p class="header-description">Build stronger customer relationships with innovative remote engagement tools. We drive loyalty and satisfaction remotely.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item level-2">
                <div class="accordion-header">
                    <div>
                        <h4><a href="https://pebuc.site/menu-d4" target="_blank" class="accordion-link">4.4 Other Tailored Remote Support Services</a></h4>
                        <p class="header-description">Access customized remote support to meet your unique business needs. Our solutions ensure flexibility and operational success.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    
    <script>
        function handleHeaderClick(event, header, level) {
            // Eğer tıklanan öğe bir link ise, akordiyon işlevini durdur
            if (event.target.tagName === 'A' || event.target.closest('a')) {
                return; // Link tıklandıysa, akordiyonu açma/kapama işlemini yapma
            }

            const content = header.nextElementSibling;
            const icon = header.querySelector(".toggle-icon");
            const parentItem = header.closest(".accordion-item");
            
            if (!content || !content.classList.contains("accordion-content")) return;
            
            // Otomatik kapatma mantığı kaldırıldı - Aynı anda birden fazla akordiyon açık kalabilir
            /*
            if (level === 1) {
                const allLevel1 = document.querySelectorAll(".accordion-item.level-1");
                allLevel1.forEach(item => {
                    if (item !== parentItem) {
                        closeAccordionItem(item);
                    }
                });
            } else if (level === 2) {
                const parentLevel1 = parentItem.closest(".accordion-item.level-1");
                if (parentLevel1) {
                    const siblingLevel2 = parentLevel1.querySelectorAll(".accordion-item.level-2");
                    siblingLevel2.forEach(sibling => {
                        if (sibling !== parentItem) {
                            closeAccordionItem(sibling);
                        }
                    });
                }
            } else if (level === 3) {
                const parentLevel2 = parentItem.closest(".accordion-item.level-2");
                if (parentLevel2) {
                    const siblingLevel3 = parentLevel2.querySelectorAll(".accordion-item.level-3");
                    siblingLevel3.forEach(sibling => {
                        if (sibling !== parentItem) {
                            closeAccordionItem(sibling);
                        }
                    });
                }
            }
            */
            
            // Toggle current item
            if (content.classList.contains("active")) {
                closeAccordionItem(parentItem);
            } else {
                openAccordionItem(parentItem);
            }
        }
        
        function openAccordionItem(item) {
            const content = item.querySelector('.accordion-content');
            const icon = item.querySelector('.toggle-icon');
            
            if (content) {
                content.classList.add('active');
                content.style.display = 'block';
                content.style.opacity = '1';
                content.style.maxHeight = 'none';
            }
            
            if (icon) {
                icon.textContent = '−';
                icon.classList.add('active');
            }
            
            item.classList.add('accordion-active');
        }
        
        function closeAccordionItem(item) {
            const content = item.querySelector('.accordion-content');
            const icon = item.querySelector('.toggle-icon');
            
            // Close all nested items first
            const nestedItems = item.querySelectorAll('.accordion-item');
            nestedItems.forEach(nested => {
                if (nested !== item) {
                    closeAccordionItem(nested);
                }
            });
            
            if (content) {
                content.classList.remove('active');
                content.style.display = 'none';
                content.style.opacity = '0';
                content.style.maxHeight = '0';
            }
            
            if (icon) {
                icon.textContent = '+';
                icon.classList.remove('active');
            }
            
            item.classList.remove('accordion-active');
        }
        
        // Initialize - close all items
        document.addEventListener('DOMContentLoaded', function() {
            const allItems = document.querySelectorAll('.accordion-item');
            allItems.forEach(item => {
                closeAccordionItem(item);
            });
            console.log('Accordion initialized successfully!');
        });
    </script>





    <!-- *** HTML AKORDİYON KODU SONU *** -->


    <?php
    return ob_get_clean();
}
add_shortcode('business_services', 'custom_business_services_shortcode');



//  ***********************************************
//     <!-- HTML AKORDİYON KODU SONU -->
//  ***********************************************





