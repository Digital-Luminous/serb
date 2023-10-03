const selector = {
  searchBtn: '.js-toggle-search',
  searchClear: '.js-search-clear',
  searchBar: '.js-search-bar',
  searchInput: '.js-search-input',
  searchWrapper: '.js-search-wrapper',
  openMenuItem: '.c-main-nav-list__item.is-open',
}

const state = {
  isOpen: 'is-open',
  isFilled: 'is-filled',
}

const Search = {
  searchBtn: null,
  clearBtn: null,
  searchBar: null,
  searchWrapper: null,
  input: null,
  handleClearInput: () => {
    Search.input.value = '';
    Search.searchWrapper.classList.remove(state.isFilled);
  },
  handleTyping: () => {
    const value = Search.input.value;

    if (value.length) {
      Search.searchWrapper.classList.add(state.isFilled);
    } else {
      Search.searchWrapper.classList.remove(state.isFilled);
    }
  },
  handleCloseMenuItems: () => {
    const oppenedMenuItems = document.querySelectorAll(selector.openMenuItem);

    oppenedMenuItems.forEach(openedMenuItem => {
      openedMenuItem.classList.remove(state.isOpen);
    });
  },
  handleToggleSearch: () => {
    Search.handleCloseMenuItems();

    Search.searchBar.classList.toggle(state.isOpen);
  },
  addEventListeners: () => {
    Search.searchBtn.addEventListener('click', Search.handleToggleSearch);
    Search.input.addEventListener('keyup', Search.handleTyping);
    Search.clearBtn.addEventListener('click', Search.handleClearInput);
  },
  init: () => {
    Search.searchBtn = document.querySelector(selector.searchBtn);
    Search.searchBar = document.querySelector(selector.searchBar);
    Search.searchWrapper = document.querySelector(selector.searchWrapper);
    Search.input = document.querySelector(selector.searchInput);
    Search.clearBtn = document.querySelector(selector.searchClear);

    const shouldContinue = !!Search.searchBtn && !!Search.searchBar && !!Search.input && !!Search.clearBtn;

    if (!shouldContinue) return;

    Search.addEventListeners();
  }
}

Search.init();
