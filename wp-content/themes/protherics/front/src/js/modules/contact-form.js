import Choices from 'choices.js';
import { params as customSelectParams } from './custom-select';

const selector = {
  form: '.wpcf7-form',
  defaultSelect: '.js-hidden-select',
  customSelect: '.js-select',
  fieldsGroup: '.c-form__fields-group',
};

const className = {
  jsSelect: 'js-select',
  selectContainerOuter: 'c-select c-select--alt',
};

const element = {
  form: document.querySelector(selector.form),
  defaultSelects: document.querySelectorAll(selector.defaultSelect),
};

const params = {
  timeout: 300, // Set in the live JS, never updated
};

const FormCustomSelects = {
  customSelects: [],
  initSelects: () => {
    element.defaultSelects?.forEach(select => {
      const clonedSelect = select.cloneNode(true);
      const uniqueClass = `${className.jsSelect}--${select.name}`;

      clonedSelect.classList.add(className.jsSelect, uniqueClass);
      clonedSelect.name = select.name + '_select';

      select.closest(selector.fieldsGroup).appendChild(clonedSelect);

      const customSelectEl = element.form.querySelector(selector.customSelect + '.' + uniqueClass);

      setTimeout(() => {
        const customSelect = new Choices(customSelectEl, {
          ...FormCustomSelects.getNormalSelectOptions(select),

          classNames: {
            ...customSelectParams.classNames,
            containerOuter: className.selectContainerOuter,
          },
        });

        FormCustomSelects.triggerDefaultSelect(customSelectEl, select);
        FormCustomSelects.customSelects = [...FormCustomSelects.customSelects, customSelect];
      }, params.timeout);
    });
  },
  triggerDefaultSelect: (customSelect, defaultSelect) => {
    customSelect.addEventListener('change', ({ detail }) => {
      defaultSelect.value = detail.value;
    });
  },
  getNormalSelectOptions(select) {
    const type = select.dataset.type;

    if (type === 'search') {
      return {
        removeItemButton: true,
        allowHTML: true,
        searchPlaceholderValue: select.dataset.searchPlaceholder || 'Search...',
      };
    } else {
      return {
        ...customSelectParams,
      };
    }
  },

  addEventListeners: () => {
    element.form.addEventListener('wpcf7mailsent', () => {
      const customSelects = document.querySelectorAll(selector.customSelect);
      FormCustomSelects.customSelects.forEach(choices => {
        choices.destroy();
      });

      customSelects.forEach(select => select.parentElement.removeChild(select));
      FormCustomSelects.customSelects = [];
      FormCustomSelects.initSelects();
    });
  },

  handleSelectsValidation() {
    document.addEventListener(
      'wpcf7submit',
      function () {
        const selects = document.querySelectorAll('.js-select');

        setTimeout(() => {
          selects.forEach(select => {
            const selectInner = select.closest('.c-select .c-select__inner');

            if (selectInner) {
              selectInner.classList.toggle('is-not-valid', select.classList.contains('wpcf7-not-valid'));
            }
          });
        }, 1);
      },
      false
    );
  },

  init: () => {
    if (!element.form) return;

    FormCustomSelects.initSelects();
    FormCustomSelects.addEventListeners();
    FormCustomSelects.handleSelectsValidation();
  },
};

FormCustomSelects.init();
