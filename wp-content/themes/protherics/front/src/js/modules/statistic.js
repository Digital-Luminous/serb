const selector = {
  statistic: '.c-statistic',
  number: '[data-statistic-number]',
  numberValue: '[data-statistic-number-value]',
};

const ANIMATION_DURATION = 2000;

const parseStatisticNumber = (value) => {
  if (!value) return null;

  const normalized = value.trim();
  const match = normalized
    .replace(/,/g, '')
    .match(/^([^0-9.-]*)(-?[0-9]+(?:\.[0-9]+)?)(.*)$/);

  if (!match) return null;

  const numericPart = match[2];
  const target = parseFloat(numericPart);

  if (Number.isNaN(target)) return null;

  return {
    prefix: match[1],
    target,
    suffix: match[3],
    decimals: numericPart.includes('.') ? numericPart.split('.')[1].length : 0,
    useGrouping: normalized.includes(','),
  };
};

const formatStatisticNumber = (value, { decimals, useGrouping }) => {
  const fixed = value.toFixed(decimals);

  if (!useGrouping) return fixed;

  const [integer, fraction] = fixed.split('.');
  const grouped = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

  return fraction !== undefined ? `${grouped}.${fraction}` : grouped;
};

const Statistic = {
  init() {
    const sections = document.querySelectorAll(selector.statistic);

    if (!sections.length) return;

    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;

    sections.forEach((section) => {
      const numbers = section.querySelectorAll(selector.number);

      if (!numbers.length) return;

      const items = Array.from(numbers)
        .map((element) => {
          const rawValue = element.dataset.statisticNumber;
          const valueElement = element.querySelector(selector.numberValue);
          const config = parseStatisticNumber(rawValue);

          if (!valueElement || !config) return null;

          if (prefersReducedMotion) {
            valueElement.textContent = rawValue;
            return null;
          }

          valueElement.textContent = `${config.prefix}${formatStatisticNumber(0, config)}${config.suffix}`;

          return { valueElement, config };
        })
        .filter(Boolean);

      if (!items.length) return;

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            items.forEach(({ valueElement, config }) => {
              Statistic.animateNumber(valueElement, config);
            });

            observer.unobserve(entry.target);
          });
        },
        { threshold: 1 }
      );

      observer.observe(section);
    });
  },

  animateNumber(element, config) {
    const start = performance.now();
    const { prefix, target, suffix, decimals, useGrouping } = config;

    const step = (now) => {
      const progress = Math.min((now - start) / ANIMATION_DURATION, 1);
      const eased = 1 - (1 - progress) ** 3;
      const current = eased * target;

      element.textContent = `${prefix}${formatStatisticNumber(current, {
        decimals,
        useGrouping,
      })}${suffix}`;

      if (progress < 1) {
        requestAnimationFrame(step);
      }
    };

    requestAnimationFrame(step);
  },
};

Statistic.init();
