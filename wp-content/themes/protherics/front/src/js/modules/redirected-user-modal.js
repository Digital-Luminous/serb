const selector = {
  modal: '.js-redirected-user-modal',
  closeModalBtn: '.js-gate-close'
}

const state = {
  isOpen: 'is-open'
}

const RedirectedUserModal = {
  modal: null,
  closeModalBtn: null,
  url: '',
  urls: [],
  init: () => {
    RedirectedUserModal.modal = document.querySelector(selector.modal);
    RedirectedUserModal.closeModalBtn = document.querySelector(selector.closeModalBtn);
    RedirectedUserModal.url = RedirectedUserModal.modal?.dataset.url;

    const shouldContinue = !!RedirectedUserModal.modal && !!RedirectedUserModal.closeModalBtn && !!RedirectedUserModal.url.length

    if (!shouldContinue) return;

    RedirectedUserModal.addEventListeners();
    RedirectedUserModal.fetchUrls();
  },
  fetchUrls: async () => {
    try {
      const response = await fetch(RedirectedUserModal.url);

      if (!response.ok) return;

      RedirectedUserModal.urls = await response.json();

      if (!RedirectedUserModal.urls.length) return;

      RedirectedUserModal.handleCheckUrls();
    } catch (error) {
      console.error(error);
    }
  },
  addEventListeners: () => {
    RedirectedUserModal.closeModalBtn.addEventListener('click', RedirectedUserModal.handleCloseModal);
  },
  handleCloseModal: () => {
    RedirectedUserModal.modal.classList.remove(state.isOpen);
  },
  handleShowModal: () => {
    RedirectedUserModal.modal.classList.add(state.isOpen);
  },
  handleCheckUrls: () => {
    let shouldModalBeDisplayed = false;

    RedirectedUserModal.urls.forEach(url => {
      shouldModalBeDisplayed = document.referrer.indexOf(url.url) >= 0 ? true : shouldModalBeDisplayed;
    });

    if (!shouldModalBeDisplayed) return;

    RedirectedUserModal.handleShowModal();
  }
}

RedirectedUserModal.init();
