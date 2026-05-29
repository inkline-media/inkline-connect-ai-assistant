/*
 * Admin settings — initialise the WP color picker on the brand-colour
 * field, toggle the Google Font / Custom font-family rows based on the
 * selected font-source radio, and wire per-field "Restore default"
 * buttons that read from window.ICAIA_ADMIN.defaults.
 *
 * Saving is a separate step — restore buttons only stage a change so
 * the admin still has to click Save Changes for it to persist.
 */
(function ($) {
	'use strict';
	$(function () {
		if ($.fn && $.fn.wpColorPicker) {
			$('.icaia-color-field').wpColorPicker();
		}

		var $modes = $('.icaia-font-mode');
		var $rows  = $('.icaia-font-row');

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

		// Restore Inkline default for the font block: switch back to
		// Google mode, set the family to Inter, and enable the loader.
		$('#icaia-font-reset').on('click', function () {
			$('.icaia-font-mode[value="google"]').prop('checked', true).trigger('change');
			$('#icaia-font-google-family').val('Inter');
			$('input[name$="[font_google_load]"]').prop('checked', true);
		});

		/* --------------------------------------------------------- */
		/*  Per-field Restore default — wires .icaia-restore buttons. */
		/* --------------------------------------------------------- */

		var DEFAULTS = (window.ICAIA_ADMIN && window.ICAIA_ADMIN.defaults) || {};

		function setColorField($input, value) {
			// wpColorPicker wraps the raw input. Clearing requires the
			// .clear button (or val('') + change); setting a value can go
			// through the public .wpColorPicker('color', value) API.
			if (value === '' || value == null) {
				if ($input.data('wp-color-picker-clear')) {
					$input.data('wp-color-picker-clear').click();
					return;
				}
				var $wrap = $input.closest('.wp-picker-container');
				var $clear = $wrap.find('.wp-picker-clear');
				if ($clear.length) {
					$clear.trigger('click');
					return;
				}
				$input.val('').trigger('change');
				return;
			}
			if ($input.hasClass('wp-color-picker') || $input.closest('.wp-picker-container').length) {
				try {
					$input.wpColorPicker('color', value);
					return;
				} catch (e) { /* fall through to plain val */ }
			}
			$input.val(value).trigger('change');
		}

		function setCheckboxField($input, value) {
			$input.prop('checked', Number(value) === 1).trigger('change');
		}

		function setTextField($input, value) {
			$input.val(value == null ? '' : value).trigger('change');
		}

		$(document).on('click', '.icaia-restore', function (e) {
			e.preventDefault();
			var key = $(this).data('key');
			var target = $(this).data('target');
			var $field = $(target);
			if (!$field.length || !(key in DEFAULTS)) return;
			var def = DEFAULTS[key];

			if ($field.is(':checkbox')) {
				setCheckboxField($field, def);
				return;
			}
			if ($field.hasClass('icaia-color-field')) {
				setColorField($field, def);
				return;
			}
			setTextField($field, def);
		});
	});
})(window.jQuery);
