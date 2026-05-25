/*
 * Admin settings — initialise the WordPress colour picker on the
 * Brand color field.
 */
(function ($) {
	'use strict';
	$(function () {
		if ($.fn && $.fn.wpColorPicker) {
			$('.icaia-color-field').wpColorPicker();
		}
	});
})(window.jQuery);
