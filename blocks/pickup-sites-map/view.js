(function () {
  var containers = document.querySelectorAll('.c-pickup-sites-map:not(.c-pickup-sites-map--preview)');
  if (!containers.length) return;

  var toInit = [];
  var apiKey = '';
  for (var i = 0; i < containers.length; i++) {
    var el = containers[i];
    var pinsData = el.getAttribute('data-pins');
    var key = (el.getAttribute('data-api-key') || '').trim();
    var showSearch = el.getAttribute('data-show-search') !== '0';
    var enableClustering = el.getAttribute('data-enable-clustering') !== '0';
    var pins = [];
    try {
      pins = pinsData ? JSON.parse(pinsData) : [];
    } catch (e) {}
    if (pins.length && key) {
      toInit.push({ container: el, apiKey: key, pins: pins, showSearch: showSearch, enableClustering: enableClustering });
      if (!apiKey) apiKey = key;
    }
  }

  var anyClustering = toInit.some(function (item) { return item.enableClustering; });

  function runInit() {
    toInit.forEach(function (item) {
      initMap(item.container, item.apiKey, item.pins, item.showSearch, item.enableClustering);
    });
  }

  function loadMarkerClusterer(callback) {
    if (!anyClustering) {
      callback();
      return;
    }
    if (typeof markerClusterer !== 'undefined' && markerClusterer.MarkerClusterer) {
      callback();
      return;
    }
    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/@googlemaps/markerclusterer@2.5.3/dist/index.umd.min.js';
    script.async = true;
    script.onload = callback;
    script.onerror = function () { callback(); }
    document.head.appendChild(script);
  }

  function initMap(container, key, pinsData, showSearch, enableClustering) {
    var canvas = container.querySelector('.c-pickup-sites-map__canvas');
    var searchInput = showSearch ? container.querySelector('.c-pickup-sites-map__search') : null;
    if (!canvas) return;

    var map = new google.maps.Map(canvas, {
      zoom: 10,
      center: { lat: pinsData[0].lat, lng: pinsData[0].lng },
      mapTypeControl: true,
      fullscreenControl: true,
      streetViewControl: false,
    });

    var bounds = new google.maps.LatLngBounds();
    var closeTimeout = null;
    var currentInfoWindow = null;
    var allMarkerData = [];

    pinsData.forEach(function (pin, pinIndex) {
      var pos = { lat: pin.lat, lng: pin.lng };
      bounds.extend(pos);

      var marker = new google.maps.Marker({
        position: pos,
        map: null,
        title: pin.name,
      });

      var tooltipId = 'psm-tooltip-' + (container.id || 'map') + '-' + pinIndex;
      var content = '<div id="' + escapeAttr(tooltipId) + '" class="c-pickup-sites-map__tooltip">' +
        '<strong class="c-pickup-sites-map__tooltip-name">' + escapeHtml(pin.name) + '</strong>' +
        (pin.address ? '<p class="c-pickup-sites-map__tooltip-address">' + escapeHtml(pin.address) + '</p>' : '') +
        '<a href="' + escapeAttr(pin.url) + '" class="c-pickup-sites-map__tooltip-link">Click to learn more</a>' +
        '</div>';

      var infoWindow = new google.maps.InfoWindow({
        content: content,
      });

      marker.addListener('mouseover', function () {
        if (closeTimeout) {
          clearTimeout(closeTimeout);
          closeTimeout = null;
        }
        if (currentInfoWindow) currentInfoWindow.close();
        currentInfoWindow = infoWindow;
        infoWindow.open(map, marker);
      });

      marker.addListener('mouseout', function () {
        closeTimeout = setTimeout(function () {
          infoWindow.close();
          if (currentInfoWindow === infoWindow) currentInfoWindow = null;
          closeTimeout = null;
        }, 200);
      });

      google.maps.event.addListener(infoWindow, 'domready', function () {
        var tooltipEl = document.getElementById(tooltipId);
        if (!tooltipEl) return;
        tooltipEl.addEventListener('mouseenter', function () {
          if (closeTimeout) {
            clearTimeout(closeTimeout);
            closeTimeout = null;
          }
        });
        tooltipEl.addEventListener('mouseleave', function () {
          closeTimeout = setTimeout(function () {
            infoWindow.close();
            if (currentInfoWindow === infoWindow) currentInfoWindow = null;
            closeTimeout = null;
          }, 200);
        });
      });

      allMarkerData.push({ marker: marker, pin: pin });
    });

    var markerCluster = null;
    var useClustering = enableClustering && typeof markerClusterer !== 'undefined' && markerClusterer.MarkerClusterer;
    if (useClustering) {
      markerCluster = new markerClusterer.MarkerClusterer({
        map: map,
        markers: allMarkerData.map(function (d) { return d.marker; }),
      });
    } else {
      allMarkerData.forEach(function (d) {
        d.marker.setMap(map);
      });
    }

    function applyFilter(query) {
      var q = (query || '').trim().toLowerCase();
      var visible;
      if (!q) {
        visible = allMarkerData;
      } else {
        visible = allMarkerData.filter(function (d) {
          var name = (d.pin.name || '').toLowerCase();
          var addr = (d.pin.address || '').toLowerCase();
          return name.indexOf(q) !== -1 || addr.indexOf(q) !== -1;
        });
      }
      if (markerCluster) {
        var visibleMarkers = visible.map(function (d) { return d.marker; });
        markerCluster.clearMarkers();
        markerCluster.addMarkers(visibleMarkers);
      } else {
        allMarkerData.forEach(function (d) {
          d.marker.setMap(visible.indexOf(d) !== -1 ? map : null);
        });
      }
      if (visible.length > 0) {
        var b = new google.maps.LatLngBounds();
        visible.forEach(function (d) {
          b.extend(d.marker.getPosition());
        });
        map.fitBounds(b, { top: 60, right: 40, bottom: 40, left: 40 });
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        applyFilter(searchInput.value);
      });
      searchInput.addEventListener('search', function () {
        applyFilter(searchInput.value);
      });
    }

    if (pinsData.length > 1) {
      map.fitBounds(bounds, { top: 60, right: 40, bottom: 40, left: 40 });
    }
  }

  function escapeHtml(s) {
    if (!s) return '';
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function escapeAttr(s) {
    if (!s) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  if (!toInit.length) {
    containers.forEach(function (c) {
      var canvas = c.querySelector('.c-pickup-sites-map__canvas');
      if (canvas && !c.querySelector('.c-pickup-sites-map__empty')) {
        var key = (c.getAttribute('data-api-key') || '').trim();
        if (!key) {
          canvas.innerHTML = '<p style="padding:1em;margin:0;">Set a Google Maps API key in ACF settings to show the map.</p>';
        }
      }
    });
    return;
  }

  function whenReady() {
    loadMarkerClusterer(runInit);
  }

  if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
    whenReady();
    return;
  }

  /* Avoid loading the Maps API twice (e.g. if another script already loads it). */
  var existingMaps = document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]');
  if (existingMaps) {
    var waitForMaps = function () {
      if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
        whenReady();
        return;
      }
      setTimeout(waitForMaps, 50);
    };
    waitForMaps();
    return;
  }

  /* Load Maps API once; use loading=async as recommended by Google. */
  if (document.getElementById('tectn-gmaps-api')) return;
  var script = document.createElement('script');
  script.id = 'tectn-gmaps-api';
  script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&callback=tectnPickupSitesMapInit&loading=async';
  script.async = true;
  script.defer = true;
  window.tectnPickupSitesMapInit = whenReady;
  document.head.appendChild(script);
})();
