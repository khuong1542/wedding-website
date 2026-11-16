import { showToast } from '../toast';
import { showValidationErrors } from '../validation';
const root = document.querySelector('[data-builder]');
if (root) {
  const form = root.querySelector('[data-builder-form]');
  const tabs = [...form.querySelectorAll('[data-tab]')];
  const openTab = (name) => {
    tabs.forEach((tab) => {
      const active = tab.dataset.tab === name;
      tab.setAttribute('aria-selected', String(active));
      tab.tabIndex = active ? 0 : -1;
    });
    form.querySelectorAll('[data-panel]').forEach((panel) => {
      panel.hidden = panel.dataset.panel !== name;
    });
  };
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => openTab(tab.dataset.tab));
    tab.addEventListener('keydown', (event) => {
      const offset =
        event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
      if (offset) {
        event.preventDefault();
        const next = tabs[(index + offset + tabs.length) % tabs.length];
        openTab(next.dataset.tab);
        next.focus();
      }
    });
  });
  const photo = form.querySelector('[data-page-image]');
  const photoInput = form.querySelector('[data-page-photo]');
  const originalPhoto = photo.getAttribute('src') || '';
  let photoUrl;
  const setPhoto = (source) => {
    photo.hidden = !source;
    if (source) photo.src = source;
    else photo.removeAttribute('src');
    form.querySelector('[data-photo-hint]').hidden = Boolean(source);
    form.querySelector('[data-remove-photo]').hidden = !source;
  };
  setPhoto(originalPhoto);
  photoInput.addEventListener('change', async () => {
    const file = photoInput.files[0];
    if (!file) return;
    if (
      !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
      file.size > 2 * 1024 * 1024
    ) {
      photoInput.value = '';
      return showToast(root.dataset.photoError, 'error');
    }
    const source = URL.createObjectURL(file);
    try {
      const image = new Image();
      image.src = source;
      await image.decode();
      if (photoInput.files[0] !== file) {
        URL.revokeObjectURL(source);
        return;
      }
      if (photoUrl) URL.revokeObjectURL(photoUrl);
      photoUrl = source;
      form.querySelector('[data-remove-photo-value]').value = '0';
      setPhoto(source);
    } catch {
      URL.revokeObjectURL(source);
      photoInput.value = '';
      showToast(root.dataset.photoError, 'error');
    }
  });
  form.querySelector('[data-remove-photo]').addEventListener('click', () => {
    photoInput.value = '';
    form.querySelector('[data-remove-photo-value]').value = '1';
    if (photoUrl) URL.revokeObjectURL(photoUrl);
    photoUrl = null;
    setPhoto('');
  });
  const reveal = (input) => {
    openTab(input.closest('[data-panel]')?.dataset.panel || 'general');
  };
  form.noValidate = true;
  form.addEventListener('submit', (event) => {
    const invalid = [...form.elements].find(
      (input) => input.willValidate && !input.validity.valid,
    );
    if (invalid) {
      event.preventDefault();
      reveal(invalid);
      invalid.focus();
      invalid.reportValidity();
    }
  });
  form.addEventListener('admin:field-error', (event) =>
    reveal(event.detail.input),
  );
  form.addEventListener('reset', () => {
    if (photoUrl) URL.revokeObjectURL(photoUrl);
    photoUrl = null;
    setPhoto(originalPhoto);
  });
  const preview = root.querySelector('[data-preview-dialog]');
  const previewButton = root.querySelector('[data-preview]');
  const previewContent = root.querySelector('[data-preview-content]');
  const previewLoading = root.querySelector('[data-preview-loading]');
  let previewController;
  let previewSequence = 0;
  const clearPreview = () => {
    previewSequence++;
    previewController?.abort();
    previewController = null;
    previewContent.replaceChildren();
    previewContent.removeAttribute('aria-busy');
    previewLoading.hidden = true;
    previewButton.disabled = false;
  };
  previewButton.addEventListener('click', async () => {
    clearPreview();
    const sequence = previewSequence;
    previewController = new AbortController();
    previewButton.disabled = true;
    const payload = new FormData(form);
    try {
      const response = await fetch(form.dataset.previewUrl, {
        method: 'POST',
        credentials: 'same-origin',
        cache: 'no-store',
        signal: previewController.signal,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: payload,
      });
      const result = await response.json();
      if (sequence !== previewSequence) return;
      if (response.status === 422 && result.errors) {
        showValidationErrors(result.errors, form);
        return;
      }
      if (!response.ok || typeof result.html !== 'string')
        throw new Error('Preview failed');
      const frame = document.createElement('iframe');
      frame.title = previewContent.dataset.title;
      frame.setAttribute('sandbox', '');
      frame.hidden = true;
      frame.addEventListener(
        'load',
        () => {
          if (sequence !== previewSequence) return;
          previewLoading.hidden = true;
          previewContent.removeAttribute('aria-busy');
          frame.hidden = false;
        },
        { once: true },
      );
      frame.srcdoc = result.html;
      previewLoading.hidden = false;
      previewContent.setAttribute('aria-busy', 'true');
      previewContent.replaceChildren(frame);
      preview.showModal();
    } catch (error) {
      if (error.name !== 'AbortError' && sequence === previewSequence)
        showToast(root.dataset.error, 'error');
    } finally {
      if (sequence === previewSequence) previewButton.disabled = false;
    }
  });
  preview.addEventListener('close', clearPreview);
  root
    .querySelector('[data-close-preview]')
    .addEventListener('click', () => preview.close());
  if (!form.elements.namedItem('id').value) openTab('general');
}
