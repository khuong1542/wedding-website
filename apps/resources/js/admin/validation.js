let errorNumber = 0;
const errorNodes = new WeakMap();

const clearError = (input) => {
  const node = errorNodes.get(input);
  if (!node) return;
  const describedBy = (input.getAttribute('aria-describedby') || '')
    .split(/\s+/)
    .filter((id) => id && id !== node.id);
  if (describedBy.length)
    input.setAttribute('aria-describedby', describedBy.join(' '));
  else input.removeAttribute('aria-describedby');
  input.removeAttribute('aria-invalid');
  node.remove();
  errorNodes.delete(input);
};

const showError = (input, message) => {
  clearError(input);
  const error = document.createElement('p');
  error.className = 'admin-field-error';
  error.id = `admin-field-error-${++errorNumber}`;
  error.textContent = message;
  input.setAttribute('aria-invalid', 'true');
  input.setAttribute(
    'aria-describedby',
    [input.getAttribute('aria-describedby'), error.id]
      .filter(Boolean)
      .join(' '),
  );
  const field = input.closest(
    '.couple-field, .story-field, .registry-field, .event-field, .builder-field, .section-field, .iv-field, .appearance-field, .pb-field, .nf-field, .wish-field, .wish-setting-field',
  );
  const photo = input.closest(
    '.couple-photo-picker, .story-image-picker, .registry-qr-picker, .event-photo-picker, .builder-photo',
  );
  if (field) field.append(error);
  else if (photo) photo.after(error);
  else input.after(error);
  errorNodes.set(input, error);
};

// Capture native validation so messages appear next to controls instead of a browser popup.
document.addEventListener(
  'invalid',
  (event) => {
    if (!event.target.closest('.admin-main')) return;
    event.preventDefault();
    showError(event.target, event.target.validationMessage);
  },
  true,
);

for (const type of ['input', 'change']) {
  document.addEventListener(type, (event) => {
    if (errorNodes.has(event.target)) clearError(event.target);
  });
}
document.addEventListener('reset', (event) => {
  if (event.target instanceof HTMLFormElement)
    [...event.target.elements].forEach(clearError);
});

export function showValidationErrors(errors, scope = document) {
  scope.querySelectorAll('[aria-invalid="true"]').forEach(clearError);
  let first;
  Object.entries(errors).forEach(([key, messages]) => {
    const parts = key.split('.');
    const name = parts.shift() + parts.map((part) => `[${part}]`).join('');
    const input = [...scope.querySelectorAll('[name], [data-error-name]')].find(
      (element) =>
        (element.name === name || element.dataset.errorName === name) &&
        element.matches('input:not([type="hidden"]), textarea, select'),
    );
    if (!input) return;
    showError(input, messages[0]);
    first ||= input;
  });
  if (first) {
    first.form?.dispatchEvent(
      new CustomEvent('admin:field-error', { detail: { input: first } }),
    );
    first.focus({ preventScroll: true });
  }
}

const renderServerErrors = () => {
  const seed = document.querySelector('[data-validation-errors]');
  if (!seed) return;
  let errors;
  try {
    errors = JSON.parse(seed.textContent);
  } catch {
    return;
  }
  showValidationErrors(errors);
  seed.remove();
};
if (document.readyState === 'complete') renderServerErrors();
else window.addEventListener('load', renderServerErrors, { once: true });
