<?php defined( 'ABSPATH' ) || exit; 
$comments_instance = vgse_comments();
?>
<p><?php _e('Thank you for installing our plugin.', $comments_instance->textname); ?></p>

<?php
$steps = array();


$free_limit_note = __('<p>Note. You are using the free version and you can edit only comments of the blog posts.</p>', $comments_instance->textname);
if (wpsecr_fs()->can_use_premium_code__premium_only()) {
	$free_limit_note = '';
}

$steps['open_editor'] = '<p>' . sprintf(__('You can open the Comments Bulk Editor Now:  <a href="%s" class="button">Click here</a>', $comments_instance->textname), VGSE()->helpers->get_editor_url('comments')) . '</p>' . $free_limit_note;

include VGSE_DIR . '/views/free-extensions-for-welcome.php';
$steps['free_extensions'] = $free_extensions_html;

$steps = apply_filters('vg_sheet_editor/comments/welcome_steps', $steps);

if (!empty($steps)) {
	echo '<ol class="steps">';
	foreach ($steps as $key => $step_content) {
		if (empty($step_content)) {
			continue;
		}
		?>
		<li><?php echo wp_kses_post($step_content); ?></li>		
		<?php
	}

	echo '</ol>';
}	