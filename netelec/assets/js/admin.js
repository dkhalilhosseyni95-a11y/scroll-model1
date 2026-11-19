/**
 * NETELEC settings panel admin script.
 */
(function ($) {
	'use strict';

	$(function () {
		// Color pickers.
		$('.nc-color').wpColorPicker();

		// Media picker.
		$(document).on('click', '.nc-media-select', function (event) {
			event.preventDefault();
			var wrapper = $(this).closest('.nc-media');
			var input = wrapper.find('input[type="hidden"]');
			var preview = wrapper.find('.nc-media-preview');

			var frame = wp.media({
				title: 'انتخاب تصویر',
				button: { text: 'استفاده از تصویر' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				input.val(attachment.id);
				var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
				preview.html('<img src="' + url + '" alt="">');
			});

			frame.open();
		});

		$(document).on('click', '.nc-media-remove', function (event) {
			event.preventDefault();
			var wrapper = $(this).closest('.nc-media');
			wrapper.find('input[type="hidden"]').val('');
			wrapper.find('.nc-media-preview').empty();
		});

		// Section order (drag & drop).
		var $sections = $('.nc-sections');
		if ($sections.length && $.fn.sortable) {
			$sections.sortable({
				handle: '.nc-drag',
				axis: 'y',
				update: function () {
					var order = $sections
						.find('.nc-section-row')
						.map(function () {
							return $(this).data('slug');
						})
						.get()
						.join(',');
					$('#' + $sections.data('input')).val(order);
				}
			});
		}

		// AJAX actions.
		function runAction(button, action, resultSelector, doneLabel) {
			var $button = $(button);
			var $result = $(resultSelector);

			$button.prop('disabled', true);
			$result.removeClass('is-error is-success').text('در حال انجام…');

			$.post(netelecAdmin.ajaxUrl, {
				action: action,
				nonce: netelecAdmin.nonce
			})
				.done(function (response) {
					if (response && response.success) {
						$result.addClass('is-success').text(response.data.message || doneLabel);
					} else {
						$result.addClass('is-error').text((response && response.data && response.data.message) || 'خطا');
					}
				})
				.fail(function () {
					$result.addClass('is-error').text('خطای شبکه');
				})
				.always(function () {
					$button.prop('disabled', false);
				});
		}

		$('#nc-sms-test').on('click', function () {
			runAction(this, 'netelec_sms_test', '#nc-sms-test-result', 'اتصال برقرار است.');
		});

		$('#nc-create-pages').on('click', function () {
			runAction(this, 'netelec_create_pages', '#nc-create-pages-result', 'صفحات ساخته شدند.');
		});

		$('#nc-seed-demo').on('click', function () {
			runAction(this, 'netelec_seed_demo', '#nc-seed-demo-result', 'داده نمونه ساخته شد.');
		});
	});
})(jQuery);
