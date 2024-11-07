import Choices from 'choices.js';
const { __ } = wp.i18n;

const element = {
  selects: document.querySelectorAll('.js-select'),
};

const selectType = {
  formSelect: 'form-select',
};

export const params = {
  itemSelectText: '',
  searchEnabled: false,
  allowHTML: true,
  removeItemButton: true,
  shouldSort: false,
  classNames: {
    containerOuter: 'c-select',
    containerInner: 'c-select__inner',
    input: 'c-select__input',
    inputCloned: 'c-select__input--cloned',
    list: 'c-select__list',
    listItems: 'c-select__list--multiple',
    listSingle: 'c-select__list--single',
    listDropdown: 'c-select__list--dropdown',
    item: 'c-select__item',
    itemSelectable: 'c-select__item--selectable',
    itemDisabled: 'c-select__item--disabled',
    itemChoice: 'c-select__item--choice',
    placeholder: 'c-select__placeholder',
    group: 'c-select__group',
    groupHeading: 'c-select__heading',
    button: 'c-select__button',
    activeState: 'is-active',
    focusState: 'is-focused',
    openState: 'is-open',
    disabledState: 'is-disabled',
    highlightedState: 'is-highlighted',
    selectedState: 'is-selected',
    flippedState: 'is-flipped',
    loadingState: 'is-loading',
    noResults: 'has-no-results',
  },
};

export const choicesInstances = [...element.selects].map((select) => {
  const type = select.dataset.type;

  if (type === selectType.formSelect) {
    return new Choices(select, {
      removeItemButton: true,
      allowHTML: true,
      searchPlaceholderValue: __('Type to search products', 'protherics'),
      classNames: {
        ...params.classNames,
        containerOuter: 'c-select c-select--alt',
      },
    });
  } else {
    const defaultOptions = [...select.options];

    return new Choices(select, {
      ...params,
      callbackOnCreateTemplates(template) {
        return {
          choice: ({ classNames }, data) => {
            const relatedOption = defaultOptions[data.id - 1];
            const optionColor = relatedOption.dataset.color || '';
            const optionsArgs = { classNames, data, optionColor };

            return template(renderDefaultSelectTemplate(optionsArgs));
          },
        };
      },
    });
  }
});

function renderDefaultSelectTemplate({ data, classNames, optionColor }) {
  return `
    <div ${optionColor ? `style="color: ${optionColor}"` : ''} class="${
    classNames.item
  } ${classNames.itemChoice} ${
    data.placeholder ? classNames.placeholder : ''
  } ${data.selected ? classNames.selectedState : ''}
        ${
          data.disabled ? classNames.itemDisabled : classNames.itemSelectable
        }" data-choice ${
    data.disabled
      ? 'data-choice-disabled aria-disabled="true"'
      : 'data-choice-selectable'
  } data-id="${data.id}" data-value="${data.value}">${data.label}</div>
  `;
}
