const selector = {
  accordion: '.js-accordion',
  accordionBody: '.c-accordion__body',
  accordionContent: '.js-accordion-content',
};

const element = {
  accordions: document.querySelectorAll(selector.accordion),
};

const state = {
  isActive: 'is-active',
};

const Accordion = {
  accordions: [],
  handleToggleAccordion(accordionItem) {
    const targetAccordion = accordionItem;
    const targetAccordionBody = accordionItem.querySelector(
      selector.accordionBody
    );
    const targetAccordionContent = accordionItem.querySelector(
      selector.accordionContent
    );

    const contentHeight = Accordion.getAccordionContentHeight(
      targetAccordionContent
    );

    if (targetAccordion.classList.contains(state.isActive)) {
      targetAccordion.classList.remove(state.isActive);
      targetAccordionBody.style.maxHeight = 0;
    } else {
      targetAccordion.classList.add(state.isActive);
      targetAccordionBody.style.maxHeight = contentHeight + 'px';
    }
  },
  getAccordionContentHeight(accordionContent) {
    return accordionContent.offsetHeight;
  },
  init() {
    Accordion.accordions = element.accordions;

    const shouldContinue = !!Accordion.accordions.length;
    if (!shouldContinue) return;

    Accordion.accordions.forEach((accordionItem) =>
      accordionItem.addEventListener('click', () =>
        Accordion.handleToggleAccordion(accordionItem)
      )
    );
  },
};

Accordion.init();
