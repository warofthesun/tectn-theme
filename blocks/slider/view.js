(function () {
  function parseItems(el) {
    var raw = el.getAttribute('data-items');
    try {
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  }

  // --- Table of contents slider ---
  document.querySelectorAll('.c-slider--table_of_contents').forEach(function (root) {
    var items = parseItems(root);
    if (!items.length) return;

    var wrap = root.querySelector('.c-slider__image-wrap');
    var currentSlide = root.querySelector('.c-slider__slide--current');
    var nextSlide = root.querySelector('.c-slider__slide--next');
    var buttons = root.querySelectorAll('.c-slider__list-btn');
    if (!wrap || !currentSlide || !nextSlide || !buttons.length) return;

    var currentIndex = 0;
    var isTransitioning = false;

    function setWrapAspectFromImage(img) {
      if (!img || !wrap) return;
      function apply() {
        if (img.naturalWidth && img.naturalHeight) {
          wrap.style.setProperty('--slider-aspect-ratio', img.naturalWidth / img.naturalHeight);
        }
      }
      if (img.complete && img.naturalWidth) {
        apply();
      } else {
        img.addEventListener('load', apply);
      }
    }

    function showIndex(index) {
      var item = items[index];
      if (!item || index === currentIndex || isTransitioning) return;

      isTransitioning = true;
      wrap.classList.add('c-slider__image-wrap--transitioning');

      var nextImg = nextSlide.querySelector('[data-slider-image]');
      nextImg.src = item.url;
      nextImg.alt = item.title || '';

      nextSlide.classList.remove('c-slider__slide--up');
      nextSlide.classList.add('c-slider__slide--down');
      currentSlide.classList.add('c-slider__slide--up');

      var endCount = 0;
      var swapTimeout = setTimeout(function () {
        swapTimeout = null;
        if (isTransitioning) doSwap();
      }, 450);
      function doSwap() {
        if (!isTransitioning) return;
        wrap.removeEventListener('transitionend', onEnd);
        if (swapTimeout) {
          clearTimeout(swapTimeout);
          swapTimeout = null;
        }
        isTransitioning = false;
        wrap.classList.remove('c-slider__image-wrap--transitioning');
        currentSlide.classList.remove('c-slider__slide--up');
        nextSlide.classList.remove('c-slider__slide--down');
        var curImg = currentSlide.querySelector('[data-slider-image]');
        curImg.src = item.url;
        curImg.alt = item.title || '';
        currentSlide.classList.remove('c-slider__slide--current');
        currentSlide.classList.add('c-slider__slide--next');
        nextSlide.classList.remove('c-slider__slide--next');
        nextSlide.classList.add('c-slider__slide--current');
        var tmp = currentSlide;
        currentSlide = nextSlide;
        nextSlide = tmp;
        currentIndex = index;
        setWrapAspectFromImage(currentSlide.querySelector('[data-slider-image]'));
      }
      var onEnd = function (e) {
        if (e.propertyName !== 'transform') return;
        if (e.target !== currentSlide && e.target !== nextSlide) return;
        endCount += 1;
        if (endCount === 2) doSwap();
      };

      wrap.addEventListener('transitionend', onEnd);
      buttons.forEach(function (btn, i) {
        var isActive = i === index;
        btn.classList.toggle('c-slider__list-btn--active', isActive);
        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var index = parseInt(btn.getAttribute('data-index'), 10);
        if (!isNaN(index)) showIndex(index);
      });
    });

    setWrapAspectFromImage(currentSlide.querySelector('[data-slider-image]'));
  });

  // --- Horizontal slider ---
  document.querySelectorAll('.c-slider--horizontal').forEach(function (root) {
    var items = parseItems(root);
    if (!items.length) return;

    var wrap = root.querySelector('.c-slider__image-wrap');
    var currentSlide = root.querySelector('.c-slider__slide--current');
    var nextSlide = root.querySelector('.c-slider__slide--next');
    var captionEl = root.querySelector('[data-slider-caption]');
    var prevBtn = root.querySelector('[data-slider-prev]');
    var nextBtn = root.querySelector('[data-slider-next]');
    var dots = root.querySelectorAll('.c-slider__dot');
    var autoplay = root.getAttribute('data-autoplay') === '1';
    if (!wrap || !currentSlide || !nextSlide) return;

    var currentIndex = 0;
    var isTransitioning = false;
    var autoplayTimer = null;
    var AUTOPLAY_MS = 5000;

    function setCaption(index) {
      if (!captionEl) return;
      var item = items[index];
      if (!item) return;
      var html = '';
      if (item.caption && item.caption.trim() !== '') {
        html += '<p class="c-slider__caption-text">' + escapeHtml(item.caption) + '</p>';
      }
      if (item.author && item.author.trim() !== '') {
        html += '<p class="c-slider__caption-author">' + escapeHtml(item.author) + '</p>';
      }
      captionEl.innerHTML = html;
      var hasContent = (item.caption && item.caption.trim() !== '') || (item.author && item.author.trim() !== '');
      captionEl.classList.toggle('c-slider__caption--visible', !!hasContent);
    }

    function escapeHtml(s) {
      var div = document.createElement('div');
      div.textContent = s;
      return div.innerHTML;
    }

    function go(index) {
      var item = items[index];
      if (!item || index === currentIndex || isTransitioning) return;

      var direction = index > currentIndex ? 1 : -1;
      isTransitioning = true;
      wrap.classList.add('c-slider__image-wrap--transitioning');

      var nextImg = nextSlide.querySelector('[data-slider-image]');
      nextImg.src = item.url;
      nextImg.alt = item.title || '';

      currentSlide.classList.remove('c-slider__slide--out-left', 'c-slider__slide--out-right');
      nextSlide.classList.remove('c-slider__slide--in-from-left', 'c-slider__slide--in-from-right');
      currentSlide.classList.add(direction > 0 ? 'c-slider__slide--out-left' : 'c-slider__slide--out-right');
      nextSlide.classList.add(direction > 0 ? 'c-slider__slide--in-from-right' : 'c-slider__slide--in-from-left');

      var endCount = 0;
      var swapTimeout = setTimeout(function () {
        if (isTransitioning) finishSwap();
      }, 450);
      function finishSwap() {
        if (!isTransitioning) return;
        wrap.removeEventListener('transitionend', onEnd);
        if (swapTimeout) clearTimeout(swapTimeout);
        isTransitioning = false;
        wrap.classList.remove('c-slider__image-wrap--transitioning');
        currentSlide.classList.remove('c-slider__slide--out-left', 'c-slider__slide--out-right');
        nextSlide.classList.remove('c-slider__slide--in-from-left', 'c-slider__slide--in-from-right');
        var curImg = currentSlide.querySelector('[data-slider-image]');
        curImg.src = item.url;
        curImg.alt = item.title || '';
        currentSlide.classList.remove('c-slider__slide--current');
        currentSlide.classList.add('c-slider__slide--next');
        nextSlide.classList.remove('c-slider__slide--next');
        nextSlide.classList.add('c-slider__slide--current');
        var tmp = currentSlide;
        currentSlide = nextSlide;
        nextSlide = tmp;
        currentIndex = index;
        setCaption(index);
        updateDots();
        resetAutoplay();
      }
      function onEnd(e) {
        if (e.propertyName !== 'transform') return;
        if (e.target !== currentSlide && e.target !== nextSlide) return;
        endCount += 1;
        if (endCount === 2) finishSwap();
      }
      wrap.addEventListener('transitionend', onEnd);
      setCaption(index);
      updateDots();
    }

    function updateDots() {
      dots.forEach(function (dot, i) {
        var active = i === currentIndex;
        dot.classList.toggle('c-slider__dot--active', active);
        dot.setAttribute('aria-current', active ? 'true' : 'false');
      });
    }

    function goNext() {
      go((currentIndex + 1) % items.length);
    }
    function goPrev() {
      go(currentIndex === 0 ? items.length - 1 : currentIndex - 1);
    }

    function startAutoplay() {
      stopAutoplay();
      if (!autoplay) return;
      autoplayTimer = setInterval(goNext, AUTOPLAY_MS);
    }
    function stopAutoplay() {
      if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    }
    function resetAutoplay() {
      startAutoplay();
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { goPrev(); stopAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { goNext(); stopAutoplay(); });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var index = parseInt(dot.getAttribute('data-index'), 10);
        if (!isNaN(index)) {
          go(index);
          stopAutoplay();
        }
      });
    });

    // Touch/drag (simple: swipe left = next, swipe right = prev)
    var touchStartX = 0;
    var touchEndX = 0;
    wrap.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    wrap.addEventListener('touchend', function (e) {
      touchEndX = e.changedTouches[0].screenX;
      var diff = touchStartX - touchEndX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) goNext();
        else goPrev();
        stopAutoplay();
      }
    }, { passive: true });

    startAutoplay();
  });

  // --- Slideshow (square, crossfade, autoplay, hover/tap to show controls) ---
  document.querySelectorAll('.c-slider--slideshow').forEach(function (root) {
    var items = parseItems(root);
    if (!items.length) return;

    var wrap = root.querySelector('.c-slider__image-wrap');
    var currentSlide = root.querySelector('.c-slider__slide--current');
    var nextSlide = root.querySelector('.c-slider__slide--next');
    var captionEl = root.querySelector('[data-slider-caption]');
    var showCaptions = root.getAttribute('data-show-captions') === '1';
    var prevBtn = root.querySelector('[data-slider-prev]');
    var nextBtn = root.querySelector('[data-slider-next]');
    var dots = root.querySelectorAll('.c-slider__dot');
    if (!wrap || !currentSlide || !nextSlide) return;

    var currentIndex = 0;
    var isTransitioning = false;
    var autoplayTimer = null;
    var AUTOPLAY_MS = 5000;

    function setCaption(index) {
      if (!showCaptions || !captionEl) return;
      var item = items[index];
      if (!item) return;
      var html = '';
      if (item.caption && item.caption.trim() !== '') {
        html += '<p class="c-slider__caption-text">' + escapeHtml(item.caption) + '</p>';
      }
      if (item.author && item.author.trim() !== '') {
        html += '<p class="c-slider__caption-author">' + escapeHtml(item.author) + '</p>';
      }
      captionEl.innerHTML = html;
      var hasContent = (item.caption && item.caption.trim() !== '') || (item.author && item.author.trim() !== '');
      captionEl.classList.toggle('c-slider__caption--visible', !!hasContent);
    }

    function escapeHtml(s) {
      var div = document.createElement('div');
      div.textContent = s;
      return div.innerHTML;
    }

    function go(index) {
      var item = items[index];
      if (!item || index === currentIndex || isTransitioning) return;

      isTransitioning = true;
      wrap.classList.add('c-slider__image-wrap--transitioning');

      var nextImg = nextSlide.querySelector('[data-slider-image]');
      nextImg.src = item.url;
      nextImg.alt = item.title || '';

      currentSlide.classList.add('c-slider__slide--fade-out');
      nextSlide.classList.add('c-slider__slide--fade-in');

      var onEnd = function (e) {
        if (e.propertyName !== 'opacity' || e.target !== nextSlide) return;
        wrap.removeEventListener('transitionend', onEnd);
        isTransitioning = false;
        currentSlide.classList.remove('c-slider__slide--fade-out');
        nextSlide.classList.remove('c-slider__slide--fade-in');
        var curImg = currentSlide.querySelector('[data-slider-image]');
        curImg.src = item.url;
        curImg.alt = item.title || '';
        currentSlide.classList.remove('c-slider__slide--current');
        currentSlide.classList.add('c-slider__slide--next');
        nextSlide.classList.remove('c-slider__slide--next');
        nextSlide.classList.add('c-slider__slide--current');
        var tmp = currentSlide;
        currentSlide = nextSlide;
        nextSlide = tmp;
        currentIndex = index;
        setCaption(index);
        updateDots();
        startAutoplay();
      };
      wrap.addEventListener('transitionend', onEnd);
      setCaption(index);
      updateDots();
    }

    function updateDots() {
      dots.forEach(function (dot, i) {
        var active = i === currentIndex;
        dot.classList.toggle('c-slider__dot--active', active);
        dot.setAttribute('aria-current', active ? 'true' : 'false');
      });
    }

    function goNext() {
      go((currentIndex + 1) % items.length);
    }
    function goPrev() {
      go(currentIndex === 0 ? items.length - 1 : currentIndex - 1);
    }

    function startAutoplay() {
      stopAutoplay();
      if (!wrap.classList.contains('c-slider__image-wrap--controls-visible')) {
        autoplayTimer = setInterval(goNext, AUTOPLAY_MS);
      }
    }
    function stopAutoplay() {
      if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    }

    wrap.addEventListener('mouseenter', function () {
      wrap.classList.add('c-slider__image-wrap--controls-visible');
      stopAutoplay();
    });
    wrap.addEventListener('mouseleave', function () {
      wrap.classList.remove('c-slider__image-wrap--controls-visible');
      startAutoplay();
    });

    wrap.addEventListener('click', function (e) {
      if (!('ontouchstart' in window)) return;
      if (e.target.closest('.c-slider__arrow') || e.target.closest('.c-slider__dot')) return;
      wrap.classList.toggle('c-slider__image-wrap--controls-visible');
      if (wrap.classList.contains('c-slider__image-wrap--controls-visible')) {
        stopAutoplay();
      } else {
        startAutoplay();
      }
    });

    if (prevBtn) prevBtn.addEventListener('click', function (e) { e.stopPropagation(); goPrev(); });
    if (nextBtn) nextBtn.addEventListener('click', function (e) { e.stopPropagation(); goNext(); });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function (e) {
        e.stopPropagation();
        var index = parseInt(dot.getAttribute('data-index'), 10);
        if (!isNaN(index)) go(index);
      });
    });

    startAutoplay();
  });
})();
