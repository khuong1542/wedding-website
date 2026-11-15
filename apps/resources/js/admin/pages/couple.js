import { showToast } from '../toast';

const form = document.querySelector('[data-couple-form]');

if (form) {
  const fields = [
    ...form.querySelectorAll(
      'input:not([type=hidden]):not([type=file]), textarea',
    ),
  ];
  const submitButtons = [...form.elements].filter(
    (element) => element.type === 'submit',
  );
  const status = form.querySelector('[data-couple-status]');
  const photos = Object.fromEntries(
    ['groom', 'bride', 'groom-bank-qr', 'bride-bank-qr'].map((person) => [
      person,
      form
        .querySelector(`[data-photo-preview="${person}"]`)
        .getAttribute('src') || '',
    ]),
  );
  const saved = Object.fromEntries(
    fields.map((field) => [field.name, field.value]),
  );
  const savedPhotos = { ...photos };
  let photoVersion = 0;
  let reading = 0;
  const showStatus = (message, error = false) => {
    if (status) {
      status.textContent = message;
      status.classList.toggle('is-error', error);
    }
    if (error) showToast(message, 'error');
  };
  const updatePreview = () => {
    Object.entries(photos).forEach(([person, photo]) => {
      form.querySelector(`[data-photo-hint="${person}"]`).hidden =
        Boolean(photo);
      form.querySelector(`[data-photo-remove="${person}"]`).hidden = !photo;
      form
        .querySelectorAll(`[data-photo-preview="${person}"]`)
        .forEach((image) => {
          image.hidden = !photo;
          if (photo) image.src = photo;
          else image.removeAttribute('src');
        });
    });
  };
  const restore = () => {
    photoVersion++;
    fields.forEach((field) => {
      field.value =
        typeof saved[field.name] === 'string' ? saved[field.name] : '';
    });
    Object.keys(photos).forEach((person) => {
      photos[person] = savedPhotos[person];
      const removal = form.querySelector(`[data-photo-removal="${person}"]`);
      removal.value = removal.defaultValue;
    });
    form.querySelectorAll('[data-photo-input]').forEach((input) => {
      input.value = '';
    });
    updatePreview();
  };
  restore();
  form.addEventListener('input', () => {
    showStatus(form.dataset.unsaved);
    updatePreview();
  });
  form.addEventListener('submit', (event) => {
    if (reading) {
      event.preventDefault();
      return;
    }
    if (form.reportValidity())
      submitButtons.forEach((button) => {
        button.disabled = true;
      });
  });
  form.addEventListener('reset', (event) => {
    event.preventDefault();
    restore();
    showStatus('');
  });
  form.querySelectorAll('[data-photo-input]').forEach((input) => {
    input.addEventListener('change', async () => {
      const file = input.files[0];
      if (!file) return;
      if (
        !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
        file.size > 2 * 1024 * 1024
      ) {
        input.value = '';
        showStatus(form.dataset.photoError, true);
        return;
      }
      const version = photoVersion;
      reading++;
      submitButtons.forEach((button) => {
        button.disabled = true;
      });
      try {
        const data = await new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result);
          reader.onerror = reject;
          reader.readAsDataURL(file);
        });
        const image = new Image();
        image.src = data;
        await image.decode();
        if (version !== photoVersion) return;
        photos[input.dataset.photoInput] = data;
        form.querySelector(
          `[data-photo-removal="${input.dataset.photoInput}"]`,
        ).value = '0';
        showStatus(form.dataset.unsaved);
        updatePreview();
      } catch {
        input.value = '';
        showStatus(form.dataset.photoReadError, true);
      } finally {
        reading--;
        submitButtons.forEach((button) => {
          button.disabled = reading > 0;
        });
      }
    });
  });
  form.querySelectorAll('[data-photo-remove]').forEach((button) => {
    button.addEventListener('click', () => {
      photoVersion++;
      photos[button.dataset.photoRemove] = '';
      form.querySelector(
        `[data-photo-removal="${button.dataset.photoRemove}"]`,
      ).value = '1';
      form.querySelector(
        `[data-photo-input="${button.dataset.photoRemove}"]`,
      ).value = '';
      updatePreview();
      showStatus(form.dataset.unsaved);
    });
  });
}
