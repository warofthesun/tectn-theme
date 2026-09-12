(function () {
	function setup(root) {
		var track = root.querySelector('.c-sponsor-scroll__track');
		var items = root.querySelector('.c-sponsor-scroll__items');
		if (!track || !items || items.classList.contains('c-sponsor-scroll__items--clone')) {
			return;
		}

		var rail = null;
		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

		function clearTrail() {
			var trail = items.querySelector('.c-sponsor-scroll__sep--trail');
			if (trail && trail.parentNode) {
				trail.parentNode.removeChild(trail);
			}
		}

		function teardown() {
			root.classList.remove('c-sponsor-scroll--animate');
			clearTrail();
			if (!rail) {
				return;
			}
			track.appendChild(items);
			if (rail.parentNode) {
				rail.parentNode.removeChild(rail);
			}
			rail = null;
		}

		function update() {
			teardown();

			if (reduceMotion.matches) {
				return;
			}

			var needsScroll = items.scrollWidth > track.clientWidth + 1;
			if (!needsScroll) {
				return;
			}

			var trail = document.createElement('span');
			trail.className = 'c-sponsor-scroll__sep c-sponsor-scroll__sep--trail';
			trail.setAttribute('aria-hidden', 'true');
			trail.textContent = '\u2022';
			items.appendChild(trail);

			rail = document.createElement('div');
			rail.className = 'c-sponsor-scroll__rail';

			var clone = items.cloneNode(true);
			clone.setAttribute('aria-hidden', 'true');
			clone.classList.add('c-sponsor-scroll__items--clone');

			track.appendChild(rail);
			rail.appendChild(items);
			rail.appendChild(clone);

			// Exact distance to the clone start avoids padding/margin seam gaps.
			var distance = clone.offsetLeft - items.offsetLeft;
			if (distance < 1) {
				distance = items.scrollWidth;
			}
			var duration = Math.max(12, distance / 40);
			rail.style.setProperty('--sponsor-scroll-distance', distance + 'px');
			rail.style.setProperty('--sponsor-scroll-duration', duration + 's');
			root.classList.add('c-sponsor-scroll--animate');
		}

		update();

		if (typeof ResizeObserver !== 'undefined') {
			var ro = new ResizeObserver(function () {
				update();
			});
			ro.observe(track);
			ro.observe(items);
		}

		if (typeof reduceMotion.addEventListener === 'function') {
			reduceMotion.addEventListener('change', update);
		} else if (typeof reduceMotion.addListener === 'function') {
			reduceMotion.addListener(update);
		}
	}

	function init() {
		document.querySelectorAll('[data-sponsor-scroll]').forEach(setup);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
