jQuery(document).ready(function () {
	var $aiAddNewModal = jQuery('.remodal-add-new-ai');
	if (!$aiAddNewModal.length) {
		return true;
	}
	var $addNewForm = $aiAddNewModal.find('form');

	$addNewForm.on('submit', function (e) {
		e.preventDefault();
		// Exit if this is not a scheduled job
		if (!$addNewForm.find('[name="schedule_for_later"]').prop('checked')) {
			return true;
		}
		var number_rows = parseInt($addNewForm.find('[name="number_rows"]').val());
		var columns_auto_fill = $addNewForm.find('.columns-auto-fill').val();
		var prompt = $addNewForm.find('[name="prompt"]').val();

		var $response = $addNewForm.find('.response').empty();
		$('#be-add-new-nanobar-container').remove();


		loading_ajax(true);

		var scheduleDate = $addNewForm.find('.schedule-for-later-container [name="schedule_for_later_date"]').val();
		var isRecurring = $addNewForm.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'yes';
		var repeatEvery = isRecurring ? parseInt($addNewForm.find('.schedule-for-later-container [name="schedule_repeat_every"]').val()) : 0;
		var repeatUnit = $addNewForm.find('.schedule-for-later-container [name="schedule_repeat_unit"]').val();
		var jobId = $addNewForm.data('jobId') || vgseGuidGenerator();

		var dataForTask = {
			_wpnonce: vgse_editor_settings.rest_nonce,
			sheet_key: vgse_editor_settings.post_type,
			prompt,
			columns_auto_fill,
			wpse_job_id: jobId,
			number_rows,
			// These fields aren't used by the add-new endpoint, but are used by the scheduler to track progress
			per_page: vgse_editor_settings.duplicate_batch_size,
			processed_rows: 0,
			total: number_rows,
		};
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/ai/schedule/add-new',
			dataType: 'json',
			type: 'POST',
			data: {
				sheet_key: vgse_editor_settings.post_type,
				date: scheduleDate,
				repeat_every: repeatEvery,
				repeat_unit: repeatUnit,
				email_notification_address: $addNewForm.find('.schedule-for-later-container [name="schedule_for_later_email_address"]').val(),
				name: $addNewForm.find('.schedule-for-later-container [name="scheduled_job_name"]').val(),
				verbose_log: vgse_editor_settings.automations_verbose_logs,
				wpse_job_id: jobId,
				task_args: dataForTask,
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				if (response.success) {
					$response.empty();
					$response.append(response.message || vgse_editor_settings.texts.ai_add_new_scheduled_in_popup);

					// If we edited a job, go to the scheduled jobs table
					if ($addNewForm.data('jobId')) {
						vgseGoToModal('vgse_scheduled_ai_add_new_modal');
						notification({ mensaje: vgse_editor_settings.texts.scheduled_ai_add_new_edited_successfully, tipo: 'success', tiempo: 4000 });
					}
				}
			}
		});
		return false;
	});
	jQuery(document).on('opened', '.remodal-add-new-ai', function () {
		$aiAddNewModal.find('.response').empty();
	});


	jQuery('body').on('click', '.vgse-scheduled-actions-popup .advanced-edit-ai-add-new', function (e) {
		e.preventDefault;
		var $modal = $aiAddNewModal;
		var taskArgs = jQuery('.edit-job-form.add-new').prev().data('extra').task_args;
		var scheduleData = jQuery('.edit-job-form.add-new').prev().data('schedule-data');
		var $editRow = jQuery(this).parents('table').find('.edit-job-form');

		$modal.remodal().open();
		$modal.find('[name="number_rows"]').val(taskArgs.number_rows).trigger('change');
		$modal.find('.columns-auto-fill').val(taskArgs.columns_auto_fill).trigger('change');
		$modal.find('[name="prompt"]').val(taskArgs.prompt).trigger('change');

		$modal.find('form').data('jobId', $editRow.data('job-id'));
		$modal.find('form').data('previousTaskArgs', taskArgs);

		vgseFillInScheduleFields($modal, scheduleData);

		// Close the edit row because we don't want it to stay open when we return
		jQuery(this).parents('tr').find('.cancel-edit').trigger('click');
	});

});