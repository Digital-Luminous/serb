const selector = {
  section: '.l-location-map',
  map: '.js-location-map',
  data: '.js-location-map-data',
};

// Global callback name, must match the callback parameter in the template.
const callbackName = 'prothericsInitLocationMaps';

const config = {
  defaultCenter: { lat: 48.5, lng: 10 },
  defaultZoom: 4,
  singleLocationZoom: 6,
  fitPadding: 60,
  iconWidth: 22,
  iconHeight: 26,
  // Pin hover lift, in pixels and ms.
  hoverLift: 4,
  hoverDuration: 200,
  // Pins closer than this on screen collapse into one pin.
  clusterRadius: 36,
  // A cluster click can come in closer than a continent so the pins separate.
  maxClusterZoom: 16,
  // At that zoom, pins that still overlap (same spot) fan out on a circle, in pixels.
  spreadMinRadius: 28,
  spreadPerPin: 8,
  // Never closer than a continent, even on a large map.
  maxContinentZoom: 5,
  minContinentZoom: 2,
  // Camera move, in ms: a base time plus extra per zoom level, with a cap.
  continuousBase: 700,
  continuousPerZoom: 350,
  continuousMax: 2200,
  // Room kept free inside the map for the pin, the popup pointer and some air.
  popupReserve: 100,
  popupMinHeight: 180,
  // Space left on the sides of a narrow map, and the narrowest the popup gets.
  popupSideGap: 40,
  popupMinWidth: 200,
};

// The plain purple pin, used when a pin type has no uploaded icon.
const defaultPinSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40" fill="none"><path d="M16 0C7.16344 0 0 7.16344 0 16C0 20.2482 1.65563 24.1097 4.35685 26.9745L16 40L27.6431 26.9745C30.3444 24.1097 32 20.2482 32 16C32 7.16344 24.8366 0 16 0Z" fill="#54178E"/></svg>';

// Running camera animations, one per map.
const animations = new WeakMap();

// Checked in order. Smaller regions come before the ones that overlap them.
const continents = [
  { north: 71, south: 35, west: -25, east: 40 },
  { north: 37, south: -35, west: -18, east: 52 },
  { north: 70, south: 25, west: -140, east: -52 },
  { north: 13, south: -56, west: -82, east: -34 },
  { north: -9, south: -44, west: 112, east: 154 },
  { north: 77, south: -10, west: 40, east: 180 },
];

// Muted palette that follows the theme colours. No POI, transit or labels.
const mapStyles = [
  { featureType: 'poi', stylers: [{ visibility: 'off' }] },
  { featureType: 'transit', stylers: [{ visibility: 'off' }] },
  { featureType: 'road', stylers: [{ visibility: 'off' }] },
  { elementType: 'labels', stylers: [{ visibility: 'off' }] },
  { featureType: 'landscape', elementType: 'geometry', stylers: [{ color: '#D6D1CB' }] },
  { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#F4F2EF' }] },
  { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#F4F2EF' }] },
  { featureType: 'administrative.country', elementType: 'geometry.stroke', stylers: [{ color: '#F4F2EF' }, { weight: 2 }] },
];

const LocationMap = {
  parseData(script) {
    if (!script) return null;

    try {
      return JSON.parse(script.textContent);
    } catch (error) {
      console.error(error);
      return null;
    }
  },

  // The box is taller than the image by the hover lift, so the lifted pin
  // still covers the pointer and does not flicker at its tip.
  markerIcon(url) {
    return {
      url,
      size: new google.maps.Size(config.iconWidth, config.iconHeight + config.hoverLift),
      scaledSize: new google.maps.Size(config.iconWidth, config.iconHeight),
      anchor: new google.maps.Point(config.iconWidth / 2, config.iconHeight),
    };
  },

  loadIcon(url) {
    if (!url || !window.google || !window.google.maps) return null;

    return LocationMap.markerIcon(url);
  },

  // Moves the icon anchor rather than the element, so it works however Google draws the marker.
  liftMarker(marker, lifted) {
    const icon = marker.getIcon();
    if (!icon || typeof icon === 'string' || !icon.anchor) return;

    const from = icon.anchor.y;
    const to = config.iconHeight + (lifted ? config.hoverLift : 0);

    cancelAnimationFrame(marker.get('liftFrame'));
    if (from === to) return;

    const start = performance.now();

    const step = (now) => {
      const progress = Math.min(1, (now - start) / config.hoverDuration);
      const eased = 1 - Math.pow(1 - progress, 3);

      marker.setIcon({
        ...icon,
        anchor: new google.maps.Point(icon.anchor.x, from + (to - from) * eased),
      });

      if (progress < 1) marker.set('liftFrame', requestAnimationFrame(step));
    };

    marker.set('liftFrame', requestAnimationFrame(step));
  },

  dropMarker(marker) {
    cancelAnimationFrame(marker.get('liftFrame'));

    const icon = marker.getIcon();
    if (!icon || typeof icon === 'string' || !icon.anchor || icon.anchor.y === config.iconHeight) return;

    marker.setIcon({ ...icon, anchor: new google.maps.Point(icon.anchor.x, config.iconHeight) });
  },

  bindLift(marker) {
    marker.addListener('mouseover', () => LocationMap.liftMarker(marker, true));
    marker.addListener('mouseout', () => LocationMap.liftMarker(marker, false));
  },

  loadIcons(pins) {
    return Object.fromEntries(
      pins.map((pin) => [pin.id, LocationMap.loadIcon(pin.icon)])
    );
  },

  // Same uploaded pin, with a plus drawn in the head, for a cluster of that type.
  loadClusterArtwork(pins) {
    const fallback = defaultPinSvg;

    return Promise.all(
      pins.map((pin) => {
        if (!pin.icon) return [pin.id, LocationMap.withPlus(fallback)];

        return fetch(pin.icon)
          .then((response) => response.text())
          .then((svg) => [pin.id, LocationMap.withPlus(svg)])
          .catch(() => [pin.id, LocationMap.withPlus(fallback)]);
      })
    ).then((entries) => Object.fromEntries(entries));
  },

  withPlus(svg) {
    const source = svg.replace(/<\?xml[^>]*>/, '').trim();
    const box = source.match(/viewBox="([\d.\s-]+)"/);
    const parts = box ? box[1].trim().split(/\s+/).map(Number) : [0, 0, 32, 40];
    const width = parts[2] || 32;
    const height = parts[3] || 40;
    const x = (parts[0] || 0) + width / 2;
    const y = (parts[1] || 0) + height * 0.4;
    const arm = Math.min(width, height) * 0.28;
    const thickness = arm * 0.25;
    const plus = `<path fill="#ffffff" d="M${x - thickness / 2} ${y - arm}h${thickness}v${arm * 2}h${-thickness}zM${x - arm} ${y - thickness / 2}h${arm * 2}v${thickness}h${-arm * 2}z"/>`;

    return source.replace('</svg>', `${plus}</svg>`);
  },

  clusterIcon(artwork, pinId) {
    if (!LocationMap.clusterIconCache) LocationMap.clusterIconCache = {};
    const key = pinId || 'default';
    if (LocationMap.clusterIconCache[key]) return LocationMap.clusterIconCache[key];

    const svg = (pinId && artwork[pinId]) || LocationMap.withPlus(defaultPinSvg
    );

    const icon = LocationMap.markerIcon(
      `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`
    );

    LocationMap.clusterIconCache[key] = icon;
    return icon;
  },

  isValidPosition(position) {
    return (
      position.lat >= -90 &&
      position.lat <= 90 &&
      position.lng >= -180 &&
      position.lng <= 180
    );
  },

  groupByDistance(points, radius) {
    const parent = points.map((_, index) => index);
    const find = (index) => {
      if (parent[index] !== index) parent[index] = find(parent[index]);
      return parent[index];
    };
    const radiusSq = radius * radius;

    for (let i = 0; i < points.length; i += 1) {
      for (let j = i + 1; j < points.length; j += 1) {
        // Only pins of the same type cluster together.
        if (points[i].entry.location.pinId !== points[j].entry.location.pinId) continue;

        const dx = points[i].x - points[j].x;
        const dy = points[i].y - points[j].y;
        if (dx * dx + dy * dy <= radiusSq) parent[find(i)] = find(j);
      }
    }

    const groups = new Map();
    points.forEach((point, index) => {
      const root = find(index);
      if (!groups.has(root)) groups.set(root, []);
      groups.get(root).push(point.entry);
    });

    return [...groups.values()];
  },

  averagePosition(entries) {
    const total = entries.reduce(
      (sum, entry) => ({
        lat: sum.lat + entry.position.lat,
        lng: sum.lng + entry.position.lng,
      }),
      { lat: 0, lng: 0 }
    );

    return {
      lat: total.lat / entries.length,
      lng: total.lng / entries.length,
    };
  },

  latRad(lat) {
    const sin = Math.sin((lat * Math.PI) / 180);
    const radX2 = Math.log((1 + sin) / (1 - sin)) / 2;

    return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
  },

  zoomForSpan(mapPx, fraction) {
    if (!mapPx || !fraction) return config.minContinentZoom;

    return Math.round(Math.log(mapPx / 256 / fraction) / Math.LN2);
  },

  zoomForContinent(map, position) {
    const continent = continents.find(
      (area) =>
        position.lat <= area.north &&
        position.lat >= area.south &&
        position.lng >= area.west &&
        position.lng <= area.east
    );

    if (!continent) return config.minContinentZoom;

    const latFraction =
      (LocationMap.latRad(continent.north) - LocationMap.latRad(continent.south)) /
      Math.PI;
    let lngDiff = continent.east - continent.west;
    if (lngDiff < 0) lngDiff += 360;
    const lngFraction = lngDiff / 360;

    const mapEl = map.getDiv();
    const zoom = Math.min(
      LocationMap.zoomForSpan(mapEl.offsetWidth, lngFraction),
      LocationMap.zoomForSpan(mapEl.offsetHeight, latFraction)
    );

    return Math.max(config.minContinentZoom, Math.min(zoom, config.maxContinentZoom));
  },

  isAnimating(map) {
    return animations.has(map);
  },

  stopAnimation(map) {
    const animation = animations.get(map);
    if (!animation) return;

    window.cancelAnimationFrame(animation.frame);
    animations.delete(map);
  },

  easeInOut(progress) {
    return progress < 0.5
      ? 4 * progress * progress * progress
      : 1 - Math.pow(-2 * progress + 2, 3) / 2;
  },

  animateCamera(map, center, zoom, done) {
    LocationMap.stopAnimation(map);

    const projection = map.getProjection();
    const reduceMotion =
      window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!projection || reduceMotion) {
      map.setCenter(center);
      map.setZoom(zoom);
      if (done) done();
      return;
    }

    LocationMap.animateContinuous(map, center, zoom, done);
  },

  // Fractional zoom and centre in one call. Vector maps have moveCamera, which
  // sets both at once; raster maps take them one after the other.
  setCamera(map, center, zoom) {
    if (typeof map.moveCamera === 'function') {
      map.moveCamera({ center, zoom });
      return;
    }

    map.setCenter(center);
    map.setZoom(zoom);
  },

  // One continuous move: the zoom and the centre change together on every frame
  // with fractional map.setZoom values. The centre follows the zoom so the target
  // glides to the middle of the map instead of drifting past it.
  animateContinuous(map, center, zoom, done) {
    const projection = map.getProjection();
    const startZoom = map.getZoom();
    const from = projection.fromLatLngToPoint(map.getCenter());
    const to = projection.fromLatLngToPoint(new google.maps.LatLng(center));

    // Take the short way round the world.
    if (to.x - from.x > 128) to.x -= 256;
    if (from.x - to.x > 128) to.x += 256;

    const animation = { frame: 0 };
    const levels = Math.abs(zoom - startZoom);
    const duration = Math.min(
      config.continuousBase + levels * config.continuousPerZoom,
      config.continuousMax
    );
    const startScale = Math.pow(2, -startZoom);
    const scaleSpan = startScale - Math.pow(2, -zoom);
    const startTime = window.performance.now();

    const finish = () => {
      animations.delete(map);
      LocationMap.setCamera(map, center, zoom);
      google.maps.event.trigger(map, 'idle');
      if (done) done();
    };

    const tick = (now) => {
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = LocationMap.easeInOut(progress);
      const currentZoom = startZoom + (zoom - startZoom) * eased;

      // How far along the path the centre has to be at this zoom.
      const travelled =
        Math.abs(scaleSpan) < 1e-9
          ? eased
          : (startScale - Math.pow(2, -currentZoom)) / scaleSpan;

      LocationMap.setCamera(map, projection.fromPointToLatLng(
        new google.maps.Point(
          from.x + (to.x - from.x) * travelled,
          from.y + (to.y - from.y) * travelled
        )
      ), currentZoom);

      if (progress < 1) {
        animation.frame = window.requestAnimationFrame(tick);
      } else {
        finish();
      }
    };

    animations.set(map, animation);
    animation.frame = window.requestAnimationFrame(tick);
  },

  focusPin(map, position, done) {
    if (!LocationMap.isValidPosition(position)) {
      if (done) done();
      return;
    }

    const targetZoom = LocationMap.zoomForContinent(map, position);
    // Stay at a closer zoom. Zooming back out would hide this pin inside a cluster.
    const zoom = Math.max(targetZoom, map.getZoom());

    LocationMap.animateCamera(map, position, zoom, done);
  },

  fitView(map, entries) {
    const projection = map.getProjection();
    const points = entries.map((entry) =>
      projection.fromLatLngToPoint(new google.maps.LatLng(entry.position))
    );
    const xs = points.map((point) => point.x);
    const ys = points.map((point) => point.y);
    const minX = Math.min(...xs);
    const maxX = Math.max(...xs);
    const minY = Math.min(...ys);
    const maxY = Math.max(...ys);

    const mapEl = map.getDiv();
    const width = Math.max(mapEl.offsetWidth - config.fitPadding * 2, 1);
    const height = Math.max(mapEl.offsetHeight - config.fitPadding * 2, 1);
    // Points are in world units (0 to 256), which cover 256px at zoom 0.
    const zoomX = maxX > minX ? Math.log(width / (maxX - minX)) / Math.LN2 : Infinity;
    const zoomY = maxY > minY ? Math.log(height / (maxY - minY)) / Math.LN2 : Infinity;
    const zoom = Math.floor(Math.min(zoomX, zoomY));

    return {
      zoom: Number.isFinite(zoom) ? zoom : config.maxClusterZoom,
      center: projection.fromPointToLatLng(
        new google.maps.Point((minX + maxX) / 2, (minY + maxY) / 2)
      ),
    };
  },

  focusCluster(map, entries) {
    if (!map.getProjection()) return;

    const startZoom = map.getZoom() || 0;
    const view = LocationMap.fitView(map, entries);
    let zoom = Math.min(view.zoom, config.maxClusterZoom);

    // Always come closer, otherwise the click would look like it did nothing.
    if (zoom <= startZoom) zoom = Math.min(startZoom + 2, config.maxClusterZoom);

    LocationMap.animateCamera(map, view.center, zoom);
  },

  // Positions for a stack of pins on a small circle around its centre. Sizes are in screen pixels.
  fanPositions(projection, scale, group) {
    const origin = projection.fromLatLngToPoint(
      new google.maps.LatLng(LocationMap.averagePosition(group))
    );
    const radius = Math.max(config.spreadMinRadius, group.length * config.spreadPerPin);

    return group.map((_, index) => {
      const angle = (2 * Math.PI * index) / group.length - Math.PI / 2;

      return projection.fromPointToLatLng(
        new google.maps.Point(
          origin.x + (Math.cos(angle) * radius) / scale,
          origin.y + (Math.sin(angle) * radius) / scale
        )
      );
    });
  },

  // The popup never grows taller or wider than the map. Longer content scrolls inside
  // it, while the close button stays where it is.
  popupMaxHeight(element) {
    return Math.max(config.popupMinHeight, element.clientHeight - config.popupReserve);
  },

  popupMaxWidth(element) {
    return Math.max(config.popupMinWidth, element.clientWidth - config.popupSideGap);
  },

  createPopup(location, onClose, element) {
    const popup = document.createElement('div');
    popup.className = 'c-location-map__popup';

    if (element) {
      popup.style.maxHeight = `${LocationMap.popupMaxHeight(element)}px`;
      popup.style.maxWidth = `${LocationMap.popupMaxWidth(element)}px`;
    }

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'c-location-map__popup-close';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.addEventListener('click', onClose);
    popup.appendChild(closeBtn);

    const body = document.createElement('div');
    body.className = 'c-location-map__popup-body';
    popup.appendChild(body);

    if (location.title) {
      const title = document.createElement('h3');
      title.className = 'c-location-map__popup-title';
      title.textContent = location.title;
      body.appendChild(title);
    }

    if (location.address) {
      // Address is HTML from the WYSIWYG field, already sanitised with wp_kses_post in PHP.
      const content = document.createElement('div');
      content.className = 'c-location-map__popup-content';
      content.innerHTML = location.address;
      body.appendChild(content);
    }

    if (location.cta && location.cta.url && location.cta.title) {
      const cta = document.createElement('a');
      cta.className =
        'c-btn c-btn--primary c-btn--arrowed c-btn--small c-location-map__popup-cta';
      cta.href = location.cta.url;
      cta.textContent = location.cta.title;

      if (location.cta.target) {
        cta.target = location.cta.target;
      }

      if (location.cta.target === '_blank') {
        cta.rel = 'noopener noreferrer';
      }

      body.appendChild(cta);
    }

    return popup;
  },

  // Desktop equivalent of a one-finger drag on touch: a trackpad two-finger swipe or
  // mouse wheel pans the map instead of zooming it. Ctrl + wheel (which is what a
  // trackpad pinch sends) is left to Google Maps, so it still zooms.
  bindWheel(map, element) {
    element.addEventListener(
      'wheel',
      (event) => {
        if (event.ctrlKey) return;

        // A popup with more text than fits scrolls itself. Keep Google Maps from also
        // reacting to it, but leave the default action so the text scrolls.
        const body = event.target.closest && event.target.closest('.c-location-map__popup-body');
        if (body && body.scrollHeight > body.clientHeight) {
          event.stopPropagation();
          return;
        }

        // Capture phase, so Google Maps never sees the plain wheel and does not zoom.
        event.preventDefault();
        event.stopPropagation();
        LocationMap.stopAnimation(map);

        const lineHeight = 16;
        const scale =
          event.deltaMode === 1 ? lineHeight : event.deltaMode === 2 ? element.clientHeight : 1;

        map.panBy(event.deltaX * scale, event.deltaY * scale);
      },
      { capture: true, passive: false }
    );
  },

  initMap(element, data, icons, artwork) {
    const options = {
      center: config.defaultCenter,
      zoom: config.defaultZoom,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      // Show the classic + / − buttons instead of the collapsed camera control.
      cameraControl: false,
      zoomControl: true,
    };

    // A Map ID means vector rendering with the style set in Google Cloud Console.
    // Google ignores inline styles in that case, so only add them without an ID.
    if (data.mapId) {
      options.mapId = data.mapId;
    } else {
      options.styles = mapStyles;
    }

    const map = new google.maps.Map(element, options);
    LocationMap.bindWheel(map, element);

    const infoWindow = new google.maps.InfoWindow({
      headerDisabled: true,
      maxWidth: 250,
    });

    const bounds = new google.maps.LatLngBounds();
    let activeMarker = null;

    const entries = data.locations.reduce((list, location) => {
      const position = { lat: location.latitude, lng: location.longitude };
      if (!LocationMap.isValidPosition(position)) return list;

      const marker = new google.maps.Marker({
        position,
        title: location.title || '',
        icon: (location.pinId && icons[location.pinId]) || undefined,
      });

      LocationMap.bindLift(marker);

      const openPopup = () => {
        infoWindow.setContent(
          LocationMap.createPopup(location, () => infoWindow.close(), element)
        );
        infoWindow.open({ anchor: marker, map });
      };

      marker.addListener('click', () => {
        infoWindow.close();
        activeMarker = marker;

        // Open the popup once the camera has arrived, so it does not fight the glide.
        LocationMap.focusPin(map, position, () => {
          if (activeMarker !== marker) return;

          openPopup();
        });
      });

      bounds.extend(position);
      list.push({ location, position, marker });
      return list;
    }, []);

    const clusterMarkers = [];

    const showMarker = (marker) => {
      if (marker.getMap() !== map) marker.setMap(map);
    };

    // A hidden marker gets no mouseout, so drop the lift here or it comes back raised.
    const hideMarker = (marker) => {
      LocationMap.dropMarker(marker);
      if (marker.getMap()) marker.setMap(null);
    };

    // A fanned-out pin sits off its real spot. Put it back when it is no longer fanned.
    const restoreMarker = (entry) => {
      if (!entry.marker.get('fanned')) return;

      entry.marker.setPosition(entry.position);
      entry.marker.set('fanned', false);
    };

    const renderClusters = () => {
      const projection = map.getProjection();
      // Wait for the glide to finish, otherwise pins swap while the map moves.
      if (!projection || LocationMap.isAnimating(map)) return;

      const zoom = map.getZoom();
      const scale = Math.pow(2, zoom);
      // Zoomed in as far as clusters go, pins that still overlap fan out instead.
      const fanStacks = zoom >= config.maxClusterZoom - 0.01;
      const points = entries.map((entry) => {
        const world = projection.fromLatLngToPoint(new google.maps.LatLng(entry.position));
        return { entry, x: world.x * scale, y: world.y * scale };
      });
      const groups = LocationMap.groupByDistance(points, config.clusterRadius);
      const visible = new Set();
      let clusterIndex = 0;

      groups.forEach((group) => {
        if (group.length < 2) {
          restoreMarker(group[0]);
          showMarker(group[0].marker);
          visible.add(group[0].marker);
          return;
        }

        if (fanStacks) {
          LocationMap.fanPositions(projection, scale, group).forEach((position, index) => {
            const { marker } = group[index];

            marker.setPosition(position);
            marker.set('fanned', true);
            showMarker(marker);
            visible.add(marker);
          });
          return;
        }

        group.forEach((entry) => {
          restoreMarker(entry);
          hideMarker(entry.marker);
        });

        // Every pin in a group has the same type, so the cluster takes that pin.
        const pinId = group[0].location.pinId;
        let clusterMarker = clusterMarkers[clusterIndex];

        if (!clusterMarker) {
          clusterMarker = new google.maps.Marker({
            optimized: false,
            zIndex: 100,
          });
          clusterMarker.addListener('click', () => {
            infoWindow.close();
            activeMarker = null;
            LocationMap.focusCluster(map, clusterMarker.get('members'));
          });
          LocationMap.bindLift(clusterMarker);
          clusterMarkers[clusterIndex] = clusterMarker;
        }

        clusterMarker.setPosition(LocationMap.averagePosition(group));
        clusterMarker.setTitle(`${group.length} locations`);
        clusterMarker.set('members', group);

        if (clusterMarker.get('pinId') !== pinId) {
          clusterMarker.setIcon(LocationMap.clusterIcon(artwork, pinId));
          clusterMarker.set('pinId', pinId);
        }

        showMarker(clusterMarker);
        clusterIndex += 1;
      });

      clusterMarkers.slice(clusterIndex).forEach(hideMarker);

      if (activeMarker && !visible.has(activeMarker)) {
        infoWindow.close();
        activeMarker = null;
      }
    };

    map.addListener('idle', renderClusters);
    // Dragging takes over from a running glide.
    map.addListener('dragstart', () => LocationMap.stopAnimation(map));

    if (entries.length > 1) {
      map.fitBounds(bounds, config.fitPadding);
    } else if (entries.length === 1) {
      map.setCenter(entries[0].position);
      map.setZoom(config.singleLocationZoom);
    }
  },

  initBlock(element) {
    const section = element.closest(selector.section);
    const data = LocationMap.parseData(
      section ? section.querySelector(selector.data) : null
    );

    if (!data) return;

    const pins = data.pins || [];
    const icons = LocationMap.loadIcons(pins);

    LocationMap.loadClusterArtwork(pins).then((artwork) => {
      LocationMap.initMap(element, data, icons, artwork);
    });
  },

  initAll() {
    if (!window.google || !window.google.maps) return;

    document.querySelectorAll(selector.map).forEach((element) => {
      LocationMap.initBlock(element);
    });
  },
};

// Google Maps calls this once the API is loaded (see callback parameter in the template).
window[callbackName] = LocationMap.initAll;

// Fallback in case the API was already loaded before this module ran.
if (window.google && window.google.maps) {
  LocationMap.initAll();
}
