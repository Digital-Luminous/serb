import Cookies from 'js-cookie';

const elSelector = {
  cookieBar: '.js-cookie-bar',
  acceptBtn: '.js-cookie-accept',
}

const state = {
  isHidden: 'is-hidden',
  isVisible: 'is-visible',
}

const configuration = {
  expires: 14,
}

const element = {
  body: document.querySelector(elSelector.body),
  cookieBar: document.querySelector(elSelector.cookieBar),
  acceptBtn: document.querySelector(elSelector.acceptBtn),
}

const Cookie = {
  init: () => {
    if (!element.cookieBar) return;

    element.acceptBtn?.addEventListener('click', e => {
      element.cookieBar.classList.remove(state.isVisible);
      Cookies.set('accepted', true, { expires: configuration.expires });
    })

    if (!Cookies.get('accepted')) {
      element.cookieBar.classList.add(state.isVisible);
    }
  },
}

Cookie.init()
