<?php defined( 'ABSPATH' ) || exit; ?>
<table>
	<thead>
		<tr>
			<th><?php _e( 'Name', 'vg_sheet_editor_automations' ); ?></th>
			<th><?php _e( 'Status', 'vg_sheet_editor_automations' ); ?></th>
			<th><?php _e( 'Recurring', 'vg_sheet_editor_automations' ); ?></th>
			<th><?php _e( 'Created', 'vg_sheet_editor_automations' ); ?></th>
			<th><?php _e( 'Previous run', 'vg_sheet_editor_automations' ); ?></th>
			<th><?php _e( 'Next run', 'vg_sheet_editor_automations' ); ?></th>
			<th><?php _e( 'Actions', 'vg_sheet_editor_automations' ); ?></th>
		</tr>
	</thead>
	<tbody>

		<?php

		foreach ( $rows as $row ) {
			$extra_data = array();
			if ( WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) {
				$extra_data['task_args'] = $row['task_args'];
			}
			$extra_data         = apply_filters( 'vg_sheet_editor/automations/scheduled_jobs_table/extra_job_data/' . $row['category'], $extra_data, $row );
			$created_human_time = sprintf( __( '%s ago', 'vg_sheet_editor_automations' ), human_time_diff( current_time( 'timestamp' ), strtotime( $row['formatted_created'] ) ) );
			$prev_run_human_time = $row['formatted_prev_run'] ? sprintf( __( '%s ago', 'vg_sheet_editor_automations' ), human_time_diff( current_time( 'timestamp' ), strtotime( $row['formatted_prev_run'] ) ) ) : '';
			$next_run_human_time = $row['formatted_next_run'] ? sprintf( __( 'In %s', 'vg_sheet_editor_automations' ), human_time_diff( current_time( 'timestamp' ), strtotime( $row['formatted_next_run'] ) ) ) : '';
			?>
		<tr data-job-id="<?php echo esc_attr( $row['job_id'] ); ?>"
			data-schedule-data="<?php echo esc_attr( wp_json_encode( $row['editable_data'] ) ); ?>"
			data-extra="<?php echo esc_attr( wp_json_encode( $extra_data ) ); ?>">
			<td><?php echo wp_kses_post( $row['job_name'] ); ?></td>
			<td><span data-wpse-tooltip="up" aria-label="<?php echo esc_attr( WPSE_Automations_Core::get_status_description( $row['status'] ) ); ?>"><?php echo esc_html( apply_filters( 'vg_sheet_editor/automations/scheduled_jobs_table/formatted_status/' . $row['category'], $row['formatted_status'], $row, $extra_data ) ); ?></span></td>
			<td><?php echo esc_html( $row['recurring'] ); ?></td>
			<td><span data-wpse-tooltip="up" aria-label="<?php echo esc_attr( $created_human_time ); ?>"><?php echo esc_html( $row['formatted_created'] ); ?></span></td>
			<td><span data-wpse-tooltip="up" aria-label="<?php echo esc_attr( $prev_run_human_time ); ?>"><?php echo $row['formatted_prev_run'] ? esc_html( $row['formatted_prev_run'] ) : ''; ?></span></td>
			<td><span data-wpse-tooltip="up" aria-label="<?php echo esc_attr( $next_run_human_time ); ?>"><?php echo $row['formatted_next_run'] ? esc_html( $row['formatted_next_run'] ) : ''; ?></span>
			</td>
			<td class="scheduled-actions">
				<button type="button" class="button view-log-button" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'View the log', 'vg_sheet_editor_automations' ); ?>"><i
						class="fa fa-eye"></i></button>
				<?php
				if ( empty( (int) $row['editable_data']['schedule_repeat_every'] ) && $row['status'] === 'completed' ) {
					?>
				<button type="button" class="button execute-again" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'Run this again', 'vg_sheet_editor_automations' ); ?>"><i
						class="fa fa-rotate-left"></i></button>
				<?php } ?>
				<?php if ( $row['formatted_next_run'] ) { ?>
				<button type="button" class="button execute-next-run" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'Run the next batch right now. If the process is in progress, this will run the next batch right now and the other batches will be rescheduled relative to this batch until the process finishes, but the future recurring dates won\'t be rescheduled and they\'ll still use the original dates', 'vg_sheet_editor_automations' ); ?>">
				<i class="fa fa-step-forward"></i>
			</button>
				<button type="button" class="button resume-next-run 
					<?php
					if ( $row['status'] !== 'paused' ) {
						echo 'hidden';}
					?>
		" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'Resume the process if you paused it previously. If you resume before the original schedule date, we\'ll use the original date. If you resume after the original date, we\'ll reschedule the next batch to run right now', 'vg_sheet_editor_automations' ); ?>"><i
						class="fa fa-play"></i></button>
				<button type="button" class="button pause-next-run 
					<?php
					if ( $row['status'] === 'paused' ) {
						echo 'hidden';}
					?>
		" data-wpse-tooltip="up"
					aria-label="<?php echo esc_attr( __( 'Pause the process.', 'vg_sheet_editor_automations' ) . '. ' . WPSE_Automations_Core::get_status_description( 'paused' ) ); ?>"><i
						class="fa fa-pause"></i></button>
				<button type="button" class="button cancel-next-run" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'Stop the process completely. If the process was in progress, the next batch won\'t be executed. If the process was finished and waiting for the next recurring date, it won\'t start again. The log file will still exist.', 'vg_sheet_editor_automations' ); ?>"><i
						class="fa fa-stop"></i></button>
				<?php } ?>
				<?php if ( WPSE_Automations_Core::user_can_edit_its_own_job( $row['job_id'] ) ) { ?>
				<button type="button" class="button edit-job" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'Edit the scheduled job.', 'vg_sheet_editor_automations' ); ?>"><i
						class="fa fa-edit"></i></button>
				<?php } ?>
				<?php do_action( 'vg_sheet_editor/automations/scheduled_jobs_table/' . $row['category'] . '/before_delete_job_button', $row, $post_type ); ?>
				<?php if ( $row['status'] !== 'archived' ) { ?>
				<button type="button" class="button archive-job" data-wpse-tooltip="up"
					aria-label="<?php echo esc_attr( __( 'Archive the job', 'vg_sheet_editor_automations' ) . '. ' . WPSE_Automations_Core::get_status_description( 'archived' ) ); ?>"><i
						class="fa fa-download"></i></button>
						<?php } else { ?>
				<button type="button" class="button restore-job" data-wpse-tooltip="up"
					aria-label="<?php echo esc_attr( __( 'Restore the job', 'vg_sheet_editor_automations' ) . '. ' . WPSE_Automations_Core::get_status_description( 'restored' ) ); ?>"><i
						class="fa fa-upload"></i></button>
							<?php } ?>
				<button type="button" class="button delete-job" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'We\'ll delete the scheduled job and all the related files, including the log files and csv files created through this scheduled job. If you don\'t want to delete the files yet, you should archive the job', 'vg_sheet_editor_automations' ); ?>"><i
						class="fa fa-trash"></i></button>
				<?php do_action( 'vg_sheet_editor/automations/scheduled_jobs_table/' . $row['category'] . '/after_all_actions', $row, $post_type ); ?>
			</td>
		</tr>
			<?php
		}
		?>
		<!-- The next <tr>'s should be job agnostic because all the jobs will use the elements -->
		<tr class="edit-job-form hidden <?php echo sanitize_html_class( $row['category'] ); ?>" data-job-id="">
			<td colspan="7">
				<form method="POST" action="">
					<?php do_action( 'vg_sheet_editor/automations/scheduled_jobs_table/' . $row['category'] . '/edit_form_before_core_fields', $post_type ); ?>
					<div class="schedule-fields-wrapper">
						<h2><?php esc_html_e( 'Schedule settings', 'vg_sheet_editor_automations' ); ?></h2>
						<?php WPSE_Automations_Core_Obj()->render_form_fields( $post_type, $row['category'], true, true ); ?>
					</div>
					<?php do_action( 'vg_sheet_editor/automations/scheduled_jobs_table/' . $row['category'] . '/edit_form_after_core_fields', $post_type ); ?>
					<p class="form-actions">
						<button type="submit"
							class="button button-primary save-edit"><?php esc_html_e( 'Save', 'vg_sheet_editor_automations' ); ?></button>
						<button type="button"
							class="button cancel-edit"><?php esc_html_e( 'Cancel', 'vg_sheet_editor_automations' ); ?></button>
						<?php do_action( 'vg_sheet_editor/automations/scheduled_jobs_table/' . $row['category'] . '/edit_form_after_action_buttons', $post_type ); ?>
					</p>
				</form>
			</td>
		</tr>
		<tr class="view-job-log hidden <?php echo sanitize_html_class( $row['category'] ); ?>" data-job-id="">
			<td colspan="7">
				<a class="button download-log-file" href="#" target="_blank" data-wpse-tooltip="up"
					aria-label="<?php esc_attr_e( 'This file contains everything that\'s happened in the process and you can use it to find any errors or issues that might be causing the process to stall, you can also send this file to the support team of WP Sheet Editor if you experience issues.', 'vg_sheet_editor_automations' ); ?>"><?php _e( 'Download log file', 'vg_sheet_editor_automations' ); ?></a>
				<button type="button" class="button close-log">Close</button>
				<p><?php printf( __( 'The log is reloaded every %d seconds', 'vg_sheet_editor_automations' ), VGSE()->get_option( 'automations_log_reload_time', 30 ) ); ?>  
				</p>
				<textarea readonly rows="10"></textarea>
			</td>
		</tr>
	</tbody>
</table>
