import { showToast } from '../toast';
const page = document.querySelector('[data-events]');
if (page) {
  const form = page.querySelector('[data-event-form]');
  const csrf = form.querySelector('[name="_token"]').value;
  const cards = [...page.querySelectorAll('[data-event-card]')];
  const search = page.querySelector('[data-event-search]');
  const filter = page.querySelector('[data-event-filter]');
  const applyFilters = () => {
    const query = search.value.trim().toLocaleLowerCase();
    cards.forEach((card) => {
      card.hidden =
        !`${card.dataset.title} ${card.dataset.venue}`
          .toLocaleLowerCase()
          .includes(query) ||
        (filter.value !== 'all' && filter.value !== card.dataset.visible);
    });
    const count = cards.filter((card) => !card.hidden).length;
    page.querySelector('[data-event-count]').textContent = count;
    page.querySelector('[data-event-empty]').hidden = count > 0;
  };
  search.addEventListener('input', applyFilters);
  filter.addEventListener('change', applyFilters);
  page.querySelectorAll('[data-status-url]').forEach((button) =>
    button.addEventListener('click', async () => {
      const visible = button.getAttribute('aria-checked') !== 'true';
      button.disabled = true;
      try {
        const response = await fetch(button.dataset.statusUrl, {
          method: 'PATCH',
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
          },
          body: JSON.stringify({ is_visible: visible }),
        });
        if (!response.ok) throw new Error('Update failed');
        button.setAttribute('aria-checked', String(visible));
        const card = button.closest('[data-event-card]');
        card.dataset.visible = visible ? '1' : '0';
        if (form.elements.namedItem('id').value === card.dataset.id) {
          const checkbox = form.querySelector(
            'input[type="checkbox"][name="is_visible"]',
          );
          checkbox.checked = visible;
          checkbox.defaultChecked = visible;
        }
        applyFilters();
        showToast(page.dataset.statusSaved, 'success');
      } catch {
        showToast(page.dataset.statusError, 'error');
      } finally {
        button.disabled = false;
      }
    }),
  );
  const dialog = page.querySelector('[data-event-dialog]');
  page.querySelectorAll('[data-delete-event]').forEach((button) =>
    button.addEventListener('click', () => {
      page.querySelector('[data-delete-form]').action =
        button.dataset.deleteEvent;
      page.querySelector('[data-delete-name]').textContent =
        button.dataset.name;
      button.closest('details').open = false;
      dialog.showModal();
      dialog.querySelector('[data-cancel-delete]').focus();
    }),
  );
  page
    .querySelector('[data-cancel-delete]')
    .addEventListener('click', () => dialog.close());
  const menus = [...page.querySelectorAll('.event-options')];
  menus.forEach((menu) =>
    menu.addEventListener('toggle', () => {
      if (!menu.open) return;
      menus.forEach((other) => {
        if (other !== menu) other.open = false;
      });
      const rect = menu.querySelector('summary').getBoundingClientRect();
      const popup = menu.querySelector('div');
      popup.style.top = `${Math.max(8, Math.min(rect.bottom + 4, window.innerHeight - 110))}px`;
      popup.style.right = `${Math.max(8, window.innerWidth - rect.right)}px`;
    }),
  );
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.event-options'))
      menus.forEach((menu) => {
        menu.open = false;
      });
  });
  const image = form.querySelector('[data-event-image]');
  const photoInput = form.querySelector('[data-event-photo]');
  const originalPhoto = image.getAttribute('src') || '';
  let previewUrl;
  const setPhoto = (source) => {
    image.hidden = !source;
    if (source) image.src = source;
    else image.removeAttribute('src');
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
      return showToast(page.dataset.photoError, 'error');
    }
    const source = URL.createObjectURL(file);
    try {
      const probe = new Image();
      probe.src = source;
      await probe.decode();
      if (photoInput.files[0] !== file) {
        URL.revokeObjectURL(source);
        return;
      }
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = source;
      form.querySelector('[data-remove-photo-value]').value = '0';
      setPhoto(source);
    } catch {
      URL.revokeObjectURL(source);
      photoInput.value = '';
      showToast(page.dataset.photoError, 'error');
    }
  });
  form.querySelector('[data-remove-photo]').addEventListener('click', () => {
    photoInput.value = '';
    form.querySelector('[data-remove-photo-value]').value = '1';
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    setPhoto('');
  });
  const description = form.elements.namedItem('description');
  const updateCount = () => {
    form.querySelector('[data-event-count-text]').textContent =
      `${description.value.length} / 3,000`;
  };
  description.addEventListener('input', updateCount);
  form.addEventListener('reset', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    setPhoto(originalPhoto);
    requestAnimationFrame(updateCount);
  });
  updateCount();
  applyFilters();
}
