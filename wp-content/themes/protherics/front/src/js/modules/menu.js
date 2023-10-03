const selector = {
  hamburger: '.js-menu-button',
  menuItemWithSubmenu: '.js-menu-btn',
  header: '.l-header',
  menuLink: '.c-main-nav-list__link',
  menuItem: '.c-main-nav-list__item',
  searchBar: '.js-search-bar',
  closeSubmenuButton: '.js-close-submenu',
}

const state = {
  isOpen: 'is-open',
}

const Menu = {
  header: null,
  hamburger: null,
  searchBar: null,
  itemsWithSubmenu: [],
  closeSubmenuButtons: [],
  handleCloseSearchBar: () => {
    Menu.searchBar.classList.remove(state.isOpen);
  },
  handleToggleMenu: () => {
    Menu.header.classList.toggle(state.isOpen);
  },
  handleToggleSubmenu: (e) => {
    const menuLink = e.currentTarget;
    const menuItem = menuLink.closest(selector.menuItem);

    Menu.handleCloseSearchBar();

    menuItem.classList.toggle(state.isOpen);
  },
  handleCloseSubmenu: (e) => {
    const closeButton = e.currentTarget;
    const menuItem = closeButton.closest(selector.menuItem);

    menuItem.classList.remove(state.isOpen)
  },
  addEventListeners: () => {
    Menu.hamburger.addEventListener('click', Menu.handleToggleMenu);

    Menu.itemsWithSubmenu.forEach(itemWithSubmenu => {
        itemWithSubmenu.addEventListener('click', Menu.handleToggleSubmenu);
    });

    Menu.closeSubmenuButtons.length && Menu.closeSubmenuButtons.forEach(closeSubmenuButton => {
      closeSubmenuButton.addEventListener('click', Menu.handleCloseSubmenu);
    });
  },
  init: () => {
    Menu.header = document.querySelector(selector.header);
    Menu.hamburger = document.querySelector(selector.hamburger);
    Menu.itemsWithSubmenu = document.querySelectorAll(selector.menuItemWithSubmenu);
    Menu.searchBar = document.querySelector(selector.searchBar);
    Menu.closeSubmenuButtons = document.querySelectorAll(selector.closeSubmenuButton);

    const shouldContinue = !!Menu.header && !!Menu.hamburger && !!Menu.itemsWithSubmenu.length && !!Menu.searchBar;

    if (!shouldContinue) return;

    Menu.addEventListeners();
  }
}

Menu.init();
