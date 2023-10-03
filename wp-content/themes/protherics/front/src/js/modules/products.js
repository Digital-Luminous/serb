import { debounceEvent } from '../utils';
import { Scroll } from './scroll.js';

const selector = {
  productsSection: '.js-products',
  productsContainer: '.js-products-container',
  productsSearch: '.js-products-search',
  searchWrapper: '.js-search-wrapper',
  productsAreaSelect: '.js-products-area-select',
  productsLocationSelect: '.js-products-location-select',
  productUrlsOpenPopup: '.js-product-urls-popup',
  productTrademarksOpenPopup: '.js-product-trademarks-popup',
  urlsPopup: '.js-urls-popup',
  trademarksPopup: '.js-trademarks-popup',
  productBox: '.c-product-box',
  closePopupBtn: '.js-close-popup',
  popup: '.c-popup',
  searchClear: '.js-products-search-clear',
};

const state = {
  isVisible: 'is-visible',
  isFilled: 'is-filled',
};

const param = {
  onlyPlusSignPattern: /[+]/g,
};

const Products = {
  section: null,
  productsContainer: null,
  productsURL: '',
  allProducts: [],
  searchWrapper: null,
  search: null,
  clearSearchBtn: null,
  areaSelect: null,
  locationSelect: null,
  searchPhrase: '',
  filterArea: '',
  filterLocation: '',
  filterType: {
    filterArea: 'filterArea',
    filterLocation: 'filterLocation'
  },
  popupType: {
    urls: 'urls',
    trademarks: 'trademarks',
  },
  handleFilterValueChange: (e, filterValue) => {
    const value = e.currentTarget.value;

    Products[filterValue] = decodeURI(value).replace(param.onlyPlusSignPattern, ' ');

    Products.searchProducts();
  },
  handleSearchPhraseChange: (e) => {
    Products.searchPhrase = e.target.value;

    if (Products.searchPhrase.length) {
      Products.searchWrapper.classList.add(state.isFilled);
    } else {
      Products.searchWrapper.classList.remove(state.isFilled);
    }

    Products.searchProducts();
  },
  handleClearInput: () => {
    Products.search.value = '';
    Products.searchPhrase = '';

    Products.searchWrapper.classList.remove(state.isFilled);

    Products.searchProducts();
  },
  handleOpenPopup: (e, type) => {
    const popups = Products.productsContainer.querySelectorAll(selector.popup);

    popups.forEach(popup => popup.classList.remove(state.isVisible));

    const btn = e.currentTarget;

    const popup = btn
      .closest(selector.productBox)
      ?.querySelector(type);

    popup?.classList.add(state.isVisible);
  },
  handleClosePopup: (e) => {
    const btn = e.currentTarget;

    const popup = btn.closest(selector.popup);

    popup?.classList.remove(state.isVisible);
  },
  searchProducts: () => {
    const filteredProducts = JSON.parse(JSON.stringify(Products.allProducts));

    for (const category in filteredProducts) {
      if (Products.searchPhrase) {
        const filteredByPhrase = filteredProducts[
          `${category}`
        ].products.filter((item) =>
          item?.title
            .toLowerCase()
            .includes(Products.searchPhrase.toLowerCase())
        );

        filteredProducts[`${category}`].products = filteredByPhrase;
      }

      if (Products.filterArea) {
        if (category !== Products.filterArea) {
          delete filteredProducts[category];
        }
      }

      if (Products.filterLocation) {
        filteredProducts[`${category}`]?.products.forEach((product) => {
          const shouldRemove = !product?.locations.filter(
            (location) => location?.name === Products.filterLocation
          ).length;

          if (shouldRemove) {
            const filteredByLocation = filteredProducts[
              `${category}`
            ].products.filter((item) => item !== product);

            filteredProducts[`${category}`].products = filteredByLocation;
          }
        });
      }
    }

    Products.productsContainer.innerHTML =
      Products.renderProductsList(filteredProducts);

    Products.handleAddListeners();
  },
  renderPopupContent: (data, type) => {
    switch (type) {
      case Products.popupType.urls:
        return `
          <h5>Choose your country website:</h5>
            <ul>
              ${data
                .map(({ url }) => {
                  return `
                    <li>
                      <a href="${url}" target="_blank" rel="nofollow">${url}</a>
                    </li>`;
                })
                .join('')}
            </ul>`;
      case Products.popupType.trademarks:
        return `
          ${data.title ? `<h5>${data.title}</h5>` : ''}
          ${data.content ? data.content : ''}`;
    }
  },
  renderPopup: (data, type) => {
    let popup = `
      <div class="c-popup js-${type}-popup ui-bg--white-1">
        <button class="c-popup__close js-close-popup">
          <svg width="1em" height="1em" viewBox="0 0 3.5939147 3.5939226" xmlns="http://www.w3.org/2000/svg">
            <title>Close</title><path d="m.206.206 3.197 3.197M.198 3.395 3.395.198" stroke="currentColor" stroke-width=".4" stroke-linecap="round"/>
          </svg>
          <span class="sr-only">
            Close
          </span>
        </button>
        <div class="c-popup__container">
          <div class="c-cms-popup">
            ${Products.renderPopupContent(data, type)}
          </div>
        </div>
      </div>`;

    return popup;
  },
  renderSingleProduct: (product) => {
    const {
      title,
      trademark,
      btnLabel,
      buttonUrls,
      diseadeArea,
      img,
      imgAlt,
      locations,
      productComposition,
    } = product;

    return `
      <li class="c-products-list__item ui-bg--white-1">
        <div class="c-product-box">
          <header class="c-product-box__header">
            <h4 class="c-product-box__title t-size-22 t-size-24--desktop ui-font-weight--semibold">${title}</h4>
            ${
              !!trademark.title || !!trademark.content
                ? `<button class="c-product-box__btn t-size-14 t-size-16--desktop js-product-trademarks-popup">Tradenames by country</button>`
                : ''
            }
          </header>
          <figure class="c-product-box__figure">
            ${
              img
                ? `<img class="c-product-box__image" src="${img}" alt="${imgAlt || title}">`
                : ''
            }
          </figure>
          <ul class="c-product-box__list t-size-14 t-size-16--desktop">
            ${productComposition
              .map(
                ({ item }) => `
              <li class="c-product-box__item">
                ${item}
              </li>
            `
              )
              .join('')}
          </ul>
          <div class="c-product-box__tags">
            <div class="c-tags">
              <ul class="c-tags__list">
                ${diseadeArea ? diseadeArea
                  .map(
                    ({ name, color }) => `
                  <li class="c-tags__item t-size-14 ui-color--black-1 ui-bg--white-1"
                  ${color ? `style="color: ${color};"` : ''}
                  >
                    ${name}
                  </li>
                `
                  )
                  .join('') : ''}
                ${locations ? locations
                  .map(
                    ({ name }) => `
                  <li class="c-tags__item t-size-14 ui-color--black-1 ui-bg--white-1">
                    ${name}
                  </li>
                `
                  )
                  .join('') : ''}
              </ul>
            </div>
          </div>
          <div class="c-product-box__actions">
            ${
              buttonUrls.length > 1
                ? `<button class="c-btn c-btn--tertiary c-btn--arrowed js-product-urls-popup">${btnLabel}</button>`
                : ''
            }
            ${
              buttonUrls.length === 1
                ? `<a href="${buttonUrls[0].url}" class="c-btn c-btn--tertiary c-btn--arrowed" target="_blank" rel="nofollow">${btnLabel}</a>`
                : ''
            }
          </div>
          <div class="c-product-box__popup">
          ${
            buttonUrls.length > 1
              ? Products.renderPopup(buttonUrls, Products.popupType.urls)
              : ''
          }
          ${
            !!trademark.title || !!trademark.content
              ? Products.renderPopup(trademark, Products.popupType.trademarks)
              : ''
          }
          </div>
        </div>
      </li>
    `;
  },
  renderProductsList: (productsList) => {
    const categories = [];

    for (const category in productsList) {
      if (productsList[`${category}`].products.length) {
        categories.push(category);
      }
    }

    return categories
      .map((category) => {
        const { color, products } = productsList[`${category}`];

        return (
          `
      <div class="c-products-category s-medium-bottom ui-bg--light-grey-2 js-products-category">
        <header class="c-products-category__header">
          <h3 class="c-products-category__title t-size-22 t-size-24--desktop ui-font-weight--semibold"
          ${
            color
              ? `style="color: ${color};`
              : ''
          }">
            ${category}
          </h3>
        </header>
        <div class="c-products-category__list">
          <ul class="c-products-list">
            ` +
          products
            .map((product) => Products.renderSingleProduct(product))
            .join('') +
          `
          </ul>
				</div>
        <footer class="c-products-category__footer">
					<a class="c-back-top t-size-14 ui-color--purple-1 js-scroll" href="#products">back to top</a>
				</footer>
      </div>
      `
        );
      })
      .join('');
  },
  fetchProducts: async () => {
    try {
      const response = await fetch(Products.productsURL);
      Products.allProducts = await response.json();

      Products.productsContainer.innerHTML = Products.renderProductsList(
        Products.allProducts
      );

      Products.handleAddListeners();
    } catch (error) {
      console.error(error);
    }
  },
  handleAddListeners: () => {
    const closePopupBtns = Products.productsContainer.querySelectorAll(selector.closePopupBtn);
    const urlsPopups = Products.productsContainer.querySelectorAll(selector.productUrlsOpenPopup);
    const trademarksPopups = Products.productsContainer.querySelectorAll(
      selector.productTrademarksOpenPopup
    );

    Scroll.init();

    closePopupBtns.forEach((btn) => {
      btn.addEventListener('click', Products.handleClosePopup);
    });

    urlsPopups.forEach((btn) => {
      btn.addEventListener('click', (e) => Products.handleOpenPopup(e, selector.urlsPopup));
    });

    trademarksPopups.forEach((btn) => {
      btn.addEventListener('click', (e) => Products.handleOpenPopup(e, selector.trademarksPopup));
    });
  },
  addEventListeners: () => {
    Products.search.addEventListener(
      'input',
      debounceEvent((e) => Products.handleSearchPhraseChange(e))
    );
    Products.areaSelect.addEventListener(
      'change',
      (e) => Products.handleFilterValueChange(e, Products.filterType.filterArea)
    );
    Products.locationSelect.addEventListener(
      'change',
      (e) => Products.handleFilterValueChange(e, Products.filterType.filterLocation)
    );
    Products.clearSearchBtn.addEventListener(
      'click',
      Products.handleClearInput
    );
  },
  init: () => {
    Products.section = document.querySelector(selector.productsSection);
    Products.productsContainer = Products.section?.querySelector(
      selector.productsContainer
    );
    Products.search = Products.section?.querySelector(selector.productsSearch);
    Products.searchWrapper = Products.search?.closest(selector.searchWrapper);
    Products.clearSearchBtn = Products.section?.querySelector(selector.searchClear);
    Products.areaSelect = Products.section?.querySelector(selector.productsAreaSelect);
    Products.locationSelect = Products.section?.querySelector(
      selector.productsLocationSelect
    );
    Products.productsURL = Products.section?.dataset.url;

    const shouldContinue =
      !!Products.section &&
      !!Products.productsContainer &&
      !!Products.productsURL.length &&
      !!Products.search &&
      !!Products.searchWrapper &&
      !!Products.clearSearchBtn &&
      !!Products.areaSelect &&
      !!Products.locationSelect;

    if (!shouldContinue) return;

    Products.fetchProducts();
    Products.addEventListeners();
  },
};

Products.init();
