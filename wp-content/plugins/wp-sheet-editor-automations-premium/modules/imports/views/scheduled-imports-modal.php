<?php defined( 'ABSPATH' ) || exit; ?>
<div class="remodal scheduled-imports-modal <?php if ( ! empty( $rows ) ) {
	echo 'remodal-extra-large';} ?>" data-remodal-id="scheduled-imports-modal" data-remodal-options="closeOnOutsideClick: false, hashTracking: false">
	<div class="modal-content">
		<h3><?php _e( 'Scheduled imports', 'vg_sheet_editor_automations' ); ?></h3>
<?php if ( empty( $rows ) ) { ?>
<p><?php _e( 'You haven\'t scheduled imports yet.', 'vg_sheet_editor_automations' ); ?></p>
<p><button type="button" data-remodal-target="import-csv-modal" class="remodal-confirm"><?php _e( 'Schedule a new import', 'vg_sheet_editor_automations' ); ?></button></p>
	<?php
} else {
	include_once WPSE_Automations_Init::$dir . '/modules/core/views/scheduled-jobs-table.php';
}
?>
		<button data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', 'vg_sheet_editor_automations' ); ?></button>
	</div>
</div>
