import { showToast } from './toast';
import { showValidationErrors } from './validation';

const pending = new WeakSet();

document.addEventListener('submit', async (event) => {
  const form = event.target;
  if (
    !(form instanceof HTMLFormElement) ||
    !form.matches('.admin-main form[enctype="multipart/form-data"]')
  )
    return;
  if (event.defaultPrevented) return;
  event.preventDefault();
  if (pending.has(form)) return;
  const payload = new FormData(form);
  const buttons = [...form.elements].filter(
    (element) => element.type === 'submit',
  );
  pending.add(form);
  buttons.forEach((button) => {
    button.disabled = true;
  });
  form.inert = true;
  form.setAttribute('aria-busy', 'true');
  let errors;
  let redirecting = false;
  try {
    const response = await fetch(form.action, {
      method: form.method.toUpperCase(),
      body: payload,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    const result = await response.json();
    if (response.status === 422 && result.errors) {
      errors = result.errors;
      showToast(Object.values(errors).flat()[0] || result.message, 'error');
    } else if (response.ok && typeof result.redirect === 'string') {
      redirecting = true;
      window.location.assign(result.redirect);
    } else throw new Error('Save failed');
  } catch {
    showToast(
      document.querySelector('[data-toast-region]')?.dataset.saveError,
      'error',
    );
  } finally {
    if (!redirecting) {
      pending.delete(form);
      form.inert = false;
      form.removeAttribute('aria-busy');
      buttons.forEach((button) => {
        button.disabled = false;
      });
      form.dispatchEvent(new Event('admin:submit-complete'));
      if (errors) showValidationErrors(errors, form);
    }
  }
});
