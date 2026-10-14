// Save later modal

jQuery(document).ready(function () {
	var $saveLaterModal = jQuery('.save-later-modal');
	if (!$saveLaterModal.length) {
		return true;
	}
	jQuery('body').on('vgSheetEditor/automations/jobEditorOpened', function (e, $jobTr, $editRow) {

		var extraData = $jobTr.data('extra');
		// Saved cells
		if (typeof extraData === 'object' && extraData && extraData.cells_data) {

			var cellHandsontableArgs = {
				data: extraData.cells_data,
				width: $editRow.find('.schedule-fields-wrapper').width(),
				height: $editRow.find('.schedule-fields-wrapper').height(),
				wordWrap: true,
				allowInsertRow: false,
				columnSorting: true,
				colHeaders: extraData.column_titles,
			};
			window.scheduledSaveCellsHOT = new Handsontable($editRow.find('.job-details .editable-cells')[0], cellHandsontableArgs);
		}

	});
	var $form = $saveLaterModal.find('form');

	$form.on('submit', function (e) {
		e.preventDefault();

		var $response = $form.find('.response');
		$response.empty();

		var fullData = hot.getSourceData();
		fullData = beGetModifiedItems(fullData, window.beOriginalData);

		// No posts to save found
		if (!fullData.length) {

			$response.append('<p>' + vgse_editor_settings.texts.no_changes_to_save + '</p>');
			loading_ajax({ estado: false });

			vgseAllowToClosePageWithoutWarning(true);
			setTimeout(function () {
				beSetSaveButtonStatus(false);
				$progress.find('.remodal-cancel').click();
			}, 500);
			notification({ mensaje: vgse_editor_settings.texts.no_changes_to_save, tipo: 'info' });
			return true;
		}

		loading_ajax(true);

		var scheduleDate = $form.find('.schedule-for-later-container [name="schedule_for_later_date"]').val();
		var isRecurring = $form.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'yes';
		var repeatEvery = isRecurring ? parseInt($form.find('.schedule-for-later-container [name="schedule_repeat_every"]').val()) : 0;
		var repeatUnit = $form.find('.schedule-for-later-container [name="schedule_repeat_unit"]').val();

		// We use a hash of the modified cells as the job id + post type + scheduleDate, to make 
		// sure only one job is scheduled per unique cell changes per date
		// For example, we don't want to schedule multiple jobs for making the same edits
		var jobId = vgseMd5(JSON.stringify(fullData) + vgse_editor_settings.post_type + scheduleDate);
		var dataForTask = {
			data: fullData,
			sheet_key: vgse_editor_settings.post_type,
			filters: vgseGetFiltersJson(),
			wpse_source_suffix: vgse_editor_settings.wpse_source_suffix || '',
			wpse_job_id: jobId,
			per_page: vgse_editor_settings.automations_save_cells_batch_size
		};
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/rows',
			dataType: 'json',
			type: 'POST',
			data: {
				sheet_key: vgse_editor_settings.post_type,
				date: scheduleDate,
				repeat_every: repeatEvery,
				repeat_unit: repeatUnit,
				email_notification_address: $form.find('.schedule-for-later-container [name="schedule_for_later_email_address"]').val(),
				name: $form.find('.schedule-for-later-container [name="scheduled_job_name"]').val(),
				verbose_log: vgse_editor_settings.automations_verbose_logs,
				wpse_job_id: jobId,
				task_args: dataForTask,
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				if (response.success) {
					vgseAllowToClosePageWithoutWarning(true);
					$saveLaterModal.find('.wpse-trigger-schedule-now, .wpse-trigger-save-now').hide();
					$response.empty();
					$response.append(response.message || vgse_editor_settings.texts.save_rows_scheduled_in_popup);
				}
			}
		});
		return false;
	});
	jQuery(document).on('opened', '.save-later-modal', function () {
		$saveLaterModal.find('.wpse-trigger-schedule-now, .wpse-trigger-save-now').show();
		$saveLaterModal.find('.response').empty();
		$saveLaterCheckbox = $saveLaterModal.find('[name="schedule_for_later"]');
		$saveLaterCheckbox.prop('checked', true).trigger('change');
		$saveLaterCheckbox.parent().hide();
	});

});