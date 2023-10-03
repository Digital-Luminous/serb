import $ from 'jquery';

const selector = {
  scrollingItem: '.js-scroll',
  header: '.l-header',
  page: 'html, .l-body',
}

export const Scroll = {
  init: function () {
    const $trigger = $(selector.scrollingItem);

    if (!$trigger.length) return;

    $trigger.on('click', (e) => {
      e.preventDefault();
      const hrefValue = $(e.currentTarget).attr('href');
      const headerHeight = $(selector.header).height();
      const scrollElTop = $(hrefValue).offset().top;

      $(selector.page).animate(
        {
          scrollTop: scrollElTop - headerHeight,
        },
        'slow'
      );
    });
  },
};

Scroll.init();
