<?php
defined( 'ABSPATH' ) || exit;
$instance = vgse_media_library();
?>
<p><?php _e('Thank you for installing our plugin.', $instance->textname); ?></p>

<?php
$steps = array();
$steps['open_editor'] = '<p>' . sprintf(__('You can open the Media library Bulk Editor Now:  <a href="%s" class="button">Click here</a>', $instance->textname), esc_url(VGSE()->helpers->get_editor_url($instance->post_type))) . '</p>';

$steps = apply_filters('vg_sheet_editor/media_library/welcome_steps', $steps);

if (!empty($steps)) {
	echo '<ol class="steps">';
	foreach ($steps as $key => $step_content) { if(empty($step_content)){continue;}
		?>
		<li><?php echo wp_kses_post($step_content); ?></li>		
		<?php
	}

	echo '</ol>';
}