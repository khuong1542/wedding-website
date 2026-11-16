import './themes';

const form = document.querySelector('.ui-settings-page [data-appearance-form]');
if (form) {
  const originalTheme = form.elements.namedItem('theme').value;
  form.querySelectorAll('[data-ui-range]').forEach((range) => {
    const number = form.elements.namedItem(range.dataset.uiRange);
    range.addEventListener('input', () => {
      number.value = range.value;
    });
    number.addEventListener('input', () => {
      range.value = number.value;
    });
  });
  const presets = [...form.querySelectorAll('[data-ui-theme]')];
  presets.forEach((button) =>
    button.addEventListener('click', () => {
      form.elements.namedItem('theme').value = button.dataset.uiTheme;
      presets.forEach((item) =>
        item.setAttribute('aria-pressed', String(item === button)),
      );
    }),
  );
  form.addEventListener('reset', () =>
    setTimeout(() => {
      form.elements.namedItem('theme').value = originalTheme;
      form.querySelectorAll('[data-ui-range]').forEach((range) => {
        range.value = form.elements.namedItem(range.dataset.uiRange).value;
      });
      presets.forEach((button) =>
        button.setAttribute(
          'aria-pressed',
          String(
            form.elements.namedItem('theme').value === button.dataset.uiTheme,
          ),
        ),
      );
    }, 0),
  );
}
