// Export tool
jQuery(document).ready(function () {
	var $exportCsv = jQuery('.export-csv-form');
	if (!$exportCsv.length) {
		return true;
	}


	jQuery('body').on('click', '.vgse-scheduled-actions-popup .advanced-edit-export', function (e) {
		e.preventDefault;
		var $modal = jQuery('.export-csv-modal');
		var taskArgs = jQuery('.edit-job-form.export-rows').prev().data('extra').task_args;
		var scheduleData = jQuery('.edit-job-form.export-rows').prev().data('schedule-data');
		var $editRow = jQuery(this).parents('table').find('.edit-job-form');

		if( taskArgs.real_time_export ){
			scheduleData.schedule_is_recurring = 'sync';
		}

		$modal.remodal().open();
		$modal.find('select.export-columns').val(taskArgs.custom_enabled_columns.split(',')).trigger('change');

		var taskFilters = typeof taskArgs.filters === 'string' ? JSON.parse(taskArgs.filters) : taskArgs.filters;
		if (jQuery.param(taskFilters) !== beGetRowsFilters()) {
			$modal.find('form').data('originalFilters', beGetRowsFilters());
			jQuery('body').data('be-filters', taskFilters);
			vgseReloadSpreadsheet();
		}
		$modal.find('.wpse-select-rows-options').val('current_search');

		var extraFields = [];
		wpse_automations_data.extra_settings_fields.exports.forEach(function(fieldKey){
			extraFields[ fieldKey ] = taskArgs[ fieldKey ];
		});
		vgseFillInFields($modal, extraFields);

		if(taskArgs.google_sheet_id){
			$modal.find('.advanced-gs-options').prop('checked', true).trigger('change');
			$modal.find('[name="google_sheet_id"]').append('<option value="'+taskArgs.google_sheet_id+'">'+taskArgs.google_sheet_id+'</option>').val(taskArgs.google_sheet_id).trigger('change');
		}

		$modal.find('[name="target_software"]').val(taskArgs.target_software).trigger('change');
		$modal.find('[name="save_for_later_name"]').val(taskArgs.save_for_later_name).trigger('change');

		$modal.find('form').data('jobId', $editRow.data('job-id'));
		$modal.find('form').data('previousTaskArgs', taskArgs);

		vgseFillInScheduleFields($modal, scheduleData);

		// Close the edit row because we don't want it to stay open when we return
		jQuery(this).parents('tr').find('.cancel-edit').trigger('click');
	});

	$exportCsv.on('submit', function (e) {
		// Exit if this is not a scheduled exports
		if (!$exportCsv.find('[name="schedule_for_later"]').prop('checked')) {
			return true;
		}

		var foundRowsToEdit = vgseGetSelectedRowsCount($exportCsv);
		if ($exportCsv.find('.wpse-select-rows-options').val() === 'selected') {
			var selectedIds = vgseGetSelectedIds();
			foundRowsToEdit = selectedIds.length;

			if (foundRowsToEdit) {
				var originalFilters = beGetRowsFilters();
				beAddRowsFilter('post__in=' + selectedIds.join(','));
			}
		}

		// Disabled this because we need to allow them to schedule future exports with filters that return zero rows now, but might return rows in the future
		// if (!vgseGetSelectedRowsCount($exportCsv)) {
		// 	alert(vgse_editor_settings.texts.no_rows_for_export);
		// 	return false;
		// }

		var $response = $exportCsv.find('.export-response');
		$response.empty();
		$exportCsv.find('#be-export-nanobar-container').remove();
		$response.before('<div id="be-export-nanobar-container"></div>');
		var options = {
			classname: 'be-progress-bar',
			target: document.getElementById('be-export-nanobar-container')
		};

		var pageSize = vgse_editor_settings.automations_export_batch_size;
		var jobId = $exportCsv.data('jobId') || [vgseFormatDate(), jQuery('#post-data').data('post-type'), vgseGuidGenerator()].join('-');
		var dataForExport = {
			page: 1,
			action: 'vgse_load_data',
			vgse_plain_mode: 'yes',
			posts_per_page: pageSize,
			vgse_csv_export: 'yes',
			custom_enabled_columns: $exportCsv.find('.export-columns').val().join(','),
			line_items_separate_rows: $exportCsv.find('.wpse_line_items_separate_rows').prop('checked') || 0,
			target_software: $exportCsv.find('.excel-compatibility-container select').val(),
			nonce: jQuery('#vgse-wrapper').data('nonce'),
			post_type: jQuery('#post-data').data('post-type'),
			filters: vgseGetFiltersJson(),
			wpse_job_id: jobId,
			wpse_source_suffix: vgse_editor_settings.wpse_source_suffix || '',
			real_time_export: $exportCsv.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'sync',
		};

		// Set this variable to true if the export is scheduled for real time sync, so that other features that depend on this work without a page reload, like the formulas that disable the fast delete for real time sync.
		if (dataForExport.real_time_export) {
			vgse_editor_settings.sheet_uses_real_time_sync = true;
		}

		wpse_automations_data.extra_settings_fields.exports.forEach(function(fieldKey){
			var $field = $exportCsv.find('[name="'+fieldKey+'"]');
			if( ! $field.length ){
				return true;
			}
			if( $field.is('input:radio') ){
				dataForExport[ fieldKey ] = $exportCsv.find('.schedule-for-later-container [name="'+fieldKey+'"]:checked').val();
			} else if( $field.is('input:checkbox') ){
				dataForExport[ fieldKey ] = $field.prop('checked');
			} else {
				dataForExport[ fieldKey ] = $field.val();
			}
		});

		// When we edit an export job connected to a Google Sheet file, continue using the same Google Sheet file
		if( $exportCsv.data('previousTaskArgs') && $exportCsv.data('previousTaskArgs').google_sheet_id ){
			dataForExport.google_sheet_id = $exportCsv.data('previousTaskArgs').google_sheet_id;
		}

		if ($exportCsv.find('[name="save_for_later_name"]').val()) {
			dataForExport.save_for_later = {
				name: $exportCsv.find('[name="save_for_later_name"]').val(),
				columns: dataForExport.custom_enabled_columns,
				target_software: dataForExport.target_software,
				filters: dataForExport.filters,
				post_type: dataForExport.post_type,
			};
			var $toolbar = jQuery('.export_csv-container');
			if (!$toolbar.find('.toolbar-submenu').length) {
				$toolbar.append('<div class="toolbar-submenu"></div>');
			}
			var newIndex = $toolbar.find('.toolbar-submenu').length;
			$toolbar.find('.toolbar-submenu .button:contains(' + dataForExport.save_for_later.name + ')').parent().remove();
			$toolbar.find('.toolbar-submenu').append('<div class="button-container save_export-container"><button name="save_export' + newIndex + '" class="button" data-start-saved-export>' + dataForExport.save_for_later.name + '</button></div>');
			$toolbar.find('.toolbar-submenu').find('.button').last().data('start-saved-export', dataForExport.save_for_later);
		}
		var nanobar = new Nanobar(options);
		// We start progress bar with 1% so it doesn't look completely empty
		nanobar.go(1);
		$exportCsv.find('.remodal-cancel').hide();
		$exportCsv.find('.remodal-confirm').hide();

		// Schedule export
		dataForExport.save_for_later_name = $exportCsv.find('[name="save_for_later_name"]').val();
		dataForExport.filters = vgseGetFiltersJson();
		var scheduleDate = $exportCsv.find('.schedule-for-later-container [name="schedule_for_later_date"]').val();
		var isRecurring = $exportCsv.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'yes';
		var repeatEvery = isRecurring ? parseInt($exportCsv.find('.schedule-for-later-container [name="schedule_repeat_every"]').val()) : 0;
		var repeatUnit = $exportCsv.find('.schedule-for-later-container [name="schedule_repeat_unit"]').val();
		jQuery.ajax({
			url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/export-rows',
			dataType: 'json',
			type: 'POST',
			data: {
				sheet_key: vgse_editor_settings.post_type,
				date: scheduleDate,
				repeat_every: repeatEvery,
				repeat_unit: repeatUnit,
				email_notification_address: $exportCsv.find('.schedule-for-later-container [name="schedule_for_later_email_address"]').val(),
				name: $exportCsv.find('[name="save_for_later_name"]').val(),
				verbose_log: vgse_editor_settings.automations_verbose_logs,
				wpse_job_id: jobId,
				task_args: dataForExport,
				_wpnonce: vgse_editor_settings.rest_nonce
			},
			success: function (response) {
				nanobar.go(100);

				if (response.success) {
					$response.empty();
					$response.append( response.message || vgse_editor_settings.texts.export_scheduled_in_popup);
					$exportCsv.find('#be-export-nanobar-container').remove();
					$exportCsv.find('.export-actions').hide();
					$exportCsv.find('.remodal-cancel').show();
					$exportCsv.find('.remodal-confirm').show();
					$exportCsv.find('.export-columns').prop('disabled', false).trigger('change');
				}

				// If we edited a job, go to the scheduled jobs table
				if ($exportCsv.data('jobId')) {
					vgseGoToModal('vgse_scheduled_exports_modal');
					notification({ mensaje: vgse_editor_settings.texts.scheduled_export_edited_successfully, tipo: 'success', tiempo: 4000 });
				}

				// We make sure that we are using the same filters as before the bulk edit
				// so we can reload and show the same rows. Necessary specially when we 
				// edit "manually selected" rows. We don't want to reload and show different rows
				if (typeof originalFilters !== 'undefined') {
					beAddRowsFilter('post__in=');
				}
			},
			error: function (jqXHR, exception) {
				$exportCsv.find('#be-export-nanobar-container').remove();
				$exportCsv.find('.export-actions').hide();
				$exportCsv.find('.remodal-cancel').show();
				$exportCsv.find('.remodal-confirm').show();
				$exportCsv.find('.export-columns').prop('disabled', false).trigger('change');
			}
		});
		return false;
	});
});
