const root = document.querySelector('[data-wishes]');
if (root) {
  const checks = [...root.querySelectorAll('[data-wish-check]')];
  const all = [...root.querySelectorAll('[data-wish-all]')];
  const selected = () => checks.filter((check) => check.checked);
  const updateSelection = () => {
    const count = selected().length;
    all.forEach((checkbox) => {
      checkbox.checked = count > 0 && count === checks.length;
      checkbox.indeterminate = count > 0 && count < checks.length;
      checkbox.disabled = checks.length === 0;
    });
    root.querySelectorAll('[data-wish-bulk]').forEach((button) => {
      button.disabled = count === 0;
    });
    const counter = root.querySelector('[data-wish-count]');
    if (counter) counter.textContent = count ? `(${count})` : '';
  };
  all.forEach((checkbox) =>
    checkbox.addEventListener('change', () => {
      checks.forEach((check) => {
        check.checked = checkbox.checked;
      });
      updateSelection();
    }),
  );
  checks.forEach((checkbox) =>
    checkbox.addEventListener('change', updateSelection),
  );
  updateSelection();
  root
    .querySelector('[data-wish-per-page]')
    ?.addEventListener('change', (event) => event.target.form.requestSubmit());
  const status = root.querySelector('[data-wish-status]');
  const visible = root.querySelector('[data-wish-visible]');
  if (status && visible) {
    status.addEventListener('change', () => {
      visible.checked = status.value === 'published';
      status.form.requestSubmit();
    });
    visible.addEventListener('change', () => {
      status.value = visible.checked ? 'published' : 'hidden';
      status.form.requestSubmit();
    });
  }
}
