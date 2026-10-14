<?php
/**
 * Template Name: Essential Grid Blank Template
 * Template Post Type: post, page
 * The template for displaying Essential Grid on a blank page
 */

if(!defined('ABSPATH')) exit();
$page_bg = get_post_meta(get_the_ID(), 'tp_eg_page_bg_color', true);
$page_bg = ($page_bg == '' || $page_bg == 'transparent') ? 'transparent' : $page_bg.";";
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>

	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<?php wp_head(); ?>
	<style>
		body:before { display:none !important}
		body:after { display:none !important}
		body, body.page-template-blank-page-template, body.page-template---publicviewsblank-page-template-php { background:<?php echo $page_bg;?>}
	</style>
</head>

<body <?php body_class(); ?>>
<?php do_action('esg_page_template_pre_content'); ?>
<div>
	<?php
	while ( have_posts() ) : 
		the_post();
		the_content();
	endwhile;
	?>
</div>
<?php do_action('esg_page_template_post_content'); ?>
<?php wp_footer(); ?>

</body>
</html>
