// Bulk edits

jQuery(document).ready(function () {
	var $formulasForm = jQuery('.vgse-modal-form.be-formulas');
	if (!$formulasForm.length) {
		return true;
	}
	jQuery('body').on('click', '.vgse-scheduled-actions-popup .advanced-edit-bulk-edit', function (e) {
		e.preventDefault;
		var $modal = jQuery('.modal-formula');
		var taskArgs = jQuery('.edit-job-form.bulk-edit').prev().data('extra').task_args;
		var scheduleData = jQuery('.edit-job-form.bulk-edit').prev().data('schedule-data');
		var $editRow = jQuery(this).parents('table').find('.edit-job-form');

		$modal.remodal().open();
		$modal.find('.multiple-column-selector select[multiple]').val([taskArgs.column]).trigger('change');
		$modal.find('.action_name').val(taskArgs.action_name).trigger('change');
		taskArgs.formula_data.forEach(function (value, index) {
			$modal.find('.action-fields').find('input,select,textarea').eq(index).val(value).trigger('change');
		});

		$modal.find('[name="use_slower_execution"]').prop('checked', taskArgs.use_slower_execution);
		$modal.find('form').data('jobId', $editRow.data('job-id'));
		$modal.find('form').data('previousTaskArgs', taskArgs);

		var taskFilters = typeof taskArgs.filters === 'string' ? JSON.parse(taskArgs.filters) : taskArgs.filters;
		if( jQuery.param(taskFilters) !== beGetRowsFilters()){
			$modal.find('form').data('originalFilters', beGetRowsFilters());
			jQuery('body').data('be-filters', taskFilters);
			vgseReloadSpreadsheet();
		}
		$modal.find('.wpse-select-rows-options').val('current_search');

		vgseFillInScheduleFields($modal, scheduleData);

		// Close the edit row because we don't want it to stay open when we return
		jQuery(this).parents('tr').find('.cancel-edit').trigger('click');
	});
	var $formulasModal = jQuery('.modal-formula');
	var $executeFormula = jQuery('.vgse-execute-formula');
	var $formulaField = $formulasForm.find('.formula-textarea');

	jQuery(document).on('closed', '.modal-formula', function () {
		var $modal = jQuery(this);
		$modal.find('.backup-alert').show();
		$executeFormula.hide();
		$formulasForm.find('.remodal-cancel').show();
		$formulasForm.find('.remodal-confirm').show();
	});
	$formulasForm.on('submit', function (e) {
		// Exit if this is not a scheduled job
		if (!$formulasForm.find('[name="schedule_for_later"]').prop('checked')) {
			return true;
		}
		e.preventDefault();
		if (!$formulasForm.find('.wpse-select-rows-options').val()) {
			return false;
		}
		$formulasModal.find('.backup-alert').hide();
		var $response = jQuery('.be-response');
		var foundRowsToEdit = vgseGetSelectedRowsCount($formulasForm);
		if ($formulasForm.find('.wpse-select-rows-options').val() === 'selected') {
			var selectedIds = vgseGetSelectedIds();
		}
		if ($formulasForm.find('.wpse-select-rows-options').val() === 'previously_selected') {
			var selectedIds = window.wpsePreviouslySelectedIds || [];
		}
		if (typeof selectedIds !== 'undefined') {
			foundRowsToEdit = selectedIds.length;

			if (foundRowsToEdit) {
				var originalFilters = beGetRowsFilters();
				beAddRowsFilter('post__in=' + selectedIds.join(','));
			}
		}

		// Disabled because they need to be able to schedule bulk edits with filters that have zero rows now but might have rows in the future
		// if (!vgseGetSelectedRowsCount($formulasForm)) {
		// 	alert(vgse_editor_settings.texts.no_rows_for_formula);
		// 	return false;
		// }

		var modifiedData = beGetModifiedItems();
		if (modifiedData.length) {
			var canReload = confirm(vgse_editor_settings.texts.save_changes_before_we_reload);
			if (!canReload) {
				return false;
			}
		}

		if (typeof vgseGetFiltersJson === 'function') {
			$formulasForm.find('input[name="filters_found_rows"]').val(foundRowsToEdit);
		}

		if (!$formulaField.val()) {
			alert(vgse_formulas_data.texts.formula_required);
			return false;
		}

		$executeFormula.show();
		$executeFormula.find('.speed-tip, .edit-running, .formula-run-controls').hide();
		$executeFormula.find('#be-formulas-nanobar-container').remove();

		var formula = $formulaField.val();
		var post_type = $formulasForm.find('[name="post_type"]').val();

		var $columns = $formulasForm.find('.multiple-column-selector > select option:selected');
		var columns = [];
		$columns.each(function () {
			if (jQuery(this).val()) {
				columns.push({
					label: jQuery(this).text(),
					key: jQuery(this).val()
				});
			}
		});

		$response.addClass('single-edit');

		$executeFormula.find('.remodal-cancel').hide();
		var perPage = vgse_editor_settings.save_posts_per_page;
		var formulaAction = $formulasForm.find('.action_name').val();
		var actionFirstValue = $formulasForm.find('.action-fields').find('select,input,textarea').first().val();
		if (!vgse_editor_settings.sheet_uses_real_time_sync && vgse_editor_settings.is_post_type && formulaAction === 'set_value' && actionFirstValue === 'delete') {
			perPage = vgse_editor_settings.delete_posts_per_page;
		}

		// When the type of edit = delete, we don't adjust the total calls
		// we make the same number of calls estimated at the beginning
		// If we adjust it, it makes it finish in the middle by mistake because 
		// the server-side total is reducing on every call
		//var useFixedTotalCalls = $formulasForm.find('.formula-parameter-field').val() !== 'delete';
		var bulkEditId = $formulasForm.data('jobId') || vgseGuidGenerator();


		$formulasForm.find('.remodal-cancel').hide();
		$formulasForm.find('.remodal-confirm').hide();
		$response.empty();
		var formData = formToObject('vgse-create-formula');
		var scheduleDate = $formulasForm.find('.schedule-for-later-container [name="schedule_for_later_date"]').val();
		var isRecurring = $formulasForm.find('.schedule-for-later-container [name="schedule_is_recurring"]:checked').val() === 'yes';
		var repeatEvery = isRecurring ? parseInt($formulasForm.find('.schedule-for-later-container [name="schedule_repeat_every"]').val()) : 0;
		var repeatUnit = $formulasForm.find('.schedule-for-later-container [name="schedule_repeat_unit"]').val();
		var ajaxCallFinished = 0;
		var lastAjaxResponse = { success: false };
		columns.forEach(function (column) {
			var dataForBulkEdit = {
				column: column.key,
				formula: formula,
				formula_data: formData.formula_data,
				sheet_key: post_type,
				page: 1,
				wpse_job_id: bulkEditId,
				action_name: formData.action_name,
				filters: vgseGetFiltersJson(),
				use_slower_execution: $formulasForm.find('[name="use_slower_execution"]').prop('checked'),
				wpse_source_suffix: vgse_editor_settings.wpse_source_suffix || '',
				per_page: vgse_editor_settings.automations_bulk_edit_batch_size
			};

			jQuery.ajax({
				url: vgse_editor_settings.rest_base_url + 'sheet-editor/v1/sheet/schedule/bulk-edit',
				dataType: 'json',
				type: 'POST',
				data: {
					sheet_key: vgse_editor_settings.post_type,
					date: scheduleDate,
					repeat_every: repeatEvery,
					repeat_unit: repeatUnit,
					email_notification_address: $formulasForm.find('.schedule-for-later-container [name="schedule_for_later_email_address"]').val(),
					name: $formulasForm.find('.schedule-for-later-container [name="scheduled_job_name"]').val(),
					verbose_log: vgse_editor_settings.automations_verbose_logs,
					wpse_job_id: bulkEditId,
					task_args: dataForBulkEdit,
					_wpnonce: vgse_editor_settings.rest_nonce
				},
				success: function (response) {
					lastAjaxResponse = response;
					ajaxCallFinished++;
				}
			});
		});

		var intervalId = setInterval(function () {
			if (ajaxCallFinished === columns.length) {
				loading_ajax(false);
				if (lastAjaxResponse.success) {
					var $response = jQuery('.be-response');
					$response.empty();
					$response.append(lastAjaxResponse.message || vgse_editor_settings.texts.bulk_edit_scheduled_in_popup);
					$executeFormula.find('.remodal-cancel').show();
					// If we edited a job, go to the scheduled jobs table
					if( $formulasForm.data('jobId') ){
						vgseGoToModal('vgse_scheduled_bulk_edits_modal');
						notification({ mensaje: vgse_editor_settings.texts.scheduled_bulk_edit_edited_successfully, tipo: 'success', tiempo: 4000 });
					}
				}

				// We make sure that we are using the same filters as before the bulk edit
				// so we can reload and show the same rows. Necessary specially when we 
				// edit "manually selected" rows. We don't want to reload and show different rows
				if (typeof originalFilters !== 'undefined') {
					beAddRowsFilter('post__in=');
				}
				clearInterval(intervalId);
			}
		}, 500);

		return false;
	});
});