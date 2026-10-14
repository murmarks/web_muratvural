<?php defined( 'ABSPATH' ) || exit; ?>
<p><a href="https://wpsheeteditor.com/?utm_source=scheduled-task-email&utm_medium=email-logo&utm_campaign=<?php echo esc_attr( $sheet_key ); ?>" target="_blank" class="logo-link"><img style="max-width: 200px; display: block; margin: 0 auto;" src="<?php echo esc_url( VGSE()->logo_url ); ?>" class="vg-logo"></a></p>
<p><?php esc_html_e('Hi there,', 'vg_sheet_editor_automations' ); ?></p>
<p><?php printf(esc_html__('This is an automated email sent from your WordPress site: %s,
to notify you that a scheduled export has been completed. Here are the details:', 'vg_sheet_editor_automations' ), esc_url($wp_url)); ?></p>
<ul>
    <li><a href="<?php echo esc_url($download_url); ?>"><?php esc_html_e('Click here to download the file', 'vg_sheet_editor_automations' ); ?></a></li>
    <li><b><?php esc_html_e('Spreadsheet:', 'vg_sheet_editor_automations' ); ?></b> <?php echo sanitize_text_field($sheet_name); ?></li>
    <li><b><?php esc_html_e('Export name:', 'vg_sheet_editor_automations' ); ?></b> <?php echo sanitize_text_field($export_name); ?></li>
    <li><b><?php esc_html_e('Completed on:', 'vg_sheet_editor_automations' ); ?></b> <?php echo sanitize_text_field($completion_date); ?></li>
    <li><b><?php esc_html_e('Total items exported:', 'vg_sheet_editor_automations' ); ?></b> <?php echo intval($total_rows); ?></li>
    <li><b><?php esc_html_e('Is this a recurring export?', 'vg_sheet_editor_automations' ); ?></b> <?php echo sanitize_text_field($repeat_every); ?></li>
    <li><b><?php esc_html_e('File path:', 'vg_sheet_editor_automations' ); ?></b> <?php echo sanitize_text_field($file_path); ?></li>
    <li><a href="<?php echo esc_url($log_url); ?>"><?php esc_html_e('View the log', 'vg_sheet_editor_automations' ); ?></a></li>
</ul>
<p><?php printf(esc_html__('The export files are deleted automatically after %d hours.', 'vg_sheet_editor_automations' ), $expiration_hours); ?></p>
<p><?php esc_html_e('You can view the log to understand what happened in every step of the process.', 'vg_sheet_editor_automations' ); ?></p>
<p><?php esc_html_e('Best regards,', 'vg_sheet_editor_automations' ); ?></p>
<p><?php esc_html_e('WP Sheet Editor', 'vg_sheet_editor_automations' ); ?></p>