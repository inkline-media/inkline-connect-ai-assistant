/*
 * Admin settings — initialise the WP color picker on the brand-colour
 * field, and toggle the Google Font / Custom font-family rows based on
 * the selected font-source radio.
 */
(function ($) {
	'use strict';
	$(function () {
		if ($.fn && $.fn.wpColorPicker) {
			$('.icaia-color-field').wpColorPicker();
		}

		var $modes  = $('.icaia-font-mode');
		var $rows   = $('.icaia-font-row');

		function applyMode(mode) {
			$rows.each(function () {
				var $row = $(this);
				var isGoogle = $row.hasClass('icaia-font-row--google');
				var isCustom = $row.hasClass('icaia-font-row--custom');
				if (mode === 'google' && isGoogle) $row.show();
				else if (mode === 'custom' && isCustom) $row.show();
				else $row.hide();
			});
		}

		if ($modes.length) {
			var initial = $modes.filter(':checked').data('target') || 'google';
			applyMode(initial);
			$modes.on('change', function () {
				applyMode($(this).data('target'));
			});
		}

		// Restore Inkline default: switch back to Google mode, set the
		// family to Inter, and enable the Google Fonts loader. The
		// admin still has to Save Changes for it to persist.
		$('#icaia-font-reset').on('click', function () {
			$('.icaia-font-mode[value="google"]').prop('checked', true).trigger('change');
			$('#icaia-font-google-family').val('Inter');
			$('input[name$="[font_google_load]"]').prop('checked', true);
		});
	});
})(window.jQuery);
