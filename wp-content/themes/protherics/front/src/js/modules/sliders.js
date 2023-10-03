import $ from 'jquery';
import Swiper, { Pagination, Autoplay } from 'swiper';

const selector = {
  pagination: '.js-slider-pagination',
  inner: '.l-inner',
  slider: '.js-slider',
  swiperSlide: '.swiper-slide',
  paginationBullet: '.c-slider-pagination__bullet',
};

const sliderDefaultPagination = {
  clickable: true,
  type: 'bullets',
  bulletClass: 'c-slider-pagination__bullet',
  bulletActiveClass: 'c-slider-pagination__bullet--active',
};

const slidersConfig = {
  news: {
    spaceBetween: 20,
    slidesPerView: 1,
    modules: [Pagination],
    pagination: true,
    breakpoints: {
      768: {
        disabled: true,
      },
    },
  },
  banners: {
    watchOverflow: true,
    slidesPerView: 1,
    modules: [Pagination, Autoplay],
    pagination: true,
    speed: 600,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
  },
};

const Slider = {
  sliderInstances: [],
  matchMediaInstances: [],

  init() {
    Slider.createMatchMediaInstances();

    Slider.initSliders();
  },

  initSliders() {
    Slider.sliderInstances.forEach((slider) => {
      const { $wrapperEl } = slider;
      slider.destroy();
      $($wrapperEl)
        .closest(selector.inner)
        .find(selector.paginationBullet)
        .remove();
    });

    Slider.sliderInstances = [];

    $(selector.slider).each(function () {
      const sliderType = $(this).data('slider-type');
      const config = slidersConfig[sliderType];

      if (!config) return;

      const { pagination, navigation } = config;
      if (pagination) {
        const paginationEl = $(this).find(selector.pagination)[0];

        config.pagination = {
          ...sliderDefaultPagination,
          ...pagination,
          el: paginationEl
        };
      }

      if (navigation) {
        const navigationEl = $(this)
          .closest(selector.inner)
          .find(selector.navigation)[0];

        config.navigation = {
          ...Slider.getNavigationElements(navigationEl),
          ...navigation,
        };
      }

      const disabled = Slider.getIsSliderDisabled(config);

      if (disabled) {
        return;
      }

      const minItemsCount = Slider.getMinSlidersCount(config);

      if (
        minItemsCount !== undefined &&
        $(this).find(selector.swiperSlide).length < minItemsCount
      ) {
        return;
      }

      const sliderInstance = new Swiper(this, config);
      sliderInstance.sliderType = sliderType;

      Slider.sliderInstances.push(sliderInstance);
    });
  },

  getNavigationElements(navElement) {
    return {
      nextEl: $(navElement).find(selector.navNext)[0],
      prevEl: $(navElement).find(selector.navPrev)[0],
    };
  },

  getCurerntBreakpointConfig(breakpoints = {}) {
    const matchingBreakpoint = Object.keys(breakpoints)
      .map((item) => Number(item))
      .sort((a, b) => b - a)
      .find((key) => {
        return window.matchMedia(`(min-width: ${key}px)`).matches;
      });
    return breakpoints[matchingBreakpoint];
  },

  getMinSlidersCount({ breakpoints = {}, minItems }) {
    const matchingBreakpointData =
      Slider.getCurerntBreakpointConfig(breakpoints);

    if (
      matchingBreakpointData &&
      matchingBreakpointData.minItems !== undefined
    ) {
      return matchingBreakpointData.minItems;
    }

    return minItems;
  },

  getIsSliderDisabled({ breakpoints = {} }) {
    const matchingBreakpointData =
      Slider.getCurerntBreakpointConfig(breakpoints);

    if (
      matchingBreakpointData &&
      matchingBreakpointData.disabled !== undefined
    ) {
      return matchingBreakpointData.disabled;
    }

    return false;
  },

  createMatchMediaInstances() {
    const allBreakpoints = Object.values(slidersConfig).reduce(
      (result, { breakpoints = {} }) => {
        return [...result, ...Object.keys(breakpoints)];
      },
      []
    );

    const uniqueBreakpoints = allBreakpoints.filter(
      (v, i, self) => self.indexOf(v) === i
    );

    for (const breakpoint of uniqueBreakpoints) {
      const matchMediaInstance = window.matchMedia(
        `(min-width: ${breakpoint}px)`
      );
      matchMediaInstance.breakpoint = breakpoint;
      Slider.matchMediaInstances.push(matchMediaInstance);

      matchMediaInstance.addEventListener('change', () => {
        Slider.initSliders();
      });
    }
  },
};

Slider.init();
