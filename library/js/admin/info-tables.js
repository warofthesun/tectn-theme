(function ($) {
	'use strict';

	if (typeof acf === 'undefined') {
		return;
	}

	var REPEATER_KEY = 'field_tectn_info_tables_embedded_list';
	var ADMIN_LABEL_KEY = 'field_tectn_info_table_admin_label';
	var TABLE_KEY_FIELD = 'field_tectn_info_table_row_key';
	var COPY_SUFFIX = ' (copy)';

	function getTablesRepeater() {
		return acf.getField(REPEATER_KEY);
	}

	function isTopLevelTableRow($row) {
		return $row.find('[data-key="' + ADMIN_LABEL_KEY + '"]').length > 0;
	}

	function getFieldInRow($row, fieldKey) {
		var $field = $row.find('[data-key="' + fieldKey + '"]').first();
		if (!$field.length) {
			return null;
		}
		return acf.getField($field);
	}

	function appendCopyLabel($row) {
		var field = getFieldInRow($row, ADMIN_LABEL_KEY);
		if (!field) {
			return;
		}
		var value = typeof field.val() === 'string' ? field.val().trim() : '';
		if (value === '') {
			field.val('(copy)');
			return;
		}
		if (!/\(copy(?:\s*\d+)?\)$/i.test(value)) {
			field.val(value + COPY_SUFFIX);
		}
	}

	function clearTableKey($row) {
		var field = getFieldInRow($row, TABLE_KEY_FIELD);
		if (field) {
			field.val('');
		}
	}

	function addDuplicateButton($row) {
		if (!isTopLevelTableRow($row) || $row.hasClass('acf-clone')) {
			return;
		}
		if ($row.find('.tectn-info-table-duplicate').length) {
			return;
		}

		var $labelField = $row.find('[data-key="' + ADMIN_LABEL_KEY + '"]').first();
		var $input = $labelField.find('> .acf-input').first();
		if (!$input.length) {
			return;
		}

		var $button = $('<button type="button" class="button button-small tectn-info-table-duplicate">Duplicate</button>');
		$input.addClass('tectn-info-table-label-row').append($button);

		$button.on('click', function (event) {
			event.preventDefault();
			duplicateTableRow($row);
		});
	}

	function duplicateTableRow($sourceRow) {
		var $duplicateControl = $sourceRow.find('> .acf-row-handle.remove a[data-event="duplicate-row"]').first();
		if (!$duplicateControl.length) {
			return;
		}

		$duplicateControl.trigger('click');

		window.setTimeout(function () {
			var $newRow = $sourceRow.next('tr.acf-row');
			if (!$newRow.length || !isTopLevelTableRow($newRow)) {
				return;
			}
			clearTableKey($newRow);
			appendCopyLabel($newRow);
			addDuplicateButton($newRow);
		}, 50);
	}

	function initDuplicateButtons($repeaterEl) {
		var $repeater = $repeaterEl && $repeaterEl.length
			? $repeaterEl
			: $('[data-key="' + REPEATER_KEY + '"]');

		$repeater.find('.acf-repeater tbody > tr.acf-row').each(function () {
			addDuplicateButton($(this));
		});
	}

	acf.addAction('ready_field/key=' + REPEATER_KEY, function (field) {
		initDuplicateButtons(field.$el);
	});

	acf.addAction('ready', function () {
		initDuplicateButtons();
	});

	acf.addAction('append', function ($el) {
		if ($el.is('tr.acf-row') && isTopLevelTableRow($el)) {
			addDuplicateButton($el);
		}
	});
})(jQuery);
