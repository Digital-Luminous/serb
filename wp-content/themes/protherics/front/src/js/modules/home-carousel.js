const selector = {
  carousel: '.c-home-carousel',
  stage: '.c-home-carousel__stage',
  panorama: '.c-home-carousel__panorama',
  tile: '.c-home-carousel__tile',
};

const state = {
  activeClass: 'is-active',
  desktopQuery: window.matchMedia('(min-width: 1024px)'),
};

const roundRectPath = (x, y, width, height, radius) => {
  const r = Math.min(radius, width / 2, height / 2);

  return [
    `M ${x + r} ${y}`,
    `H ${x + width - r}`,
    `A ${r} ${r} 0 0 1 ${x + width} ${y + r}`,
    `V ${y + height - r}`,
    `A ${r} ${r} 0 0 1 ${x + width - r} ${y + height}`,
    `H ${x + r}`,
    `A ${r} ${r} 0 0 1 ${x} ${y + height - r}`,
    `V ${y + r}`,
    `A ${r} ${r} 0 0 1 ${x + r} ${y}`,
    'Z',
  ].join(' ');
};

const HomeCarousel = {
  init() {
    document.querySelectorAll(selector.carousel).forEach((carousel) => {
      const stage = carousel.querySelector(selector.stage);
      const panorama = carousel.querySelector(selector.panorama);
      const tiles = carousel.querySelectorAll(selector.tile);

      if (!stage || !panorama || !tiles.length) {
        return;
      }

      if (!panorama.dataset.alt) {
        panorama.dataset.alt = panorama.getAttribute('alt') || '';
      }

      const update = () => {
        HomeCarousel.updatePanoramaAccessibility(panorama);
        HomeCarousel.updateClip(stage, panorama, tiles);
      };

      update();

      if (!panorama.complete) {
        panorama.addEventListener('load', update, { once: true });
      }

      const resizeObserver = new ResizeObserver(update);
      resizeObserver.observe(stage);

      tiles.forEach((tile) => {
        resizeObserver.observe(tile);
        tile.addEventListener('transitionend', update);
      });

      stage.addEventListener('transitionend', update);
      window.addEventListener('resize', update, { passive: true });

      state.desktopQuery.addEventListener('change', update);

      HomeCarousel.initExpansion(carousel, tiles, update);
    });
  },

  initExpansion(carousel, tiles, onActiveChange) {
    const setActiveTile = (tile) => {
      if (!state.desktopQuery.matches) {
        return;
      }

      tiles.forEach((currentTile) => {
        currentTile.classList.remove(state.activeClass);
      });

      tile.classList.add(state.activeClass);
      onActiveChange();
    };

    if (!tiles[0].classList.contains(state.activeClass)) {
      tiles[0].classList.add(state.activeClass);
    }

    tiles.forEach((tile) => {
      tile.addEventListener('mouseenter', () => {
        setActiveTile(tile);
      });

      tile.addEventListener('focusin', () => {
        setActiveTile(tile);
      });
    });

    requestAnimationFrame(() => {
      carousel.classList.add('is-interactive');
    });
  },

  updatePanoramaAccessibility(panorama) {
    if (!state.desktopQuery.matches) {
      panorama.hidden = true;
      panorama.setAttribute('aria-hidden', 'true');
      panorama.alt = '';
      return;
    }

    panorama.hidden = false;
    panorama.removeAttribute('aria-hidden');
    panorama.alt = panorama.dataset.alt || '';
  },

  updateClip(stage, panorama, tiles) {
    if (!state.desktopQuery.matches) {
      panorama.style.clipPath = '';
      return;
    }

    const stageRect = stage.getBoundingClientRect();

    if (!stageRect.width || !stageRect.height) {
      return;
    }

    const radius =
      parseFloat(getComputedStyle(tiles[0]).borderTopLeftRadius) || 0;
    const svgNS = 'http://www.w3.org/2000/svg';
    let svg = stage.querySelector('.c-home-carousel__clip-svg');
    let clipPath = svg ? svg.querySelector('clipPath') : null;

    if (!svg) {
      svg = document.createElementNS(svgNS, 'svg');
      svg.setAttribute('class', 'c-home-carousel__clip-svg');
      svg.setAttribute('aria-hidden', 'true');

      const defs = document.createElementNS(svgNS, 'defs');
      clipPath = document.createElementNS(svgNS, 'clipPath');
      clipPath.id = `home-carousel-clip-${Math.random()
        .toString(36)
        .slice(2, 9)}`;
      clipPath.setAttribute('clipPathUnits', 'userSpaceOnUse');
      defs.appendChild(clipPath);
      svg.appendChild(defs);
      stage.appendChild(svg);
    }

    clipPath.innerHTML = '';

    tiles.forEach((tile) => {
      const rect = tile.getBoundingClientRect();
      const path = document.createElementNS(svgNS, 'path');
      path.setAttribute(
        'd',
        roundRectPath(
          rect.left - stageRect.left,
          rect.top - stageRect.top,
          rect.width,
          rect.height,
          radius
        )
      );
      clipPath.appendChild(path);
    });

    panorama.style.clipPath = `url(#${clipPath.id})`;
  },
};

HomeCarousel.init();

export default HomeCarousel;
