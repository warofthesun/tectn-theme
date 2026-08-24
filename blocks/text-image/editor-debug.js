/**
 * Debug: Text + Image positioning CSS in the block editor canvas.
 * Session 7ac3e3 — remove after investigation.
 */
(function () {
	// #region agent log
	function post(hypothesisId, message, data) {
		fetch('http://127.0.0.1:7272/ingest/081cba34-db3c-4310-ace5-70e9f0d86181', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-Debug-Session-Id': '7ac3e3',
			},
			body: JSON.stringify({
				sessionId: '7ac3e3',
				runId: 'post-fix',
				hypothesisId: hypothesisId,
				location: 'blocks/text-image/editor-debug.js',
				message: message,
				data: data,
				timestamp: Date.now(),
			}),
		}).catch(function () {});
	}

	function inspect() {
		var doc = document;
		var win = window;
		try {
			var canvas = document.querySelector('iframe[name="editor-canvas"], iframe.editor-canvas__iframe');
			if (canvas && canvas.contentDocument) {
				doc = canvas.contentDocument;
				win = canvas.contentWindow || win;
			}
		} catch (e) {
			post('B-C', 'canvas iframe inaccessible', { error: String(e) });
		}

		var groups = doc.querySelectorAll('.c-content-group');
		var sample = groups[0] || null;
		var cs = sample ? win.getComputedStyle(sample) : null;
		var content = sample ? sample.querySelector('.c-content-group__content') : null;
		var csContent = content ? win.getComputedStyle(content) : null;

		var hrefs = [];
		var sheets = doc.styleSheets || [];
		for (var i = 0; i < sheets.length && i < 40; i++) {
			try {
				if (sheets[i].href) {
					hrefs.push(sheets[i].href);
				}
			} catch (e2) {}
		}

		var hasStyleCss = hrefs.some(function (h) {
			return /library\/css\/style\.css/i.test(h);
		});
		var mq = win.matchMedia ? win.matchMedia('(min-width: 64em)') : null;

		var col = sample ? sample.querySelector('[class*="col-md-"]') : null;
		var csCol = col ? win.getComputedStyle(col) : null;

		post('B-C-E', 'editor canvas positioning CSS', {
			groupCount: groups.length,
			sampleClass: sample ? sample.className : null,
			hasReverse: sample ? sample.classList.contains('c-content-group--reverse') : null,
			hasMiddle: sample ? sample.classList.contains('c-content-group--middle') : null,
			flexDirection: cs ? cs.flexDirection : null,
			display: cs ? cs.display : null,
			contentMarginTop: csContent ? csContent.marginTop : null,
			colMaxWidth: csCol ? csCol.maxWidth : null,
			colFlexBasis: csCol ? csCol.flexBasis : null,
			innerWidth: win.innerWidth,
			matches64em: mq ? mq.matches : null,
			hasStyleCssHref: hasStyleCss,
			styleHrefSample: hrefs.filter(function (h) {
				return /style\.css|editor-style/i.test(h);
			}).slice(0, 8),
		});
	}

	function run() {
		inspect();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}

	if (window.wp && wp.domReady) {
		wp.domReady(run);
	}

	if (window.wp && wp.data && wp.data.subscribe) {
		var done = false;
		wp.data.subscribe(function () {
			if (done) {
				return;
			}
			var canvas = document.querySelector('iframe[name="editor-canvas"], iframe.editor-canvas__iframe');
			var doc = canvas && canvas.contentDocument ? canvas.contentDocument : document;
			if (doc.querySelector('.c-content-group')) {
				done = true;
				inspect();
			}
		});
	}
	// #endregion
})();
