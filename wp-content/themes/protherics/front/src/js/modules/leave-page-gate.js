const selector = {
  modal: '.js-gate-modal',
  modalCancel: '.js-gate-cancel',
  modalConfirm: '.js-gate-confirm',
}

const state = {
  isOpen: 'is-open'
}

const LeavePageGate = {
  init() {
    LeavePageGate.links = document.querySelectorAll('a');
    LeavePageGate.modal = document.querySelector('.js-gate-modal');

    if(!!LeavePageGate.links.length && LeavePageGate.modal) {
      LeavePageGate.modalCancel = LeavePageGate.modal.querySelector('.js-gate-cancel');
      LeavePageGate.modalConfirm = LeavePageGate.modal.querySelector('.js-gate-confirm');

      LeavePageGate.bindEvents();
    }
  },

  bindEvents() {
    LeavePageGate.links.forEach((link) => link.addEventListener('click', LeavePageGate.handleClick));

    LeavePageGate.modalCancel.addEventListener('click', LeavePageGate.hideModal);
    LeavePageGate.modalConfirm.addEventListener('click', LeavePageGate.leavePage);
  },
  handleClick(e) {
    LeavePageGate.currentUrl = e.currentTarget.getAttribute('href');

    const regex = new RegExp(`${prothericsObj.siteUrl}`);

        // check if the link is relative or to your domain
      if (! /^https?:\/\/./.test(LeavePageGate.currentUrl) || regex.test(LeavePageGate.currentUrl)) return;

      e.preventDefault();

      LeavePageGate.showModal();
  },
  showModal() {
    LeavePageGate.modal.classList.add(state.isOpen);
  },

  hideModal() {
    LeavePageGate.modal.classList.remove(state.isOpen);
  },

  leavePage(e) {
    window.location = LeavePageGate.currentUrl;
  }
}

LeavePageGate.init();
