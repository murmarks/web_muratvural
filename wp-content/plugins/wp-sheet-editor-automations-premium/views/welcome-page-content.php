<?php
defined( 'ABSPATH' ) || exit;
?>
<p><?php esc_html_e( 'Thank you for installing our plugin.', 'vg_sheet_editor_automations' ); ?></p>
<p><?php esc_html_e( 'This plugin provides a lot of automation features to our spreadsheet editor, that will save you a lot of time. The goal is to allow you to save all the actions and execute them automatically for you, this includes the following:', 'vg_sheet_editor_automations' ); ?></p>
<ol>
	<li><p><?php esc_html_e( 'Save the modified cells in the spreadsheet at a later date or in a recurring schedule', 'vg_sheet_editor_automations' ); ?></p></li>
	<li><p><?php esc_html_e( 'Run bulk edits/actions on any data set later or recurringly', 'vg_sheet_editor_automations' ); ?></p></li>
	<li><p><?php esc_html_e( 'Automatically run any export at a later date or recurringly', 'vg_sheet_editor_automations' ); ?></p></li>
	<li><p><?php esc_html_e( 'Automatically import data at a later date or recurringly', 'vg_sheet_editor_automations' ); ?></p></li>
	<li><p><?php esc_html_e( 'And more', 'vg_sheet_editor_automations' ); ?></p></li>
</ol>

<h3><?php esc_html_e( 'What\'s next?', 'vg_sheet_editor_automations' ); ?></h3>
<?php
$steps = array();
if ( ! class_exists( 'WP_Sheet_Editor' ) || ! function_exists( 'vgse_universal_sheet' ) ) {
	$steps['install_dependencies'] = '<p>' . __( 'This plugin works with any premium spreadsheet editor of WP Sheet Editor. Please install any of the main plugins to be able to use this automations extension.', 'vg_sheet_editor_automations' ) . '</p>';
} else {
	$steps['cron'] = '<p>' . __( 'Please make sure that you have configured your cron jobs as <a href="https://wpsheeteditor.com/automations-installation-setup/#2-_Set_up_WordPress_cron_jobs" target="_blank">described in this guide</a>.', 'vg_sheet_editor_automations' ) . '</p>';
	$steps['ready'] = '<p>' . __( 'Everything is ready. Now you can enjoy all the automation features when you\'re using the spreadsheet editor. You\'ll see the options when you\'re saving cells, bulk editing, exporting, or importing.', 'vg_sheet_editor_automations' ) . '</p>';
}

$steps = apply_filters( 'vg_sheet_editor/automations/welcome_steps', $steps );

if ( ! empty( $steps ) ) {
	echo '<ol class="steps">';
	foreach ( $steps as $key => $step_content ) {
		?>
		<li><?php echo wp_kses_post( $step_content ); ?></li>		
		<?php
	}

	echo '</ol>';
}
