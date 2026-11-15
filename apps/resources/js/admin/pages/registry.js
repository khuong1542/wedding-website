import { showToast } from '../toast';

const form = document.querySelector('[data-registry]');
if (form) {
  const urls = new Map();
  const initialPhotos = Object.fromEntries(
    ['bride', 'groom'].map((person) => [
      person,
      form.querySelector(`[data-qr-image="${person}"]`).getAttribute('src') ||
        '',
    ]),
  );
  const setPhoto = (person, source) => {
    const image = form.querySelector(`[data-qr-image="${person}"]`);
    image.hidden = !source;
    if (source) image.src = source;
    else image.removeAttribute('src');
    form.querySelector(`[data-qr-hint="${person}"]`).hidden = Boolean(source);
    form.querySelector(`[data-remove-qr="${person}"]`).hidden = !source;
    const download = form.querySelector(`[data-qr-download="${person}"]`);
    download.hidden = !source;
    if (source) download.href = source;
  };
  Object.entries(initialPhotos).forEach(([person, source]) =>
    setPhoto(person, source),
  );
  form.querySelectorAll('[data-copy-account]').forEach((button) =>
    button.addEventListener('click', async () => {
      try {
        const value = form
          .querySelector(`[data-account="${button.dataset.copyAccount}"]`)
          .value.trim();
        if (!value) throw new Error('Empty account');
        await navigator.clipboard.writeText(value);
        showToast(form.dataset.copied, 'success');
      } catch {
        showToast(form.dataset.copyError, 'error');
      }
    }),
  );
  form.querySelectorAll('[data-qr-input]').forEach((input) =>
    input.addEventListener('change', async () => {
      const file = input.files[0];
      if (!file) return;
      if (
        !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
        file.size > 2 * 1024 * 1024
      ) {
        input.value = '';
        return showToast(form.dataset.imageError, 'error');
      }
      const source = URL.createObjectURL(file);
      try {
        const image = new Image();
        image.src = source;
        await image.decode();
        if (input.files[0] !== file) {
          URL.revokeObjectURL(source);
          return;
        }
        const person = input.dataset.qrInput;
        if (urls.has(person)) URL.revokeObjectURL(urls.get(person));
        urls.set(person, source);
        form.querySelector(`[data-qr-removal="${person}"]`).value = '0';
        setPhoto(person, source);
      } catch {
        URL.revokeObjectURL(source);
        input.value = '';
        showToast(form.dataset.imageError, 'error');
      }
    }),
  );
  form.querySelectorAll('[data-remove-qr]').forEach((button) =>
    button.addEventListener('click', () => {
      const person = button.dataset.removeQr;
      form.querySelector(`[data-qr-input="${person}"]`).value = '';
      form.querySelector(`[data-qr-removal="${person}"]`).value = '1';
      if (urls.has(person)) URL.revokeObjectURL(urls.get(person));
      urls.delete(person);
      setPhoto(person, '');
    }),
  );
  form.addEventListener('reset', () => {
    urls.forEach((url) => URL.revokeObjectURL(url));
    urls.clear();
    Object.entries(initialPhotos).forEach(([person, source]) =>
      setPhoto(person, source),
    );
  });
  form.noValidate = true;
  form.addEventListener('submit', (event) => {
    const invalid = [...form.elements].find(
      (input) => input.willValidate && !input.validity.valid,
    );
    if (invalid) {
      event.preventDefault();
      invalid.focus();
      invalid.reportValidity();
    }
  });
}
