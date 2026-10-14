function vgseFillInScheduleFields($wrapper, scheduleData) {

	$wrapper.find('[name="schedule_for_later"]').prop('checked', true).trigger('change');
	$wrapper.find('[name="schedule_for_later"]').parent().hide();
	for (var key in scheduleData) {
		var $input = $wrapper.find('.schedule-for-later-container [name="' + key + '"]');
		if (($input.length > 1 && $input.prop('type') === 'radio') || $input.prop('type') === 'checkbox') {
			$input.each(function () {
				if (jQuery(this).val() === scheduleData[key] || scheduleData[key] === true && jQuery(this).val() === 'yes') {
					jQuery(this).prop('checked', true).trigger('change');
				}
			});
		} else {
			$input.val(scheduleData[key]).trigger('change');
		}
	}
}

function vgseFillInFields($wrapper, data) {
	if (!data) {
		return true;
	}

	for (var key in data) {
		var $input = $wrapper.find('[name="' + key + '"]');
		if (($input.length > 1 && $input.prop('type') === 'radio') || $input.prop('type') === 'checkbox') {
			$input.each(function () {
				if (jQuery(this).val() === data[key] || data[key] === true && jQuery(this).val() === 'yes') {
					jQuery(this).prop('checked', true).trigger('change');
				}
			});
		} else {
			$input.val(data[key]).trigger('change');
		}
	}
}

// Scheduled jobs popup
jQuery(document).ready(function () {
	jQuery('body').on('vgSheetEditor:remodal:ajaxContentInserted', '.remodal.vgse-scheduled-actions-popup', function (event, response) {
		var $modal = jQuery(this);		
		var $newModal = jQuery(response.data.html);

		if( ! $modal.find('tbody').children().length ){
			$modal.find('.modal-content').replaceWith($newModal.find('.modal-content'));
		} else {
			$newModal.find('tr[data-schedule-data]').each(function(){
				var $existingRow = $modal.find('tr[data-schedule-data][data-job-id="'+ jQuery(this).attr('data-job-id') +'"]');

				if( $existingRow.length ){
					$existingRow.replaceWith( jQuery(this) );
				} else {
					$modal.find('tbody').prepend( jQuery(this) );
				}
			});
		}
	});

	jQuery(document).on('closed', '.remodal.vgse-scheduled-actions-popup', function () {
		var $modal = jQuery(this);		
		closeLog($modal);
	});
	// This is for all the modals during advanced editing, not for the scheduled jobs modal
	jQuery(document).on('closed', '.remodal:not(.vgse-scheduled-actions-popup)', function () {
		var $modal = jQuery(this);
		$modal.find('[name="schedule_for_later"]').prop('checked', false).trigger('change');
		// Show the schedule checkbox if we're closing a modal that was in edit-scheduled-job mode
		$modal.find('[name="schedule_for_later"]').parent().show();

		var $form = $modal.find('form');

		// Unset temp variables used only when editing a bulk edit
		if ($form.length && $form.data('originalFilters')) {
			jQuery('body').data('be-filters', $form.data('originalFilters'));
			$form.data('originalFilters', false);
			vgseReloadSpreadsheet();
		}
		if ($form.length && $form.data('jobId')) {
			$form.data('jobId', false);
		}
		if ($form.length && $form.data('columnMapping')) {
			$form.data('columnMapping', false);
		}
	});

	function cancelJobEdit() {
		var $tr = jQuery('.vgse-scheduled-actions-popup .edit-job-form');
		$tr.addClass('hidden').data('job-id', '').data('schedule-data', '');
	}

	jQuery('body').on('click', '.vgse-scheduled-actions-popup .close-log', function (e) {
		e.preventDefault;
		closeLog();
	});

	function closeLog($parent) {
		var $logRow = $parent ? $parent.find('tr.view-job-log') : jQuery('.vgse-scheduled-actions-popup tr.view-job-log');
		$logRow.addClass('hidden');
		if ($logRow.data('intervalId')) {
			clearInterval($logRow.data('intervalId'));
			$logRow.data('intervalId', false);
		}
	}
	function getLogContent(jobId, $logRow) {

		var requestData = {
			job_id: jobId,
			_wpnonce: vgse_editor_settings.rest_nonce
		};

		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/log',
			dataType: 'json',
			type: 'GET',
			data: requestData,
			success: function (response) {
				$logRow.find('.download-log-file').attr('href', response.log_url);
				var $textarea = $logRow.find('textarea');
				if ($textarea.data('last-value') !== response.lines) {
					$textarea.val(response.lines);
					$textarea.scrollTop($textarea.prop('scrollHeight') - $textarea.height() + 10 );
					$textarea.data('last-value', response.lines);
				}
			}
		});
	}
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .view-log-button', function (e) {
		e.preventDefault;
		var $button = jQuery(this);
		var $logRow = $button.parents('table').find('.view-job-log');

		if ($logRow.is(':visible')) {
			$logRow.find('.close-log').trigger('click');
			return true;
		}
		var $jobTr = $button.parents('tr');
		$logRow.insertAfter($jobTr).removeClass('hidden').data('job-id', $jobTr.data('job-id'));

		var $textarea = $logRow.find('textarea');
		$textarea.scrollTop($textarea.prop('scrollHeight') - $textarea.height() + 10 );

		getLogContent($jobTr.data('job-id'), $logRow);
		var intervalId = setInterval(function () {
			getLogContent($jobTr.data('job-id'), $logRow);
		}, parseInt(vgse_editor_settings.automations_log_reload_time) * 1000);
		$logRow.data('intervalId', intervalId);
	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .edit-job', function (e) {
		e.preventDefault;
		var $button = jQuery(this);
		var $editRow = $button.parents('table').find('.edit-job-form');
		var $jobTr = $button.parents('tr');
		$editRow.insertAfter($jobTr).removeClass('hidden').data('job-id', $jobTr.data('job-id'));
		if ($jobTr.data('schedule-data')) {
			var scheduleData = $jobTr.data('schedule-data');
			if( $jobTr.data('extra').task_args.real_time_export ){
				scheduleData.schedule_is_recurring = 'sync';
			}
			vgseFillInFields($editRow, scheduleData);
			var allExtraFields = wpse_automations_data.extra_settings_fields.exports.concat( wpse_automations_data.extra_settings_fields.imports );
			var extraFields = [];
			allExtraFields.forEach(function(fieldKey){
				extraFields[ fieldKey ] = $jobTr.data('extra').task_args[ fieldKey ];
			});
			vgseFillInFields($editRow, extraFields);
			$editRow.data('schedule-data', scheduleData);

			// Copy the job details into the edit section
			$editRow.find('.job-details').remove();
			if ($jobTr.find('.job-details').length) {
				$editRow.find('.schedule-fields-wrapper').after($jobTr.find('.job-details').clone().removeClass('hidden'));
			}

			if ($editRow.find('.select2').length) {
				vgseInitSelect2($editRow.find('.select2'));
			}
			jQuery('body').trigger('vgSheetEditor/automations/jobEditorOpened', [$jobTr, $editRow]);
		}
	});
	jQuery('body').on('submit', '.vgse-scheduled-actions-popup .edit-job-form form', function (e) {
		e.preventDefault();
		loading_ajax(true);
		var $tr = jQuery(this).parents('tr');
		var date = $tr.find('.schedule-for-later-container [name="schedule_for_later_date"]').val();
		var isRecurring = $tr.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'yes';
		var repeat_every = isRecurring ? parseInt($tr.find('.schedule-for-later-container [name="schedule_repeat_every"]').val()) : 0;
		var repeat_unit = $tr.find('.schedule-for-later-container [name="schedule_repeat_unit"]').val();
		var name = $tr.find('.schedule-for-later-container [name="scheduled_job_name"]').val();
		var email_notification_address = $tr.find('.schedule-for-later-container [name="schedule_for_later_email_address"]').val();
		var requestData = {
			job_id: $tr.data('job-id'),
			_wpnonce: vgse_editor_settings.rest_nonce,
			extra_data: $tr.prev().data('extra'),
			// Required to force WPSE to register columns for the current sheet, because we need the columns on this request
			post_type: vgse_editor_settings.post_type,
		};

		var originalData = $tr.data('schedule-data');
		if (originalData.scheduled_job_name !== name) {
			requestData.name = name;
		}
		if (originalData.schedule_for_later_date !== date) {
			requestData.date = date;
		}
		if (parseInt(originalData.schedule_repeat_every) !== repeat_every) {
			requestData.repeat_every = repeat_every;
		}
		if (originalData.schedule_repeat_unit !== repeat_unit) {
			requestData.repeat_unit = repeat_unit;
		}
		if (originalData.schedule_for_later_email_address !== email_notification_address) {
			requestData.email_notification_address = email_notification_address;
		}

		if(!requestData.date){
			delete requestData.date;
		}

		if($tr.hasClass('export-rows')){
			requestData.extra_data['task_args']['real_time_export'] = $tr.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'sync';
		}

		var taskArgsEditableInSimpleMode = wpse_automations_data.extra_settings_fields.exports.concat(wpse_automations_data.extra_settings_fields.imports);
		taskArgsEditableInSimpleMode.forEach(function (key) {
			var $field = $tr.find('[name="' + key + '"]');
			if ($field.length && $field.is(':visible')) {
				if ($field.prop('type') === 'checkbox') {
					requestData.extra_data['task_args'][key] = $field.prop('checked');
				} else {
					requestData.extra_data['task_args'][key] = $field.val();
				}
			}
		});

		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/edit-job',
			dataType: 'json',
			type: 'POST',
			data: requestData,
			success: function (response) {
				loading_ajax(false);
				cancelJobEdit();

				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});
		return false;
	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .edit-job-form .cancel-edit', function (e) {
		e.preventDefault;
		cancelJobEdit();
	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .execute-next-run', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/run-next-batch',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.hide();
				alert(response.message);

				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .execute-again', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/run-again',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.hide();
				alert(response.message);

				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .retry-batch', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/retry-failed-batch',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.hide();
				alert(response.message);

				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .resume-next-run', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/resume-job',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.addClass('hidden');
				$button.parents('tr').find('.pause-next-run').removeClass('hidden');
				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .pause-next-run', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/pause-job',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.addClass('hidden');
				$button.parents('tr').find('.resume-next-run').removeClass('hidden');
				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .cancel-next-run', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/cancel-next-batch',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.hide();
				$button.parents('tr').find('.pause-next-run, .resume-next-run').hide();

				$button.parents('tr').find('.execute-next-run').hide();
				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .restore-job', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/restore-job',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.hide();
				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});
	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .archive-job', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/archive-job',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				$button.hide();
				jQuery('.remodal[data-ajax-action]').trigger('vgSheetEditor:refreshRemodalContent');
			}
		});

	});
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .delete-job', function (e) {
		var $button = jQuery(this);
		e.preventDefault;

		if (!confirm(vgse_editor_settings.texts.confirm_delete_scheduled_job)) {
			return false;
		}

		closeLog( $button.parents('.remodal'));
		loading_ajax(true);
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/delete-job',
			dataType: 'json',
			type: 'POST',
			data: {
				job_id: $button.parents('tr').data('job-id'),
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				loading_ajax(false);
				cancelJobEdit();
				closeLog();
				$button.parents('tr').remove();
			}
		});

	});
});


// Schedule fields
jQuery(document).ready(function () {
	jQuery('body').on('change', '.schedule-for-later-container input[name="schedule_for_later_date"]', function (e) {
		var $localDate = jQuery(this).prev();
		var localDate = '';
		if (jQuery(this).val()) {
			var utcDateString = jQuery(this).val() + ' GMT';
			var dateObj = new Date(utcDateString);

			var year = dateObj.getFullYear();
			var month = ('0' + (dateObj.getMonth() + 1)).slice(-2);
			var day = ('0' + dateObj.getDate()).slice(-2);
			var hour = ('0' + dateObj.getHours()).slice(-2);
			var minute = ('0' + dateObj.getMinutes()).slice(-2);
			var second = ('0' + dateObj.getSeconds()).slice(-2);

			var localDate = year + '-' + month + '-' + day + 'T' + hour + ':' + minute + ':' + second;
		}
		$localDate.val(localDate);
	});
	jQuery('body').on('change', '.schedule-for-later-container input[name="schedule_for_later_local_date"]', function (e) {
		var $utcField = jQuery(this).next();
		var utcDate = jQuery(this).val() ? new Date(jQuery(this).val()).toISOString().replace('T', ' ').replace(/\.\d+Z$/, '') : '';
		$utcField.val(utcDate);
	});
	jQuery('body').on('change', '.schedule-for-later-container input[name="schedule_for_later"]', function (e) {
		var $saveForLater = jQuery(this).parents('form').find('.save-for-later-container');
		var $container = jQuery(this).parents('.schedule-for-later-container');
		var $popup = jQuery(this).parents('.remodal');
		if (jQuery(this).prop('checked')) {
			$container.find('.fields-scheduler').show();
			$container.find('.field-wrap:not(.if-recurring-yes) input:not([type="checkbox"])').prop('required', true);
			$saveForLater.text(vgse_editor_settings.texts.export_name_input_required);
			$saveForLater.parent().find('input').prop('required', true);
			$container.find('input[name="schedule_is_recurring"]:checked').trigger('change');
			$popup.find('.if-scheduled-for-later').show();
		} else {
			$container.find('.fields-scheduler').hide();
			$container.find('.field-wrap:not(.if-recurring-yes) input:not([type="checkbox"])').prop('required', false);
			$saveForLater.text(vgse_editor_settings.texts.export_name_input_optional);
			$saveForLater.parent().find('input').prop('required', false);
			$popup.find('.if-scheduled-for-later').hide();
		}
	});
	jQuery('body').on('change', '.schedule-for-later-container input[name="schedule_is_recurring"]', function (e) {
		var $container = jQuery(this).parents('.schedule-for-later-container');
		var conditionalClass = 'if-recurring-' + jQuery(this).val();
		var possibleClassesList = [];
		$container.find('input[name="schedule_is_recurring"]').each(function(){
			possibleClassesList.push('.if-recurring-' + jQuery(this).val());
		});
		var possibleClasses = possibleClassesList.join(',');

		$container.find(possibleClasses).hide().find('input,select').trigger('change');
		$container.find(possibleClasses).find('input:not([type="checkbox"])').prop('required', false);

		$container.find('.'+conditionalClass).show().find('input,select').trigger('change');
		$container.find('.'+conditionalClass).find('input:not([type="checkbox"])').prop('required', true);
	});
});