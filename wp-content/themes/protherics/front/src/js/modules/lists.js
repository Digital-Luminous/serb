const elSelector = {
  cmsContent: '.c-cms-content',
  listItem: 'li',
}

const cmsContents = document.querySelectorAll(elSelector.cmsContent);

const lists = {
  addColorsForListItems: function () {
    window.addEventListener('load', function () {
      cmsContents.forEach(cmsContent => {
        const listItems = cmsContent.querySelectorAll(elSelector.listItem);

        listItems.forEach(listItem => {
          if(listItem.childNodes.length) {
            if(listItem.childNodes[0].style) {
              listItem.style.color = listItem.childNodes[0].style.color;
            }
          }
        });
      });
    })
  },
  init: function () {
    this.addColorsForListItems();
  }
}

if(cmsContents.length) {
  lists.init();
}
