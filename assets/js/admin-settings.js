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
	});
})(window.jQuery);
