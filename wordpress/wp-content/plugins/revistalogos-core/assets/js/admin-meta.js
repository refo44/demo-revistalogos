/**
 * Article/issue admin: author empty-state + native wp.media PDF picker.
 */
(function ($) {
	'use strict';

	function updateAuthorsEmpty($box) {
		var assigned = $box.find('input[name="authors[]"]:checked').length > 0;
		$box.find('.revistalogos-authors-empty').toggleClass('hidden', assigned);
	}

	function bindAuthors() {
		var $box = $('#revistalogos-core-relationships');
		if (!$box.length) {
			return;
		}
		$box.on('change', 'input[name="authors[]"]', function () {
			updateAuthorsEmpty($box);
		});
		updateAuthorsEmpty($box);
	}

	function setPdfState($field, id, url, filename) {
		var has = parseInt(id, 10) > 0 && !!url;
		var i18n = window.revistalogosPdfMedia || {};
		$field.find('.revistalogos-pdf-field__id').val(has ? id : 0);
		$field.find('.revistalogos-pdf-field__filename').text(filename || '').toggleClass('hidden', !has);
		$field.find('.revistalogos-pdf-field__empty').toggleClass('hidden', has);
		$field.find('.revistalogos-pdf-field__view').attr('href', has ? url : '#').toggleClass('hidden', !has);
		$field.find('.revistalogos-pdf-field__remove').toggleClass('hidden', !has);
		$field.find('.revistalogos-pdf-field__select').text(has ? (i18n.replace || '') : (i18n.select || ''));
	}

	function openPdfFrame($field) {
		var i18n = window.revistalogosPdfMedia || {};
		var frame = wp.media({
			title: i18n.title || '',
			button: { text: i18n.button || '' },
			multiple: false,
			library: { type: 'application/pdf' }
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first();
			if (!attachment) {
				return;
			}
			var data = attachment.toJSON();
			if (data.mime && data.mime !== 'application/pdf') {
				return;
			}
			setPdfState($field, data.id, data.url, data.filename || data.title || '');
		});

		frame.open();
	}

	function bindPdf() {
		$(document).on('click', '.revistalogos-pdf-field__select', function (event) {
			event.preventDefault();
			openPdfFrame($(this).closest('.revistalogos-pdf-field'));
		});

		$(document).on('click', '.revistalogos-pdf-field__remove', function (event) {
			event.preventDefault();
			setPdfState($(this).closest('.revistalogos-pdf-field'), 0, '', '');
		});
	}

	$(function () {
		bindAuthors();
		bindPdf();
	});
})(jQuery);
