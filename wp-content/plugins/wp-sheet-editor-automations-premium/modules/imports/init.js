// Import tool
jQuery(document).ready(function ($) {
	var $importForm = jQuery('.import-csv-form');
	if (!$importForm.length) {
		return true;
	}
	
    var $importModal = jQuery('.import-csv-modal');
    $importModal.find('.data-input.ftp [name="ftp_file_path"]').on('change', function(e){
        $importModal.find('.data-input.ftp .data').val( $importModal.find('.data-input.ftp [name="ftp_server"]').val() + ':' + jQuery(this).val());
    });
    $importModal.find('.data-input.ftp [name="ftp_server"]').on('change', function(e){

        if( jQuery(this).val() ){
            $importModal.find('.ftp-file-wrapper').show();
        } else {
            $importModal.find('.ftp-file-wrapper').hide();
        }
	});

	jQuery('body').on('click', '.vgse-scheduled-actions-popup .advanced-edit-import', function (e) {
		e.preventDefault;
		var $modal = jQuery('.import-csv-modal');
		var taskArgs = jQuery('.edit-job-form.import-rows').prev().data('extra').task_args;
		var scheduleData = jQuery('.edit-job-form.import-rows').prev().data('schedule-data');
		var $editRow = jQuery(this).parents('table').find('.edit-job-form');

		$modal.remodal().open();
		$modal.find('[name="source"]').val(taskArgs.source).trigger('change');
		if (taskArgs.server_file) {
			$modal.find('[name="server_file"]').val(taskArgs.server_file).trigger('change');
		}
		if (taskArgs.file_url) {
			$modal.find('[name="file_url"]').val(taskArgs.file_url).trigger('change');
		}
		$modal.find('[name="separator"]').val(taskArgs.separator).trigger('change');
		$modal.find('[name="writing_type"]').val(taskArgs.writing_type).trigger('change');

		if ($modal.find('.data-input.google_sheets').length && taskArgs.google_sheet_file) {
			var sheetId = taskArgs.google_sheet_file.split(':')[0];
			var $sheetSelector = $modal.find('.data-input.google_sheets .sheet-selector');
			$sheetSelector.empty();
			$sheetSelector.append('<option value="' + sheetId + '">' + sheetId + '</option>');
			$sheetSelector.val(sheetId).trigger('change');

			$modal.find('.data-input.google_sheets input[name="google_sheet_file"]').val(taskArgs.google_sheet_file);

			var $worksheet = $modal.find('.data-input.google_sheets select[name="google_sheet_worksheet"]');
			$worksheet.empty();
			$worksheet.append('<option value="' + taskArgs.google_sheet_worksheet + '">' + taskArgs.google_sheet_worksheet + '</option>');
			$worksheet.val(taskArgs.google_sheet_worksheet);
		}

		var extraFields = [];
		wpse_automations_data.extra_settings_fields.imports.forEach(function(fieldKey){
			extraFields[ fieldKey ] = taskArgs[ fieldKey ];
		});
		vgseFillInFields($modal, extraFields);

		var mapping = {};
		taskArgs.source_column.forEach(function (value, index) {
			mapping[value] = taskArgs.sheet_editor_column[index];
		});
		$modal.find('form').data('columnMapping', mapping);

		$modal.find('form').data('jobId', $editRow.data('job-id'));
		$modal.find('form').data('previousTaskArgs', taskArgs);

		vgseFillInScheduleFields($modal, scheduleData);

		// Close the edit row because we don't want it to stay open when we return
		jQuery(this).parents('tr').find('.cancel-edit').trigger('click');
	});

	$importForm.find('[name="schedule_is_recurring"]').on('change', function (e) {
		var $disallowedSources = $importForm.find('[name="source"] option[value="csv_upload"],[name="source"] option[value="paste"]');
		if (jQuery(this).val() === 'yes') {
			$disallowedSources.hide();
		} else {
			$disallowedSources.show();
		}
	});

	var $importModal = jQuery('.import-csv-modal');
	jQuery(document).on('closed', '.import-csv-modal', function () {
		$importModal.find('.scheduled-import-response').addClass('hidden');
		$importForm.find('.remodal-cancel').show();
		$importForm.find('.remodal-confirm').show();
	});
	$importForm.on('submit', function (e) {
		// Exit if this is not a scheduled job
		if (!$importForm.find('[name="schedule_for_later"]').prop('checked')) {
			return true;
		}
		e.preventDefault();
		loading_ajax(true);
		$importModal.find('.import-step').hide();
		$importModal.find('.import-actions').hide();
		$importForm.find('.remodal-cancel').hide();
		$importForm.find('.remodal-confirm').hide();
		$importModal.find('.scheduled-import-response').removeClass('hidden');
		var $response = $importModal.find('.scheduled-import-response .response');
		$response.empty();
		var jobId = $importForm.data('jobId') || ['import', vgseFormatDate(), jQuery('#post-data').data('post-type'), vgseGuidGenerator()].join('-');
		$importForm.find('input[name="wpse_job_id"]').val(jobId);

		var customPerPage = parseInt( $importForm.find('[name="per_page"]').val() );

		var dataForImport = formToObject('import-csv-form');
		dataForImport.file_position = 0;
		dataForImport.page = 1;
		dataForImport.per_page = ( customPerPage && customPerPage <= vgse_editor_settings.automations_import_batch_size ) ? customPerPage : vgse_editor_settings.automations_import_batch_size;

		// Remove the schedule fields from the taskArgs
		delete dataForImport.schedule_for_later;
		delete dataForImport.schedule_for_later_date;
		delete dataForImport.schedule_for_later_email_address;
		delete dataForImport.schedule_is_recurring;
		delete dataForImport.schedule_repeat_every;
		delete dataForImport.schedule_repeat_unit;
		delete dataForImport.scheduled_job_name;

		// Manually get the column mapping because formToObject skips empty selects breaking the mapping relationships
		var sourceColumns = [];
		var sheetEditorColumns = [];
		$importForm.find('.map-columns .map-template:not(.hidden)').each(function () {
			var sheetEditorColumn = jQuery(this).find('select').val();
			if (sheetEditorColumn) {
				sourceColumns.push(jQuery(this).find('.csv-column-name-value').val());
				sheetEditorColumns.push(sheetEditorColumn);
			}
		});
		dataForImport.source_column = sourceColumns;
		dataForImport.sheet_editor_column = sheetEditorColumns;


		var scheduleDate = $importForm.find('.schedule-for-later-container [name="schedule_for_later_date"]').val();
		var isRecurring = $importForm.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'yes';
		var repeatEvery = isRecurring ? parseInt($importForm.find('.schedule-for-later-container [name="schedule_repeat_every"]').val()) : 0;
		var repeatUnit = $importForm.find('.schedule-for-later-container [name="schedule_repeat_unit"]').val();

		if( dataForImport.real_time_import ){
			dataForImport.real_time_import = dataForImport.real_time_import === 'yes' && ! isRecurring;
		}

		dataForImport.trash_wp_rows_missing_from_gs = $importForm.find('.data-input.' + dataForImport.source + ' [name="trash_wp_rows_missing_from_gs"]').prop('checked');
		dataForImport.outofstock_missing_rows = $importForm.find('.data-input.' + dataForImport.source + ' [name="outofstock_missing_rows"]').prop('checked');

		dataForImport.skip_broken_images = dataForImport.skip_broken_images && dataForImport.skip_broken_images === 'on';
		dataForImport.auto_column_names = dataForImport.auto_column_names && dataForImport.auto_column_names === 'yes';
		dataForImport.pending_post_if_image_failed = dataForImport.pending_post_if_image_failed && dataForImport.pending_post_if_image_failed === 'on';
		dataForImport.decode_quotes = dataForImport.decode_quotes && dataForImport.decode_quotes === 'on';

		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/import-rows',
			dataType: 'json',
			type: 'POST',
			data: {
				sheet_key: vgse_editor_settings.post_type,
				date: scheduleDate,
				repeat_every: repeatEvery,
				repeat_unit: repeatUnit,
				email_notification_address: $importForm.find('.schedule-for-later-container [name="schedule_for_later_email_address"]').val(),
				name: $importForm.find('.schedule-for-later-container [name="scheduled_job_name"]').val(),
				verbose_log: vgse_editor_settings.automations_verbose_logs,
				wpse_job_id: jobId,
				task_args: dataForImport,
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				if (response.success) {
					$response.empty();
					$response.append(response.message || vgse_editor_settings.texts.import_scheduled_in_popup);
				}

				// If we edited a job, go to the scheduled jobs table
				if ($importForm.data('jobId')) {
					vgseGoToModal('vgse_scheduled_imports_modal');
					notification({ mensaje: vgse_editor_settings.texts.scheduled_import_edited_successfully, tipo: 'success', tiempo: 4000 });
				}
			}
		});
		return false;
	});

});
