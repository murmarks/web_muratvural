// Attach images modal
jQuery(document).ready(function () {
	var $modal = jQuery('.remodal-modal-attach-images');
	var $button = $modal.find('.start-attach-image');
	var nonce = jQuery('.remodal-bg').data('nonce');
	$button.on('click', function (e) {
		e.preventDefault();
		loading_ajax({ estado: false });

		var $progress = $modal.find('.response');
		$progress.empty();
		jQuery('#be-attach-images-nanobar-container').remove();

		// Init progress bar
		$progress.before('<div id="be-attach-images-nanobar-container" />');
		var options = {
			classname: 'be-progress-bar',
			target: document.getElementById('be-attach-images-nanobar-container')
		};

		var nanobar = new Nanobar(options);
		// We start progress bar with 1% so it doesn't look completely empty
		nanobar.go(1);

		// Start saving posts, start ajax loop
		beAjaxLoop({
			totalCalls: 9999999,
			url: vgse_global_data.ajax_url,
			method: 'POST',
			data: {
				action: 'vgse_attach_images',
				nonce: nonce,
				step: 1
			},
			onSuccess: function (res, settings) {
				loading_ajax({ estado: false });

				// if the response is empty or has any other format,
				// we create our custom false response
				if (res.success !== true && !res.data) {
					res = {
						data: {
							message: vgse_editor_settings.texts.http_error_try_now
						},
						success: false
					};
				}

				// If error
				if (!res.success) {
					// show error message
					jQuery($progress).append('<p>' + res.data.message + '</p>');

					// Ask the user if he wants to retry the same post
					var goNext = false;

					// stop saving if the user chose to not try again
					if (!goNext) {
						jQuery($progress).append(vgse_editor_settings.texts.saving_stop_error);
						$progress.scrollTop($progress[0].scrollHeight);
						return false;
					}
				}

				if (!jQuery($progress).find('p:contains(' + res.data.message + ')').length) {
					jQuery($progress).append('<p>' + res.data.message + '</p>');
				}

				if (res.data.step1_completed) {
					nanobar.go(33);
					settings.data.step = 2;
				}
				if (res.data.step2_completed) {
					nanobar.go(66);
					settings.data.step = 3;
				}
				if (res.data.step3_completed) {
					nanobar.go(100);
					jQuery('#be-attach-images-nanobar-container').remove();
					jQuery($progress).append('<p>' + vgse_editor_settings.texts.everything_saved + '</p>');
					loading_ajax({ estado: false });
					notification({ mensaje: vgse_editor_settings.texts.everything_saved });
					$progress.find('.remodal-cancel').removeClass('hidden');
					vgseReloadSpreadsheet(true);
					return false;
				}

				// Move scroll to the button to show always the last message in the saving status section
				setTimeout(function () {
					$progress.scrollTop($progress[0].scrollHeight);
				}, 600);

				return true;
			},
			onError: function (jqXHR, textStatus, settings) {
				console.log('error cb');
				jQuery($progress).append(vgse_editor_settings.texts.process_execution_failed);
				jQuery($progress).scrollTop(jQuery($progress)[0].scrollHeight);
				nanobar.go(100);
				jQuery('#be-attach-images-nanobar-container').remove();
				$progress.find('.remodal-cancel').removeClass('hidden');
				return false;
			}
		});
	});
});

// Import modal - step 3 - Automatically select to update only, never create new items
jQuery(document).ready(function () {
	var $importForm = jQuery('.import-csv-form');
	if (!$importForm.length || vgse_editor_settings.post_type !== 'attachment') {
		return true;
	}

	$importForm.find('select[name="writing_type"]').hide().val('only_update').trigger('change');
	$importForm.find('.write-type h3').hide();
	$importForm.find('.field-find-existing-columns').show();
});