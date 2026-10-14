<?php
defined( 'ABSPATH' ) || exit;

$instance = vgse_givewp();
?>
<p><?php _e('Thank you for installing our plugin.', $instance->textname); ?></p>

<?php
$steps = array();

if (!class_exists('Give')) {
	$steps['install_dependencies'] = '<p>' . sprintf(__('Install the plugin: Give. <a href="%s" target="_blank" class="button install-plugin-trigger">Click here</a>. This is a GiveWP extension.', $instance->textname), esc_url($this->get_plugin_install_url('give'))) . '</p>';
} else {
	$steps['open_editor'] = '<p>' . sprintf(__('You can open the Give Forms Editor Now:  <a href="%s" class="button">Click here</a>', $instance->textname), esc_url(VGSE()->helpers->get_editor_url($instance->post_type))) . '</p>';
}

$steps = apply_filters('vg_sheet_editor/givewp/welcome_steps', $steps);

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