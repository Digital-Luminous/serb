import { enableScroll, disableScroll } from '../utils';

const selector = {
    videoLibrary: '.js-video-library',
    videoBtnSelector: 'js-video-library-btn',
    videoTitle: '.js-video-title',
    modal: '.js-video-library-modal',
    modalCloseBtn: '.js-video-library-close',
    iframe: '.js-video-library-iframe',
    videoSrc: '.js-video-src'
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
            this.handleVideoLinks(lib);

            const btns = lib.querySelectorAll(`.${selector.videoBtnSelector}`);
            const modal = lib.querySelector(selector.modal);
            const iframe = modal.querySelector(selector.iframe);

            this.handleOpenEvent(btns, modal, iframe);
            this.handleCloseEvent(modal, iframe);
        })
    },
    handleOpenEvent: function(btns, modal, iframe) {
        btns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const src = e.target.closest(selector.videoSrc).getAttribute('data-video-src');
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
    },
    handleVideoLinks: function(lib) {
        const linkTitles = lib.querySelectorAll(selector.videoTitle);

        linkTitles.forEach(title => {
            const links = title.querySelectorAll('a');
            links.forEach(link => {
                const src = link.closest(selector.videoSrc).getAttribute('data-video-src');
                link.classList.add(selector.videoBtnSelector);
                link.setAttribute('data-video-src', src);
            })
        })
    }
};

VideoLibrary.init()
