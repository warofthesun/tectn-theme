(function () {
  var sliders = document.querySelectorAll('.c-slider--toc');
  if (!sliders.length) return;

  sliders.forEach(function (root) {
    var itemsData = root.getAttribute('data-items');
    var items = [];
    try {
      items = itemsData ? JSON.parse(itemsData) : [];
    } catch (e) {}
    if (!items.length) return;

    var wrap = root.querySelector('.c-slider__image-wrap');
    var slides = root.querySelectorAll('[data-slider-slide]');
    var currentSlide = root.querySelector('.c-slider__slide--current');
    var nextSlide = root.querySelector('.c-slider__slide--next');
    var buttons = root.querySelectorAll('.c-slider__list-btn');
    if (!wrap || !currentSlide || !nextSlide || !buttons.length) return;

    var currentIndex = 0;
    var isTransitioning = false;

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
  });
})();
