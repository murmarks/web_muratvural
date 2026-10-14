<?php defined( 'ABSPATH' ) || exit; ?>
<div class="remodal save-later-modal" data-remodal-id="vgse_save_later_modal" data-remodal-options="closeOnOutsideClick: false, hashTracking: false">
	<div class="modal-content">
		<h3><?php _e( 'Save later', 'vg_sheet_editor_automations' ); ?></h3>
<p><?php _e( 'You can use this option if you want to save the modified cells later or recurringly.', 'vg_sheet_editor_automations' ); ?></p>
<form method="POST" action="">
<?php WPSE_Automations_Core_Obj()->render_form_fields( $post_type, 'rows', true ); ?>
<div class="response"></div>
<p><button type="submit" class="remodal-confirm wpse-trigger-schedule-now"><?php _e( 'Schedule the changes', 'vg_sheet_editor_automations' ); ?></button>
<button type="button" class="remodal-secundario wpse-trigger-save-now" data-remodal-target="bulk-save"><?php _e( 'Save now', 'vg_sheet_editor_automations' ); ?></button>
<button data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', 'vg_sheet_editor_automations' ); ?></button>
</p>
</form>
	</div>
</div>
