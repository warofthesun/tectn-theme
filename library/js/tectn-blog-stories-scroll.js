/**
 * Blog index: after pagination navigation, align #tectn-stories-header 50px below the viewport top.
 */
(function () {
	'use strict';

	var OFFSET_PX = 50;
	var ANCHOR_ID = 'tectn-stories-header';

	function isPaginatedBlogView() {
		if (/\bpaged=\d+/.test(window.location.search)) {
			return true;
		}
		if (/\/page\/\d+\/?/i.test(window.location.pathname)) {
			return true;
		}
		if (/\bpaged-\d+\b/.test(document.body.className)) {
			return true;
		}
		return false;
	}

	function scrollStoriesIntoPlace() {
		if (!isPaginatedBlogView()) {
			return;
		}
		var el = document.getElementById(ANCHOR_ID);
		if (!el) {
			return;
		}
		var y = el.getBoundingClientRect().top + window.pageYOffset - OFFSET_PX;
		window.scrollTo({
			top: Math.max(0, y),
			behavior: 'auto',
		});
	}

	function runAfterLayout() {
		requestAnimationFrame(function () {
			requestAnimationFrame(scrollStoriesIntoPlace);
		});
	}

	function bind() {
		runAfterLayout();
		if (!isPaginatedBlogView()) {
			return;
		}
		if (document.readyState === 'complete') {
			scrollStoriesIntoPlace();
			return;
		}
		window.addEventListener(
			'load',
			function onLoad() {
				window.removeEventListener('load', onLoad);
				scrollStoriesIntoPlace();
			}
		);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}

	// Restore position when returning via bfcache.
	window.addEventListener('pageshow', function (ev) {
		if (ev.persisted) {
			runAfterLayout();
		}
	});
})();
