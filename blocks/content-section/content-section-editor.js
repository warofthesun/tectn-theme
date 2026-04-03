/**
 * Content Section: hide server-rendered empty hint when inner blocks exist (JS is source of truth in editor).
 */
(function () {
	if (typeof window.wp === "undefined" || !window.wp.data || !window.wp.domReady) {
		return;
	}
	var select = window.wp.data.select;
	var subscribe = window.wp.data.subscribe;
	var domReady = window.wp.domReady;
	var BLOCK_NAME = "tectn/content-section";

	function getCanvasDocument() {
		var iframes = document.querySelectorAll("iframe");
		var i, doc;
		for (i = 0; i < iframes.length; i++) {
			try {
				doc = iframes[i].contentDocument;
				if (
					doc &&
					doc.querySelector(".block-editor-block-list__layout")
				) {
					return doc;
				}
			} catch (e) {}
		}
		return document;
	}

	function walkBlocks(blocks, acc) {
		if (!blocks || !blocks.length) {
			return;
		}
		blocks.forEach(function (b) {
			if (b.name === BLOCK_NAME) {
				acc.push({
					clientId: b.clientId,
					innerLen: b.innerBlocks ? b.innerBlocks.length : 0,
				});
			}
			if (b.innerBlocks && b.innerBlocks.length) {
				walkBlocks(b.innerBlocks, acc);
			}
		});
	}

	function syncPlaceholders() {
		var ed = select("core/block-editor");
		if (!ed || typeof ed.getBlocks !== "function") {
			return;
		}
		var doc = getCanvasDocument();
		var list = [];
		walkBlocks(ed.getBlocks(), list);
		list.forEach(function (entry) {
			var root = doc.querySelector('[data-block="' + entry.clientId + '"]');
			if (!root) {
				return;
			}
			var hint = root.querySelector(".c-content-section__placeholder");
			if (!hint) {
				return;
			}
			hint.hidden = entry.innerLen > 0;
		});
	}

	var scheduled = null;
	domReady(function () {
		subscribe(function () {
			if (scheduled !== null) {
				return;
			}
			scheduled = window.requestAnimationFrame(function () {
				scheduled = null;
				syncPlaceholders();
			});
		});
		syncPlaceholders();
		window.setTimeout(syncPlaceholders, 100);
		window.setTimeout(syncPlaceholders, 400);
	});
})();
