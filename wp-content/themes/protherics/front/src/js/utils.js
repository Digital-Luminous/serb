const BREAKPOINTS = {
  tablet: 1024,
  phone: 767,
}

const body = document.querySelector('body');

let topScroll = 0;
let isScrollDisabled = false;
const disabledScrollClass = 'scroll-disabled';

const moveCursorToEnd = el => {
  if (typeof el.selectionStart === 'number') {
    el.selectionStart = el.selectionEnd = el.value.length
  } else if (typeof el.createTextRange !== 'undefined') {
    el.focus()
    let range = el.createTextRange()
    range.collapse(false)
    range.select()
  }
}

// Screen resolution checkers
const isDesktop = () => window.matchMedia(`(min-width: ${BREAKPOINTS.tablet + 1}px)`).matches
const isTablet = () => window.matchMedia(`(max-width: ${BREAKPOINTS.tablet}px)`).matches
const isMobile = () => window.matchMedia(`(max-width: ${BREAKPOINTS.phone}px)`).matches


const isExplorer = () => {
  function GetIEVersion () {
    var sAgent = window.navigator.userAgent
    var Idx = sAgent.indexOf('MSIE')

    if (Idx > 0) {
      return parseInt(sAgent.substring(Idx + 5, sAgent.indexOf('.', Idx)))
      // eslint-disable-next-line
    } else if (!!navigator.userAgent.match(/Trident\/7\./)) {
      return 11
    } else {
      return 0
    }
  }

  if (GetIEVersion() > 0) {
    return true
  } else {
    return false
  }
}

const getScrollbarWidth = () => {
  return window.innerWidth - document.documentElement.clientWidth;
}

const isElementInViewport = element => {
  const rect = element.getBoundingClientRect();
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <=
      (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}

const debounceEvent = (callback, wait = 250) => {
  let timer;
  return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => callback(...args), wait);
  };
};

const disableScroll = () => {
		if (!isScrollDisabled) {
			topScroll = document.documentElement.scrollTop;
			body.style.top = `-${topScroll}px`;
			body.classList.add(disabledScrollClass);
			isScrollDisabled = true;
		}
	};

	const enableScroll = () => {
		body.removeAttribute('style');
		body.classList.remove(disabledScrollClass);
		document.documentElement.scrollTop = topScroll;
		isScrollDisabled = false;
	};

module.exports = {
  BREAKPOINTS,
  moveCursorToEnd,
  isElementInViewport,
  isDesktop,
  isTablet,
  isMobile,
  isExplorer,
  debounceEvent,
  getScrollbarWidth,
  enableScroll,
  disableScroll
}
