/**
 * Article/issue admin: searchable author picker + native wp.media PDF picker.
 * Gutenberg REST runs before the classic metabox POST (issue #30): journal
 * meta from classic fields must be copied into core/editor so it travels
 * in the same publish request.
 */
(function ($, wp) {
	'use strict';

	var ARTICLE_META_KEYS = [
		'title_en',
		'abstract',
		'abstract_en',
		'doi',
		'pages',
		'pdf_file',
		'language',
		'publication_date',
		'received_date',
		'accepted_date',
		'issue'
	];

	function selectedIds($box) {
		var ids = [];
		$box.find('.revistalogos-authors-assigned input[name="authors[]"]').each(function () {
			var id = parseInt($(this).val(), 10);
			if (id > 0 && ids.indexOf(id) === -1) {
				ids.push(id);
			}
		});
		return ids;
	}

	function updateAuthorsEmpty($box) {
		var assigned = selectedIds($box).length > 0;
		$box.find('.revistalogos-authors-empty').toggleClass('hidden', assigned);
	}

	function setResultsVisible($list, visible) {
		$list.toggleClass('hidden', !visible);
		if (visible) {
			$list.removeAttr('hidden');
		} else {
			$list.attr('hidden', 'hidden');
			$list.empty();
		}
	}

	function fieldValue(name) {
		var $input = $('#revistalogos-' + name);
		if (!$input.length) {
			$input = $('input[name="' + name + '"], textarea[name="' + name + '"], select[name="' + name + '"]').first();
		}
		if (!$input.length) {
			return null;
		}
		if ($input.is('select') || $input.is('textarea') || $input.is(':text') || $input.is(':hidden') || $input.attr('type') === 'date' || $input.attr('type') === 'number' || $input.attr('type') === 'search') {
			return $input.val();
		}
		return $input.val();
	}

	/**
	 * Collect classic metabox values Gutenberg must send as REST meta.
	 */
	function collectMetaboxMeta($authorsBox) {
		var meta = {
			authors: selectedIds($authorsBox && $authorsBox.length ? $authorsBox : $('#revistalogos-core-relationships'))
		};
		var i;
		var key;
		var raw;
		var pdf;

		for (i = 0; i < ARTICLE_META_KEYS.length; i++) {
			key = ARTICLE_META_KEYS[i];
			if ('pdf_file' === key) {
				pdf = $('.revistalogos-pdf-field__id').first().val();
				meta.pdf_file = pdf ? parseInt(pdf, 10) || 0 : 0;
				continue;
			}
			if ('issue' === key) {
				raw = $('#revistalogos-issue').val();
				meta.issue = raw ? parseInt(raw, 10) || 0 : 0;
				continue;
			}
			raw = fieldValue(key);
			if (null === raw) {
				continue;
			}
			meta[key] = raw;
		}

		return meta;
	}

	function metaEqual(left, right) {
		var key;
		if (!left || !right) {
			return false;
		}
		for (key in left) {
			if (!Object.prototype.hasOwnProperty.call(left, key)) {
				continue;
			}
			if (Array.isArray(left[key]) || Array.isArray(right[key])) {
				if (!authorIdsEqual(left[key] || [], right[key] || [])) {
					return false;
				}
				continue;
			}
			if (String(left[key]) !== String(right[key])) {
				return false;
			}
		}
		return true;
	}

	function storeMetaboxMeta() {
		if (typeof wp === 'undefined' || !wp.data || typeof wp.data.select !== 'function') {
			return null;
		}

		var select;
		try {
			select = wp.data.select('core/editor');
		} catch (err) {
			return null;
		}

		if (!select || typeof select.getEditedPostAttribute !== 'function') {
			return null;
		}

		var meta = select.getEditedPostAttribute('meta');
		return meta && typeof meta === 'object' ? meta : {};
	}

	function authorIdsEqual(left, right) {
		if (!left || !right || left.length !== right.length) {
			return false;
		}
		for (var i = 0; i < left.length; i++) {
			if (parseInt(left[i], 10) !== parseInt(right[i], 10)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Gutenberg publishes via REST before the classic metabox POST.
	 * Copy journal metabox values into the edited post meta store.
	 */
	function syncMetaboxMetaToBlockEditor($authorsBox) {
		if (typeof wp === 'undefined' || !wp.data || typeof wp.data.dispatch !== 'function') {
			return;
		}

		var dispatch;
		try {
			dispatch = wp.data.dispatch('core/editor');
		} catch (err) {
			return;
		}

		if (!dispatch || typeof dispatch.editPost !== 'function') {
			return;
		}

		var next = collectMetaboxMeta($authorsBox);
		var stored = storeMetaboxMeta();
		if (stored && metaEqual(next, stored)) {
			return;
		}

		dispatch.editPost({
			meta: next
		});
	}

	function isBlockEditorSaveControl(target) {
		if (!target || typeof target.closest !== 'function') {
			return false;
		}

		return !!target.closest(
			'.editor-post-publish-button, .editor-post-publish-panel__toggle, .editor-post-save-draft, .editor-post-publish-panel__header-publish-button, .editor-post-publish-button__button'
		);
	}

	function addAuthor($box, id, title) {
		id = parseInt(id, 10);
		if (!id || selectedIds($box).indexOf(id) !== -1) {
			return;
		}

		var cfg = window.revistalogosAuthorPicker || {};
		var i18n = cfg.i18n || {};
		var removeLabel = (i18n.removeNamed || i18n.remove || '').replace('%s', title);
		var $item = $('<li/>').attr('data-author-id', String(id));
		$item.append($('<span/>').addClass('revistalogos-authors-assigned__name').text(title));
		$item.append($('<input/>', { type: 'hidden', name: 'authors[]', value: String(id) }));
		$item.append(
			$('<button/>', {
				type: 'button',
				'class': 'button-link revistalogos-authors-remove',
				text: i18n.remove || 'Quitar',
				'aria-label': removeLabel
			})
		);
		$box.find('.revistalogos-authors-assigned').append($item);
		updateAuthorsEmpty($box);
		syncMetaboxMetaToBlockEditor($box);
	}

	function bindAuthors() {
		var $box = $('#revistalogos-core-relationships');
		if (!$box.length || typeof wp === 'undefined' || !wp.apiFetch) {
			return;
		}

		var cfg = window.revistalogosAuthorPicker || {};
		var minLength = parseInt(cfg.minLength, 10) || 2;
		var perPage = parseInt(cfg.perPage, 10) || 15;
		var restPath = cfg.restPath || '/wp/v2/author';
		var i18n = cfg.i18n || {};
		var $search = $box.find('#revistalogos-author-search');
		var $results = $box.find('#revistalogos-author-results');
		var $status = $box.find('#revistalogos-author-status');
		var timer = null;
		var requestSeq = 0;

		updateAuthorsEmpty($box);

		$box.on('click', '.revistalogos-authors-remove', function (event) {
			event.preventDefault();
			$(this).closest('li').remove();
			updateAuthorsEmpty($box);
			syncMetaboxMetaToBlockEditor($box);
		});

		$box.on('change', '#revistalogos-issue', function () {
			syncMetaboxMetaToBlockEditor($box);
		});

		$('#revistalogos-core-fields').on(
			'change',
			'input, textarea, select',
			function () {
				syncMetaboxMetaToBlockEditor($box);
			}
		);

		syncMetaboxMetaToBlockEditor($box);

		document.addEventListener(
			'click',
			function (event) {
				if (isBlockEditorSaveControl(event.target)) {
					syncMetaboxMetaToBlockEditor($box);
				}
			},
			true
		);

		function runSearch(query) {
			var seq = ++requestSeq;
			var params = new URLSearchParams();
			params.set('search', query);
			params.set('per_page', String(perPage));
			params.set('status', 'publish');
			params.set('orderby', 'title');
			params.set('order', 'asc');
			params.set('_fields', 'id,title');
			var exclude = selectedIds($box);
			if (exclude.length) {
				params.set('exclude', exclude.join(','));
			}

			$status.text(i18n.searching || '');
			wp.apiFetch({ path: restPath + '?' + params.toString() }).then(function (items) {
				if (seq !== requestSeq) {
					return;
				}
				$results.empty();
				if (!items || !items.length) {
					setResultsVisible($results, false);
					$status.text(i18n.noResults || '');
					return;
				}
				items.forEach(function (item) {
					var id = parseInt(item.id, 10);
					var title = item.title && item.title.rendered ? $('<textarea/>').html(item.title.rendered).text() : String(id);
					if (!id || selectedIds($box).indexOf(id) !== -1) {
						return;
					}
					var $li = $('<li/>');
					var $btn = $('<button/>', { type: 'button' });
					$btn.text(title);
					$btn.on('click', function () {
						addAuthor($box, id, title);
						setResultsVisible($results, false);
						$status.text('');
						$search.val('').focus();
					});
					$li.append($btn);
					$results.append($li);
				});
				setResultsVisible($results, $results.children().length > 0);
				$status.text($results.children().length ? (i18n.results || '') : (i18n.noResults || ''));
			}).catch(function () {
				if (seq !== requestSeq) {
					return;
				}
				setResultsVisible($results, false);
				$status.text(i18n.noResults || '');
			});
		}

		$search.on('input', function () {
			var query = $.trim($search.val());
			window.clearTimeout(timer);
			if (query.length < minLength) {
				requestSeq += 1;
				setResultsVisible($results, false);
				$status.text('');
				return;
			}
			timer = window.setTimeout(function () {
				runSearch(query);
			}, 250);
		});

		$search.on('keydown', function (event) {
			if (event.key === 'Escape') {
				setResultsVisible($results, false);
				$status.text('');
			}
		});
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
		syncMetaboxMetaToBlockEditor($('#revistalogos-core-relationships'));
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
})(jQuery, window.wp);
