import $ from 'jquery';
import Swiper, { Pagination, Autoplay, Navigation, Controller } from 'swiper';

const selector = {
  pagination: '.js-slider-pagination',
  inner: '.l-inner',
  slider: '.js-slider',
  swiperSlide: '.swiper-slide',
  paginationBullet: '.c-slider-pagination__bullet',
  imageTextFrame: '.c-image-text-slider__frame',
  imageTextControls: '.c-image-text-slider__controls',
  imageTextPrev: '.c-image-text-slider__arrow--prev',
  imageTextNext: '.c-image-text-slider__arrow--next',
  imageTextMediaSlider: '.js-image-text-slider-media',
};

const getImageTextFrame = ($slider) => $slider.closest(selector.imageTextFrame);

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
      0: {
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
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
  },
  quote: {
    slidesPerView: 1,
    modules: [Pagination],
    pagination: true,
  },
  'image-text': {
    watchOverflow: true,
    slidesPerView: 1,
    spaceBetween: 0,
    speed: 600,
    loop: false,
    modules: [Pagination, Navigation, Autoplay, Controller],
    pagination: true,
    navigation: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    breakpoints: {
      0: {
        spaceBetween: 17,
      },
      1200: {
        spaceBetween: 0,
      },
    },
  },
};

const imageTextMediaConfig = {
  slidesPerView: 1,
  spaceBetween: 0,
  speed: 600,
  loop: false,
  allowTouchMove: false,
  modules: [Controller],
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
      const { $wrapperEl, sliderType, mediaSwiper } = slider;
      slider.destroy();

      if (mediaSwiper) {
        mediaSwiper.destroy();
      }

      const $cleanupRoot =
        sliderType === 'image-text'
          ? getImageTextFrame($($wrapperEl))
          : $($wrapperEl).closest(selector.inner);

      $cleanupRoot.find(selector.paginationBullet).remove();
    });

    Slider.sliderInstances = [];

    $(selector.slider).each(function () {
      const sliderType = $(this).data('slider-type');
      const config = slidersConfig[sliderType];
      const sliderToggleAutplay = $(this).find('.js-toggle-autoplay');

      if (!config) return;

      const { pagination, navigation } = config;
      if (pagination) {
        let paginationEl = $(this).find(selector.pagination)[0];

        if (!paginationEl && sliderType === 'image-text') {
          paginationEl = getImageTextFrame($(this)).find(selector.pagination)[0];
        }

        config.pagination = {
          ...sliderDefaultPagination,
          ...pagination,
          el: paginationEl,
        };
      }

      if (navigation) {
        const navigationEl = Slider.getNavigationRoot($(this), sliderType);

        if (navigationEl) {
          config.navigation = {
            ...Slider.getNavigationElements(navigationEl, sliderType),
            ...(typeof navigation === 'object' ? navigation : {}),
          };
        }
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

      if (sliderType === 'image-text') {
        Slider.initImageTextMediaSwiper($(this), sliderInstance);
      }

      Slider.sliderInstances.push(sliderInstance);
      if (sliderToggleAutplay.length > 0) {
        sliderToggleAutplay.click(() => {
          sliderToggleAutplay.toggleClass('inactive');

          if (sliderToggleAutplay.hasClass('inactive')) {
            sliderInstance.autoplay.stop();
          } else {
            sliderInstance.autoplay.start();
          }
        })
      }
    });
  },

  getNavigationRoot($slider, sliderType) {
    if (sliderType === 'image-text') {
      return getImageTextFrame($slider).find(selector.imageTextControls)[0];
    }

    return $slider.closest(selector.inner).find(selector.navigation)[0];
  },

  initImageTextMediaSwiper($contentSlider, contentSwiper) {
    const mediaSliderEl = getImageTextFrame($contentSlider)
      .find(selector.imageTextMediaSlider)[0];

    if (!mediaSliderEl) {
      return;
    }

    const mediaSwiper = new Swiper(mediaSliderEl, imageTextMediaConfig);

    contentSwiper.controller.control = mediaSwiper;
    mediaSwiper.controller.control = contentSwiper;
    contentSwiper.mediaSwiper = mediaSwiper;
  },

  getNavigationElements(navElement, sliderType) {
    if (sliderType === 'image-text') {
      return {
        nextEl: $(navElement).find(selector.imageTextNext)[0],
        prevEl: $(navElement).find(selector.imageTextPrev)[0],
        disabledClass: 'is-disabled',
      };
    }

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
