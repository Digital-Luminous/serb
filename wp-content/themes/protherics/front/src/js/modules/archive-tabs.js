const selector = {
  tabs: '.js-tabs',
  tabsNavList: '.js-tabs-list',
  tabsTrigger: '.js-tabs-trigger',
  archiveList: '.js-archive-list',
};

const element = {
  tabs: document.querySelector(selector.tabs),
  tabsNavList: document.querySelector(selector.tabsNavList),
  tabsTrigger: document.querySelectorAll(selector.tabsTrigger),
  archiveList: document.querySelector(selector.archiveList),
};

const state = {
  isActive: 'is-active',
};

const Tabs = {
  activeTabYear: null,
  tabsNavList: null,
  tabsTriggers: [],
  allNews: [],
  fetchNews: async () => {
    try {
      const apiURL = element.tabs.dataset.api;
      const response = await fetch(apiURL);
      Tabs.allNews = await response.json();

      const cookieString = document.cookie;

      const cookies = cookieString.replace(/^\*|\*$/g, '').split(';');

      const regionCookie = cookies
        .find(cookie => cookie.trim().startsWith('protherics_region='))
        ?.split('=')[1];

        for (const year in Tabs.allNews) {
          Tabs.allNews[year] = Tabs.allNews[year].filter(news => {

            if (news?.regions === null) return true;

            if (!regionCookie) return true;

            const regionId = parseInt(regionCookie);

            return news.regions.includes(regionId);
          });
        }

      Tabs.renderNavList();
      Tabs.renderNewsList();
    } catch (error) {
      console.error(error);
    }
  },
  renderNavList: () => {
    const newsYears = Object.keys(Tabs.allNews).reverse();
    const navListItems = newsYears
      .map(
        (year, yearIndex) => `
      <li class="c-tabs-nav-list__item">
        <button class="c-pill ui-font-weight--semibold${
          yearIndex === 0 ? ' is-active' : ''
        } js-tabs-trigger" data-year="${year}">${year}</button>
      </li>
      `
      )
      .join('');

    element.tabsNavList.innerHTML = navListItems;

    Tabs.activeTabYear = newsYears[0];
    Tabs.handleTabTriggersEvent();
  },
  handleTabTriggersEvent: () => {
    Tabs.tabsTriggers = document.querySelectorAll(selector.tabsTrigger);
    Tabs.tabsTriggers.forEach((trigger) =>
      trigger.addEventListener('click', Tabs.getTargetTriggerYear)
    );
  },
  handleTabTriggerClasses: (e) => {
    Tabs.tabsTriggers.forEach((trigger) =>
      trigger.classList.remove(state.isActive)
    );
    e.target.classList.add(state.isActive);
  },
  getTargetTriggerYear: (e) => {
    const targetYear = e.target.dataset.year;

    if (Tabs.activeTabYear === targetYear) return;

    Tabs.activeTabYear = targetYear;
    Tabs.renderNewsList();
    Tabs.handleTabTriggerClasses(e);
  },
  renderNewsList: () => {
    const targetYearNews = Tabs.allNews[Tabs.activeTabYear];
    const newsListHTML = targetYearNews
      .map(
        ({ date, title, link, label }) => `
      <li class="c-news-archives__list-item">
        <div class="c-news-archive-teaser">
          <div class="c-news-archive-teaser__meta">
            <span class="c-news-archive-teaser__date t-size-14">${date}</span>
          </div>
          <div class="c-news-archive-teaser__content">
            <h3 class="c-news-archive-teaser__title t-size-18 t-size-20--desktop ui-font-weight--semibold">${title}</h3>
            <div class="c-news-archive-teaser__action">
              <a href="${link}" class="c-btn c-btn--arrowed c-btn--tertiary">${label}</a>
            </div>
          </div>
        </div>
      </li>`
      )
      .join('');

    element.archiveList.innerHTML = newsListHTML;
  },
  init: () => {
    Tabs.tabsNavList = element.tabsNavList;
    if(!element.tabs) return;
    Tabs.fetchNews();
  },
};

Tabs.init();
