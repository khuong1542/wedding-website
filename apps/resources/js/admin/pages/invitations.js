import { showToast } from '../toast';
import { showValidationErrors } from '../validation';

const root = document.querySelector('[data-invitations]');
if (root) {
  const form = root.querySelector('[data-iv-form]');
  const tabs = [...form.querySelectorAll('[data-iv-tab]')];
  const openTab = (name) => {
    tabs.forEach((tab) => {
      const active = tab.dataset.ivTab === name;
      tab.setAttribute('aria-selected', String(active));
      tab.tabIndex = active ? 0 : -1;
    });
    form.querySelectorAll('[data-iv-panel]').forEach((panel) => {
      panel.hidden = panel.dataset.ivPanel !== name;
    });
  };
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => openTab(tab.dataset.ivTab));
    tab.addEventListener('keydown', (event) => {
      const offset =
        event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
      if (!offset) return;
      event.preventDefault();
      const next = tabs[(index + offset + tabs.length) % tabs.length];
      openTab(next.dataset.ivTab);
      next.focus();
    });
  });
  form.addEventListener('admin:field-error', (event) => {
    openTab(
      event.detail.input.closest('[data-iv-panel]')?.dataset.ivPanel ||
        'general',
    );
  });
  form.addEventListener(
    'invalid',
    (event) => {
      openTab(
        event.target.closest('[data-iv-panel]')?.dataset.ivPanel || 'general',
      );
    },
    true,
  );
  const image = form.querySelector('[data-iv-photo-image]');
  const input = form.elements.namedItem('photo');
  const original = image.getAttribute('src') || '';
  let objectUrl;
  const showPhoto = (src) => {
    image.hidden = !src;
    if (src) image.src = src;
    else image.removeAttribute('src');
    form.querySelector('[data-iv-photo-hint]').hidden = !!src;
    form.querySelector('[data-iv-remove-photo]').hidden = !src;
  };
  showPhoto(original);
  input.addEventListener('change', () => {
    if (!input.files[0]) return;
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(input.files[0]);
    form.elements.namedItem('remove_photo').value = '0';
    showPhoto(objectUrl);
  });
  form.querySelector('[data-iv-remove-photo]').addEventListener('click', () => {
    input.value = '';
    form.elements.namedItem('remove_photo').value = '1';
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    showPhoto('');
  });
  form.addEventListener('reset', () => {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    showPhoto(original);
  });
  const confirm = root.querySelector('[data-iv-delete-dialog]');
  root.querySelectorAll('[data-iv-delete]').forEach((button) => {
    button.addEventListener('click', () => {
      confirm.querySelector('[data-iv-delete-form]').action =
        button.dataset.ivDelete;
      confirm.querySelector('[data-iv-delete-name]').textContent =
        button.dataset.name;
      confirm.showModal();
      confirm.querySelector('[data-iv-close-delete]').focus();
    });
  });
  root
    .querySelector('[data-iv-close-delete]')
    .addEventListener('click', () => confirm.close());
  root
    .querySelector('[data-iv-per-page]')
    ?.addEventListener('change', (event) => event.target.form.requestSubmit());
  const preview = root.querySelector('[data-iv-preview-dialog]');
  const content = root.querySelector('[data-iv-preview-content]');
  const loading = root.querySelector('[data-iv-loading]');
  let controller;
  let sequence = 0;
  const clear = () => {
    sequence++;
    controller?.abort();
    content.replaceChildren();
    loading.hidden = true;
  };
  const openPreview = async (url, payload) => {
    clear();
    const current = sequence;
    controller = new AbortController();
    loading.hidden = false;
    if (!preview.open) preview.showModal();
    try {
      const response = await fetch(url, {
        method: payload ? 'POST' : 'GET',
        body: payload,
        credentials: 'same-origin',
        cache: 'no-store',
        signal: controller.signal,
        headers: { Accept: payload ? 'application/json' : 'text/html' },
      });
      if (current !== sequence) return;
      let html;
      if (payload) {
        const result = await response.json();
        if (response.status === 422 && result.errors) {
          preview.close();
          showValidationErrors(result.errors, form);
          return;
        }
        html = result.html;
      } else html = await response.text();
      if (current !== sequence) return;
      if (!response.ok || typeof html !== 'string')
        throw new Error('Preview failed');
      const frame = document.createElement('iframe');
      frame.title = content.dataset.title;
      frame.setAttribute('sandbox', '');
      frame.hidden = true;
      frame.addEventListener(
        'load',
        () => {
          if (current !== sequence) return;
          loading.hidden = true;
          frame.hidden = false;
        },
        { once: true },
      );
      frame.srcdoc = html;
      content.replaceChildren(frame);
    } catch (error) {
      if (error.name !== 'AbortError' && current === sequence) {
        preview.close();
        showToast(root.dataset.error, 'error');
      }
    }
  };
  root
    .querySelectorAll('[data-iv-preview-url]')
    .forEach((button) =>
      button.addEventListener('click', () =>
        openPreview(button.dataset.ivPreviewUrl),
      ),
    );
  root
    .querySelector('[data-iv-form-preview]')
    .addEventListener('click', () =>
      openPreview(form.dataset.previewUrl, new FormData(form)),
    );
  root
    .querySelector('[data-iv-close-preview]')
    .addEventListener('click', () => preview.close());
  preview.addEventListener('close', clear);
}
