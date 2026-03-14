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

    var imageEl = root.querySelector('[data-slider-image]');
    var buttons = root.querySelectorAll('.c-slider__list-btn');
    if (!imageEl || !buttons.length) return;

    function showIndex(index) {
      var item = items[index];
      if (!item) return;
      imageEl.src = item.url;
      imageEl.alt = item.title || '';

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
