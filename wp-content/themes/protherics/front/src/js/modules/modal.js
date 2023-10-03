import { isTablet, getScrollbarWidth } from '../utils';
import { FetchTeamMember } from './team-member';

const element = {
  modal: document.querySelector('.js-modal'),
  modalContent: document.querySelector('.c-modal__content'),
  modalInner: document.querySelector('.js-modal-inner'),
  overlay: document.querySelector('.js-overlay'),
  modalTriggers: document.querySelectorAll('.js-modal-trigger'),
  closeModal: document.querySelector('.js-modal-close'),
};

const state = {
  isVisible: 'is-visible',
  isModalOpen: 'is-modal-open',
};

const params = {
  transitionDelay: 300,
  bodyPadding:  getScrollbarWidth() + 'px',
};

const Modal = {
  modalEl: null,
  overlayEl: null,
  closeModalEl: null,
  modelInnerEl: null,
  modalTriggers: [],

  handleCloseModal() {
    Modal.overlayEl.classList.remove(state.isVisible);
    Modal.modalEl.classList.remove(state.isVisible);

    setTimeout(() => {
      document.body.classList.remove(state.isModalOpen);
      document.body.attributeStyleMap.delete('padding')

    }, params.transitionDelay);
  },
  async handleOpenModal(e) {
    const targetApiUrl = e.target.dataset.memberApiUrl;
    const modalContent = await FetchTeamMember.init(targetApiUrl);

    !isTablet() ? document.body.style.paddingRight = params.bodyPadding : null;

    document.body.classList.add(state.isModalOpen);
    Modal.overlayEl.classList.add(state.isVisible);
    Modal.modalEl.classList.add(state.isVisible);

    Modal.modelInnerEl.innerHTML = modalContent;
  },
  handleClickOutside(e) {
    if (!Modal.modelInnerEl.contains(e.target)) {
      Modal.handleCloseModal();
    }
  },
  addEventListeners() {
    Modal.modalTriggers.forEach((trigger) =>
      trigger.addEventListener('click', Modal.handleOpenModal)
    );
    Modal.closeModalEl.addEventListener('click', Modal.handleCloseModal);
    Modal.modalEl.addEventListener('click', Modal.handleClickOutside);
  },
  init() {
    Modal.modalEl = element.modal;
    Modal.overlayEl = element.overlay;
    Modal.modelInnerEl = element.modalInner;
    Modal.closeModalEl = element.closeModal;
    Modal.modalTriggers = element.modalTriggers;

    const shouldCountinue =
      !!Modal.modalEl && !!Modal.overlayEl && Modal.modelInnerEl && !!Modal.modalTriggers.length;

    if (!shouldCountinue) return;

    Modal.addEventListeners();
  },
};

Modal.init();
