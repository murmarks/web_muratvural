<?php
/**
 * @package   Essential_Grid
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.essential-grid.com/
 * @copyright 2025 ThemePunch
 */

if( !defined( 'ABSPATH') ) exit();

if ( !function_exists( 'esg_json_decode' ) ) {

	/**
	 * @param mixed $data
	 * @param mixed $default
	 * @param bool $assoc
	 * @return mixed
	 */
	function esg_json_decode( $data, $default = '', $assoc = true ) {
		if ( empty( $data ) ) { 
			return $default;
		}
		
		$result = json_decode( $data, $assoc );
		if ( json_last_error() !== JSON_ERROR_NONE || $result === null ) {
			$result = $default;
		}
		
		return $result;
	}
	
}

if ( !function_exists( 'esg_remove_utf8_bom' ) ) {

	/**
	 * @param string $text
	 * @return string
	 */
	function esg_remove_utf8_bom($text) {
		$bom = pack('H*','EFBBBF');
		return preg_replace("/^$bom/", '', $text);
	}
}

if ( !function_exists( 'esg_get_image_tag' ) ) {

	/**
	 * Gets an img tag html
	 *
	 * @param string $src
	 * @param array $attr
	 *
	 * @return string
	 */
	function esg_get_image_tag( $src, $attr = [] ) {
		$src  = apply_filters( 'essgrid_get_image_tag_src', $src );
		$attr = apply_filters( 'essgrid_get_image_tag_attr', $attr );

		$attr = array_map( 'esc_attr', $attr );
		$attr['src'] = esc_url( $src );

		return '<img ' . implode(' ', array_map( function($n, $m) { return sanitize_key($n) . '="' . $m . '"'; }, array_keys($attr), $attr ) ) . ' />';
	}
}

if ( !function_exists( 'esg_shuffle_assoc' ) ) {

	/**
	 * shuffle by preserving the key
	 * @param array $list
	 * @return array
	 */
	function esg_shuffle_assoc( $list ) {
		if ( !is_array( $list ) ) return $list;

		$keys = array_keys( $list );
		shuffle( $keys );
		$random = [];
		foreach ( $keys as $key ) {
			$random[ $key ] = $list[ $key ];
		}

		return apply_filters( 'essgrid_shuffle_assoc', $random );
	}
}

if ( !function_exists( 'esg_text_has_certain_tag' ) ) {

	/**
	 * check if text has a certain tag in it
	 * 
	 * @param string $string
	 * @param string $tag
	 * @return bool
	 */
	function esg_text_has_certain_tag( $string, $tag ) {
		$r = apply_filters( 'essgrid_text_has_certain_tag', [ 'string' => $string, 'tag' => $tag ] );
		if ( !is_array( $r ) || !isset( $r['string'] ) || is_array( $r['string'] ) ) return "";
		return preg_match( "/<" . $r['tag'] . "[^<]+>/", $r['string'] ) != 0;
	}
}

if ( !function_exists( 'esg_sort_by_order' ) ) {

	/**
	 * Sort Array by Value order
	 * 
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	 function esg_sort_by_order( $a, $b ) {
		if ( !isset( $a['order'] ) || !isset( $b['order'] ) ) return 0;
		$a = $a['order'];
		$b = $b['order'];
		return ( ( $a < $b ) ? -1 : ( ( $a > $b ) ? 1 : 0) );
	}
}

if ( !function_exists( 'esg_stripslashes_deep' ) ) {

	/**
	 * strip slashes recursive
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	function esg_stripslashes_deep( $value)  {
		if ( !empty( $value ) ) {
			$value = is_array( $value ) ? array_map( 'esg_stripslashes_deep', $value ) : stripslashes( $value );
		}

		return $value;
	}
}

if ( !function_exists( 'esg_hex2rgba' ) ) {

	/**
	 * change hex to rgba
	 * 
	 * @param string $hex
	 * @param bool $transparency
	 * @return string
	 */
	function esg_hex2rgba( $hex, $transparency = false ) {
		if ($transparency !== false) {
			$transparency = ($transparency > 0) ? number_format(($transparency / 100), 2, ".", "") : 0;
		} else {
			$transparency = 1;
		}

		$hex = str_replace("#", "", $hex);
		$r = 0;
		$g = 0;
		$b = 0;
		if (strlen($hex) == 3) {
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		} else {
			if (strlen($hex) >= 6) {
				$r = hexdec(substr($hex, 0, 2));
				$g = hexdec(substr($hex, 2, 2));
				$b = hexdec(substr($hex, 4, 2));
			}
		}

		return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $transparency . ')';
	}
}

if ( !function_exists( 'esg_convert_post_date' ) ) {

	/**
	 * convert date to the date format that the user chose
	 *
	 * @param string $date
	 * @return string
	 */
	function esg_convert_post_date( $date ) {
		if ( empty( $date ) ) {
			return ( $date );
		}
		return date_i18n( get_option( 'date_format' ), strtotime( $date ) );
	}
}

if ( !function_exists( 'esg_split_taxonomy_string' ) ) {
	
	/**
	 * split string like 'category_25' or 'post_tag_11' to ['post_tag', 11]
	 *
	 * @param $string
	 * @return array
	 */
	function esg_split_taxonomy_string( $string ) {
		$pos = strrpos( $string, '_' );
		if ( $pos === false ) {
			// no underscore in the string
			return [ $string, null ];
		}

		$before = substr( $string, 0, $pos );
		$after = substr( $string, $pos + 1 );

		return [ $before, $after ];
	}
}

if ( !function_exists( 'esg_strip_shortcode' ) ) {

	/**
	 * remove essential grid shortcode from a text
	 * 
	 * @param string $content
	 * @param string $shortcode
	 * @return string
	 */
	function esg_strip_shortcode( $content, $shortcode = 'ess_grid' ) {
		global $shortcode_tags;

		$original_tags = $shortcode_tags;
		$shortcode_tags = [$shortcode => 1];
		$content = strip_shortcodes( $content );
		$shortcode_tags = $original_tags;

		return $content;
	}
}

if ( !function_exists( 'esg_compress_assets' ) ) {

	/**
	 * minimize assets like css or js
	 *
	 * @param string $str
	 * @param bool   $is_js
	 * @return string
	 */
	function esg_compress_assets( $str, $is_js = false ) {
		global $esg_dev_mode;

		if ( $esg_dev_mode || empty( $str ) ) {
			return $str;
		}

		// remove comments
		$str = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str );

		// remove slashed comments
		if ( $is_js ) {
			$str = preg_replace( "@\s*(?<!:)//.*?$@m", '', $str );
		}

		// Remove whitespace
		$str = preg_replace( '/\s*([{}|:;,=])\s+/', '$1', $str );

		// remove tabs, newlines, etc.
		$str = str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $str );

		// remove other spaces before/after ;
		if ( ! $is_js ) {
			$str = preg_replace( [ '(;( )*})' ], '}', $str );
		}

		return preg_replace( [ '(;( )+)', '(( )+;)' ], ';', $str );
	}
}

if ( !function_exists( 'esg_is_shortcode_with_handle_exist' ) ) {

	/**
	 * check if in the content exists a certain essential grid
	 *
	 * @param string $grid_handle
	 * @return bool
	 */
	function esg_is_shortcode_with_handle_exist( $grid_handle ) {
		$content = get_the_content();
		$pattern = get_shortcode_regex();
		preg_match_all( '/' . $pattern . '/s', $content, $matches );

		if ( !is_array( $matches[2] ) || empty( $matches[2] ) ) {
			return false;
		}

		foreach ( $matches[2] as $key => $sc ) {
			if ( 'ess_grid' != $sc ) continue;
			$attr = shortcode_parse_atts( $matches[3][$key] );
			if ( isset( $attr['alias'] ) && $grid_handle == $attr['alias'] ) {
				return true;
			}
		}

		return false;
	}
}

if ( !function_exists( 'esg_sanitize_utf8_to_unicode' ) ) {

	/**
	 * sanitizes utf8 characters to unicode
	 * 
	 * @param string $string
	 * @return string
	 */
	function esg_sanitize_utf8_to_unicode( $string ) {
		// replace 2+ space with single
		$string = preg_replace( '!\s+!', ' ', $string );
		// replace space with dash
		$string = preg_replace( '!\s!', '-', $string );

		return sanitize_key( wp_json_encode( $string ) );
	}
}

if ( !function_exists( 'esg_get_attachment_info' ) ) {

	/**
	 * get attachment info
	 * 
	 * @param int $attachment_id
	 * @return array
	 */
	function esg_get_attachment_info( $attachment_id ) {
		$attachment = get_post( $attachment_id );
		return [
			'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'caption' => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'href' => get_permalink( $attachment->ID ),
			'src' => $attachment->guid,
			'title' => $attachment->post_title
		];
	}
}

if ( !function_exists( 'esg_get_filesystem' ) ) {

	/**
	 * @return WP_Filesystem_Direct
	 */
	function esg_get_filesystem()
	{
		global $wp_filesystem;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		WP_Filesystem();

		$result = $wp_filesystem;

		if (!$result instanceof WP_Filesystem_Direct) {
			if (!defined('FS_CHMOD_DIR')) define('FS_CHMOD_DIR', fileperms(ABSPATH) & 0777 | 0755);
			if (!defined('FS_CHMOD_FILE')) define('FS_CHMOD_FILE', fileperms(ABSPATH . 'index.php') & 0777 | 0644);
			$result = new WP_Filesystem_Direct(null);
		}

		return $result;
	}
}

if ( !function_exists( 'esg_get_attachment_url' ) ) {

	/**
	 * prints out numbers in YouTube format
	 * 
	 * @param int $num
	 * @return int|string
	 */
	function esg_thousands_view_format( $num ) {
		if ( $num > 999 ) {
			$x = round( $num );
			$x_number_format = number_format( $x );
			$x_array = explode( ',', $x_number_format );
			$x_parts = [ 'K', 'M', 'B', 'T' ];
			$x_count_parts = count( $x_array ) - 1;
			$x_display = $x_array[0] . ( (int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '' );
			$x_display .= $x_parts[ $x_count_parts - 1 ];
		} else {
			$x_display = $num;
		}

		return $x_display;
	}
}
