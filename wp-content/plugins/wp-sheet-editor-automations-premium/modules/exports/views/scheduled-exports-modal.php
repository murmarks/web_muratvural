<?php defined( 'ABSPATH' ) || exit; ?>
<div class="remodal scheduled-exports-modal <?php if ( ! empty( $rows ) ) {
	echo 'remodal-extra-large';} ?>" data-remodal-id="scheduled-exports-modal" data-remodal-options="closeOnOutsideClick: false, hashTracking: false">
	<div class="modal-content">
		<h3><?php _e( 'Scheduled exports', 'vg_sheet_editor_automations' ); ?></h3>
<?php if ( empty( $rows ) ) { ?>
<p><?php _e( 'You haven\'t scheduled exports yet.', 'vg_sheet_editor_automations' ); ?></p>
<p><button type="button" data-remodal-target="export-csv-modal" class="remodal-confirm"><?php _e( 'Schedule a new export', 'vg_sheet_editor_automations' ); ?></button></p>
	<?php
} else {
	include_once WPSE_Automations_Init::$dir . '/modules/core/views/scheduled-jobs-table.php';
}
?>
		<button data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', 'vg_sheet_editor_automations' ); ?></button>
	</div>
</div>
