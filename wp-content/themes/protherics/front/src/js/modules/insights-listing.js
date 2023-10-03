import $ from 'jquery';
import { debounceEvent } from '../utils';

const selector = {
  searchInput: '.js-insights-search-input',
  insightsList: '.js-insights-list',
  paginationList: '.js-insights-pagination-list',
  paginationListItem:
    '.js-insights-pagination-list .c-pagination__link[data-page]',
  paginationNav: '.js-insights-pagination-nav',
  insightsListingWrapper: '.js-insights-list-wrapper',
  header: '.c-header'
};

const element = {
  searchInput: document.querySelector(selector.searchInput),
  paginationList: document.querySelector(selector.paginationList),
  insightsList: document.querySelector(selector.insightsList),
  insightsListingWrapper: document.querySelector(
    selector.insightsListingWrapper
  ),
};

const params = {
  action: 'get_insights',
  url: prothericsObj.ajaxurl,
  fetchMethod: 'POST',
  next: 'next',
  prev: 'prev',
  scrollSpeed: 300
};

const state = {
  isHidden: 'is-hidden',
  isActive: 'is-active',
};

const Listing = {
  searchInput: null,
  insightsList: null,
  currentPage: 1,
  searchTerm: '',
  currentPagePosts: [],
  paginationNavControls: null,
  paginationElements: null,

  getPosts: async () => {
    try {
      const URLParams = new URLSearchParams();
      const insightsParams = {
        action: params.action,
        search: Listing.searchTerm,
        page: Listing.currentPage,
      };

      for (let key in insightsParams) {
        URLParams.append(key, insightsParams[key]);
      }

      const response = await fetch(params.url, {
        method: params.fetchMethod,
        body: URLParams,
      });

      const { info, posts } = await response.json();

      Listing.currentPagePosts = posts || [];
      Listing.info = info;

      if (Listing.currentPagePosts.length) {
        Listing.renderPosts();
      } else {
        Listing.renderNotFoundInfo();
      }

      if (Listing.currentPage === 1) {
        Listing.renderPagination();
      }
    } catch (error) {
      console.error(error);
    }
  },
  handlePaginationNavClick: () => {
    Listing.paginationNavControls.forEach((nav) =>
      nav.addEventListener('click', () => {
        const paginateAction = nav.dataset.paginateAction;
        const nextPage =
          Listing.currentPage < Listing.info.max_pages &&
          paginateAction === params.next;
        const prevPage =
          Listing.currentPage > 1 && paginateAction === params.prev;

        if (!nextPage && !prevPage) return;

        if (nextPage) {
          Listing.currentPage++;
        } else if (prevPage) {
          Listing.currentPage--;
        }
        Listing.handleScrollAndRender();
      })
    );
  },
  handlePaginationClick: () => {
    Listing.paginationElements.forEach((pagination) => {
      pagination.addEventListener('click', () => {
        if (Listing.currentPage === +pagination.dataset.page) return;

        Listing.currentPage = +pagination.dataset?.page;

        Listing.handleScrollAndRender();
      });
    });
  },
  handleScrollAndRender: () => {
    $([document.documentElement, document.body]).animate({
      scrollTop: $(Listing.searchInput).offset().top - + $(selector.header).outerHeight()
    }, params.scrollSpeed, () => {
      Listing.handlePaginationClass();
      Listing.getPosts();
    });
  },
  handlePaginationEvents: () => {
    Listing.handlePaginationNavClick();
    Listing.handlePaginationClick();
  },
  renderPosts: () => {
    const postsHTML = Listing.currentPagePosts
      .map((post) => {
        const { author, date, excerpt, label, terms, title, img, link } = post;
        return `
        <li class="c-insights-list__item ui-bg--grey-1-50">
        <article class="c-news-box c-news-box--taller">
          <div class="c-news-box__header">
              <div class="c-news-box__tags-wrapper">
                <div class="c-tags">
                  <ul class="c-tags__list t-size-14 ui-color--black-1">
                      ${terms
                        .map(
                          ({ name: termName }) => `
                      <li class="c-tags__item ui-bg--white-1">
                        ${termName}
                      </li>
                    `
                        )
                        .join('')}
                  </ul>
                </div>
              </div>
            <div class="c-news-box__media">
              <figure class="c-news-box__figure">
                <img class="c-news-box__image" src="${img || ''}" >
              </figure>
            </div>
          </div>
          <div class="news-box__meta t-size-14 ui-color--black-1">
            <div class="c-news-box__author">${author}</div>
            <div class="c-news-box__date">
              ${date}
            </div>
          </div>
          <div class="c-news-box__title t-size-22 t-size-24--desktop ui-color--purple-1 ui-font-weight--semibold">
            ${title}
          </div>
          <div class="c-news-box__text t-size-18 t-size-20--desktop ui-color--black-2">
            ${excerpt}
          </div>
          <div class="c-news-box__actions">
            <a class="c-btn c-btn--secondary c-btn--arrowed" href="${
              link
            }">
              ${label}
            </a>
          </div>
        </article>
      </li>
      `;
      })
      .join('');

    Listing.insightsList.innerHTML = postsHTML;
  },
  renderNotFoundInfo: () => {
    Listing.insightsList.innerHTML = `<li class="c-insights-list__not-found t-size-18 t-size-20--desktop ui-color--black-1">Insights by term: <span class="ui-font-weight--semibold">"${Listing.searchTerm}"</span> not found. Try again.</li>`;
  },
  handlePaginationClass: () => {
    const currentPage = Listing.paginationElements?.find(
      (pagination) => pagination.dataset.page == Listing.currentPage
    );
    Listing.paginationElements.forEach((pagionation) =>
      pagionation.classList.remove(state.isActive)
    );
    currentPage.classList.add(state.isActive);
  },
  renderPagination: () => {
    if (!Listing.currentPagePosts) return;

    const previusPageTrigger = `
      <li class="c-pagination__item c-pagination__item--nav c-pagination__item--prev"><button class="c-pagination__link js-insights-pagination-nav ui-color--purple-1" data-paginate-action="${params.prev}" aria-label="Previous page">Previous</button></li>`;
    const nextPageTrigger = `
      <li class="c-pagination__item c-pagination__item--nav c-pagination__item--next"><button class="c-pagination__link js-insights-pagination-nav ui-color--purple-1" data-paginate-action="${params.next}" aria-label="Next page">Next</button></li>`;

    const paginationItems = new Array(Listing.info.max_pages)
      .fill()
      .map((_, paginationIdx) => {
        const pageNumber = paginationIdx + 1;
        const currentPage = pageNumber === Listing.currentPage;
        const lastPage = pageNumber < Listing.info.max_pages;

        return `
          <li class="c-pagination__item c-pagination__item--number">
            <button class="c-pagination__link ui-color--purple-1 ${
              currentPage ? ' is-active' : ''
            }" data-page="${pageNumber}" aria-label="Go to page ${pageNumber}">
              <span class="c-pagination__number">${pageNumber}</span>${
          lastPage ? ', ' : ''
        }
            </button>
          </li>`;
      })
      .join('');

    const paginationHTML = `
      ${previusPageTrigger}
      ${paginationItems}
      ${nextPageTrigger}
    `;

    if (Listing.info.max_pages <= 1) {
      element.paginationList.innerHTML = '';
    } else {
      element.paginationList.innerHTML = paginationHTML;
    }

    Listing.paginationNavControls = element.paginationList.querySelectorAll(
      selector.paginationNav
    );
    Listing.paginationElements = [...element.paginationList.querySelectorAll(selector.paginationListItem)];

    Listing.handlePaginationEvents();
  },
  addEventListeners: () => {
    Listing.searchInput.addEventListener(
      'keyup',
      debounceEvent((e) => {
        if (Listing.currentPage > 1) Listing.currentPage = 1;

        Listing.searchTerm = e.target.value;
        Listing.getPosts();
      })
    );
  },
  init: () => {
    Listing.searchInput = element.searchInput;
    Listing.pagination = element.pagination;
    Listing.insightsList = element.insightsList;

    if (!Listing.searchInput && !Listing.insightsList) return;

    Listing.getPosts();
    Listing.addEventListeners();
  },
};

Listing.init();
