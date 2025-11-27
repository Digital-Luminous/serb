import { enableScroll, disableScroll } from '../utils';

const selector = {
    videoLibrary: '.js-video-library',
    videoBtn: '.js-video-library-btn',
    modal: '.js-video-library-modal',
    modalCloseBtn: '.js-video-library-close',
    iframe: '.js-video-library-iframe'
};

const modalShowClass = 'video-library__modal--show';

const VideoLibrary = {
    init: function() {
        this.handleLibraries();
    },
    handleLibraries: function() {
        const libraries = document.querySelectorAll(selector.videoLibrary);
        if (!libraries) return;
        
        libraries.forEach(lib => {
            const btns = lib.querySelectorAll(selector.videoBtn);
            const modal = lib.querySelector(selector.modal);
            const iframe = modal.querySelector(selector.iframe)
            this.handleOpenEvent(btns, modal, iframe);
            this.handleCloseEvent(modal, iframe);
        })
    },
    handleOpenEvent: function(btns, modal, iframe) {
        btns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const src = e.target.getAttribute('data-video-src');
                if (!src) return;
                modal.classList.add(modalShowClass);
                iframe.src = src;
                disableScroll();
            })
        })
    },
    handleCloseEvent: function(modal, iframe) {
        const closeBtn = modal.querySelector(selector.modalCloseBtn);
        closeBtn.addEventListener('click', () => {
            modal.classList.remove(modalShowClass);
            iframe.src = '';
            enableScroll();
        })
    }
};

VideoLibrary.init()
