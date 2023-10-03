import Cookies from 'js-cookie';

const selector = {
  modal: '.js-regions-modal',
  closeModalBtn: '.js-regions-modal-close',
  acceptModalBtn: '.js-regions-modal-accept',
  openModalBtn: '.js-open-regions-modal',
  regionSelect: '.js-regions-select',
  regionName: '.js-region-name',
};

const state = {
  isOpen: 'is-open',
};

const cookieConfiguration = {
  prothericsRegion: {
    name: 'protherics_region',
    expires: 0,
    defaultValue: 'all',
  },
  prothericsRegionName: {
    name: 'protherics_region_name',
    expires: 0,
    defaultValue: 'global',
  },
};

const RegionsModal = {
  modal: null,
  closeModalBtn: null,
  acceptModalBtn: null,
  openModalBtns: [],
  regionName: null,
  init: () => {
    RegionsModal.modal = document.querySelector(selector.modal);
    RegionsModal.closeModalBtn = document.querySelector(selector.closeModalBtn);
    RegionsModal.acceptModalBtn = document.querySelector(selector.acceptModalBtn);
    RegionsModal.openModalBtns = document.querySelectorAll(selector.openModalBtn);
    RegionsModal.regionName = document.querySelector(selector.regionName);

    const shouldContinue = !!RegionsModal.modal && !!RegionsModal.closeModalBtn && !!RegionsModal.acceptModalBtn;

    if (!shouldContinue) return;

    if (!RegionsModal.getCookie(['prothericsRegion'])) {
      RegionsModal.showModal();
    }

    RegionsModal.setRegionName();
    RegionsModal.addEventListeners();
  },
  addEventListeners: () => {
    RegionsModal.closeModalBtn.addEventListener('click', RegionsModal.handleCloseModal);
    RegionsModal.acceptModalBtn.addEventListener('click', RegionsModal.handleAcceptModal);

    if (RegionsModal.openModalBtns.length) {
      RegionsModal.openModalBtns.forEach(btn => btn.addEventListener('click', RegionsModal.showModal));
    }
  },
  handleCloseModal: () => {
    const cookieValue = cookieConfiguration['prothericsRegion'].defaultValue;
    const regionNameCookie = cookieConfiguration['prothericsRegionName'].defaultValue;

    RegionsModal.saveCookie(cookieValue, 'prothericsRegion');
    RegionsModal.saveCookie(regionNameCookie, 'prothericsRegionName');
    RegionsModal.setRegionAttribute();
    RegionsModal.setRegionName();
    RegionsModal.hideModal();
    RegionsModal.refreshPage();
  },
  handleAcceptModal: async () => {
    let cookieValue = cookieConfiguration['prothericsRegion'].defaultValue;
    let regionNameCookie = cookieConfiguration['prothericsRegionName'].defaultValue;

    const regionSelect = document.querySelector(selector.regionSelect);
    const value = regionSelect.value;

    if (value) {
      if (isNaN(value)) {
        // is url - redirect
        window.location = value;
        return;
      } else {
        // is region id
        regionNameCookie = regionSelect.textContent;
        cookieValue = value;
      }
    }

    RegionsModal.saveCookie(cookieValue, 'prothericsRegion');
    RegionsModal.saveCookie(regionNameCookie, 'prothericsRegionName');
    RegionsModal.setRegionAttribute();
    RegionsModal.setRegionName();
    RegionsModal.hideModal();
    RegionsModal.refreshPage();
  },
  showModal: () => {
    RegionsModal.modal.classList.add(state.isOpen);
  },
  hideModal: () => {
    RegionsModal.modal.classList.remove(state.isOpen);
  },
  getCookie: name => {
    return Cookies.get(cookieConfiguration[name].name);
  },
  saveCookie: (cookieVal, name) => {
    let config = {};

    if (cookieConfiguration[name].expires) {
      config.expires = cookieConfiguration[name].expires;
    }

    Cookies.set(cookieConfiguration[name].name, cookieVal, config);
  },
  setRegionAttribute: () => {
    const regionId = RegionsModal.getCookie(['prothericsRegion']);

    if (regionId && regionId !== cookieConfiguration.defaultValue) {
      document.querySelector('body').setAttribute('region-id', regionId);
    }
  },
  setRegionName: () => {
    if (!RegionsModal.regionName) return;

    RegionsModal.regionName.textContent =
      RegionsModal.getCookie(['prothericsRegionName']) || cookieConfiguration['prothericsRegionName'].defaultValue;
  },
  refreshPage: () => {
    window.location.reload();
  }
};

RegionsModal.init();
