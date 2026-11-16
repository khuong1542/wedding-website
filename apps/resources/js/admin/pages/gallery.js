const root = document.querySelector('[data-gallery]');
if (root) {
  const bulk = root.querySelector('#gallery-bulk-form');
  const checks = [...root.querySelectorAll('[data-gallery-check]')];
  const all = root.querySelector('[data-gallery-all]');
  const selected = () =>
    checks.filter((input) => input.checked).map((input) => input.value);
  const refresh = () => {
    const count = selected().length;
    if (all) {
      all.checked = count > 0 && count === checks.length;
      all.indeterminate = count > 0 && count < checks.length;
      all.disabled = checks.length === 0;
    }
    root.querySelectorAll('[data-gallery-bulk]').forEach((button) => {
      button.disabled =
        count === 0 ||
        (button.hasAttribute('data-gallery-assign') &&
          !root.querySelector(
            '#gallery-assign-album option[value]:not([value=""])',
          ));
    });
    const counter = root.querySelector('[data-gallery-count]');
    if (counter)
      counter.textContent = counter.dataset.label.replace(':count', count);
  };
  all?.addEventListener('change', () => {
    checks.forEach((input) => {
      input.checked = all.checked;
    });
    refresh();
  });
  checks.forEach((input) => input.addEventListener('change', refresh));
  refresh();
  const dialog = root.querySelector('[data-gallery-confirm]');
  const deleteForm = dialog.querySelector('[data-gallery-delete-form]');
  const ids = dialog.querySelector('[data-gallery-delete-ids]');
  const showDelete = (url, values, name) => {
    deleteForm.action = url;
    deleteForm.elements.namedItem('_method').value = values ? 'POST' : 'DELETE';
    ids.replaceChildren();
    dialog.querySelector('[data-gallery-delete-name]').textContent = name || '';
    const copy = dialog.querySelector('[data-gallery-confirm-text]');
    copy.textContent = values ? copy.dataset.mediaText : copy.dataset.albumText;
    if (values) {
      values.forEach((value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = value;
        ids.append(input);
      });
      const action = document.createElement('input');
      action.type = 'hidden';
      action.name = 'action';
      action.value = 'delete';
      ids.append(action);
    }
    dialog.showModal();
    dialog.querySelector('[data-gallery-close]').focus();
  };
  root
    .querySelectorAll('[data-gallery-delete-media]')
    .forEach((button) =>
      button.addEventListener('click', () =>
        showDelete(
          bulk.action,
          [button.dataset.galleryDeleteMedia],
          button.dataset.name,
        ),
      ),
    );
  root
    .querySelector('[data-gallery-bulk-delete]')
    ?.addEventListener('click', () => {
      const values = selected();
      if (values.length) showDelete(bulk.action, values);
    });
  root
    .querySelectorAll('[data-gallery-delete-album]')
    .forEach((button) =>
      button.addEventListener('click', () =>
        showDelete(
          button.dataset.galleryDeleteAlbum,
          null,
          button.dataset.name,
        ),
      ),
    );
  dialog
    .querySelector('[data-gallery-close]')
    .addEventListener('click', () => dialog.close());
  root
    .querySelector('[data-gallery-per-page]')
    ?.addEventListener('change', (event) => event.target.form.requestSubmit());
  const filters = root.querySelector('[data-gallery-filters]');
  let searchTimer;
  filters
    ?.querySelectorAll('select')
    .forEach((select) =>
      select.addEventListener('change', () => filters.requestSubmit()),
    );
  filters?.querySelector('[name="q"]')?.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => filters.requestSubmit(), 600);
  });
  const albumDialog = root.querySelector('[data-gallery-album-dialog]');
  root.querySelector('[data-gallery-assign]')?.addEventListener('click', () => {
    const container = albumDialog.querySelector('[data-gallery-album-ids]');
    container.replaceChildren();
    selected().forEach((id) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      container.append(input);
    });
    albumDialog.showModal();
    albumDialog.querySelector('select').focus();
  });
  albumDialog
    .querySelector('[data-gallery-close-album]')
    .addEventListener('click', () => albumDialog.close());
  const form = root.querySelector('[data-gallery-form]');
  if (form) {
    const type = form.querySelector('[data-gallery-type]');
    const photo = form.elements.namedItem('photo');
    const video = form.elements.namedItem('video_url');
    const image = form.querySelector('[data-gallery-photo-image]');
    const original = image.getAttribute('src') || '';
    let objectUrl;
    const showPhoto = (src) => {
      image.hidden = !src;
      if (src) image.src = src;
      else image.removeAttribute('src');
      form.querySelector('[data-gallery-photo-hint]').hidden = !!src;
      form.querySelector('[data-gallery-photo-change]').hidden = !src;
    };
    const showType = () => {
      const isVideo = type.value === 'video';
      form.querySelector('[data-gallery-image-field]').hidden = isVideo;
      form.querySelector('[data-gallery-video-field]').hidden = !isVideo;
      photo.disabled = isVideo;
      video.disabled = !isVideo;
      video.required = isVideo;
    };
    type.addEventListener('change', showType);
    showType();
    showPhoto(original);
    photo.addEventListener('change', () => {
      if (!photo.files[0]) return;
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = URL.createObjectURL(photo.files[0]);
      showPhoto(objectUrl);
    });
    form.addEventListener('reset', () =>
      setTimeout(() => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        showPhoto(original);
        showType();
      }, 0),
    );
    form.addEventListener('admin:field-error', (event) => {
      if (event.detail.input === photo) {
        type.value = 'image';
        showType();
      }
      if (event.detail.input === video) {
        type.value = 'video';
        showType();
      }
    });
  }
}
