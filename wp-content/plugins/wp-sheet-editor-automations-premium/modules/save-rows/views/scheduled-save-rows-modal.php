<?php defined( 'ABSPATH' ) || exit; ?>
<div class="remodal scheduled-save-rows-modal <?php if ( ! empty( $rows ) ) {
	echo 'remodal-extra-large';} ?>" data-remodal-id="scheduled-save-rows-modal" data-remodal-options="closeOnOutsideClick: false, hashTracking: false">
	<div class="modal-content">
		<h3><?php _e( 'Scheduled cell changes', 'vg_sheet_editor_automations' ); ?></h3>
<?php if ( empty( $rows ) ) { ?>
<p><?php _e( 'You haven\'t scheduled cell changes yet.', 'vg_sheet_editor_automations' ); ?></p>
	<?php
} else {
	include_once WPSE_Automations_Init::$dir . '/modules/core/views/scheduled-jobs-table.php';
}
?>
		<button data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', 'vg_sheet_editor_automations' ); ?></button>
	</div>
</div>
