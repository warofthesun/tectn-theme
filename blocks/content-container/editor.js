(function (wp) {
	if (!wp || !wp.data || !wp.blocks || !wp.domReady) {
		return;
	}

	var select = wp.data.select;
	var dispatch = wp.data.dispatch;
	var createBlock = wp.blocks.createBlock;
	var BLOCK_NAME = 'tectn/content-container';
	var SPONSOR_SCROLL = 'tectn/sponsor-scroll';
	var TEXT_IMAGE = 'tectn/text-image';

	function fieldIsOn(block) {
		if (!block || !block.attributes) {
			return false;
		}
		var data = block.attributes.data || {};
		var raw = data.show_sponsor_scroll;
		if (raw === undefined || raw === null || raw === '') {
			raw = data.field_699ba_show_sponsor_scroll;
		}
		return !(
			raw === 0 ||
			raw === '0' ||
			raw === false ||
			raw === 'false' ||
			raw === null ||
			raw === undefined ||
			raw === ''
		);
	}

	function ensureInnerSponsorScroll(clientId) {
		var block = select('core/block-editor').getBlock(clientId);
		if (!block || block.name !== BLOCK_NAME) {
			return;
		}

		var on = fieldIsOn(block);
		if (block.attributes.showSponsorScroll !== on) {
			dispatch('core/block-editor').updateBlockAttributes(clientId, {
				showSponsorScroll: on,
			});
		}

		if (!on) {
			return;
		}

		var inner = block.innerBlocks || [];
		var hasSponsor = inner.some(function (b) {
			return b.name === SPONSOR_SCROLL;
		});
		if (hasSponsor) {
			return;
		}

		var sponsor = createBlock(SPONSOR_SCROLL);
		var next;
		if (inner.length >= 2) {
			next = [inner[0], sponsor].concat(inner.slice(1));
		} else if (inner.length === 1) {
			next = [inner[0], sponsor, createBlock(TEXT_IMAGE)];
		} else {
			next = [createBlock(TEXT_IMAGE), sponsor, createBlock(TEXT_IMAGE)];
		}

		dispatch('core/block-editor').replaceInnerBlocks(clientId, next, false);
	}

	function syncAllCombos() {
		function walk(list) {
			(list || []).forEach(function (block) {
				if (block.name === BLOCK_NAME) {
					ensureInnerSponsorScroll(block.clientId);
				}
				if (block.innerBlocks && block.innerBlocks.length) {
					walk(block.innerBlocks);
				}
			});
		}
		walk(select('core/block-editor').getBlocks());
	}

	var scheduled = false;
	function scheduleSync() {
		if (scheduled) {
			return;
		}
		scheduled = true;
		window.setTimeout(function () {
			scheduled = false;
			syncAllCombos();
		}, 50);
	}

	wp.domReady(function () {
		syncAllCombos();
		wp.data.subscribe(scheduleSync);

		if (window.acf && typeof window.acf.addAction === 'function') {
			window.acf.addAction('change', scheduleSync);
			window.acf.addAction('append', scheduleSync);
		}
	});
})(window.wp);
