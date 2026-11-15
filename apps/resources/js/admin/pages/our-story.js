import { showToast } from '../toast';

const form = document.querySelector('[data-story-form]');
if (form) {
  const list = form.querySelector('[data-milestone-list]');
  const template = form.querySelector('[data-milestone-template]');
  const initial = list.innerHTML;
  const cards = form.querySelector('[data-story-cards]');
  let activeRow = list.querySelector('[data-milestone]');
  const urls = new Map();
  let nextKey =
    Math.max(
      -1,
      ...[...list.querySelectorAll('input[name$="[id]"]')].map((input) =>
        Number(input.name.match(/\[(\d+)\]/)?.[1] ?? -1),
      ),
    ) + 1;
  const rows = () => [...list.querySelectorAll('[data-milestone]')];
  let savingOrder = false;
  let savedKeys = rows().map((row) => row.dataset.key);
  const initialIds = rows()
    .map((row) => row.querySelector('input[name$="[id]"]').value)
    .sort();
  const saveOrder = async (previous) => {
    const items = rows().filter(
      (row) => row.querySelector('input[name$="[id]"]').value,
    );
    const ids = items.map(
      (row) => row.querySelector('input[name$="[id]"]').value,
    );
    const keys = items.map((row) => row.dataset.key);
    if (keys.join(',') === previous.join(',')) return;
    if (
      ids.some((id) => !id) ||
      JSON.stringify([...ids].sort()) !== JSON.stringify(initialIds)
    ) {
      showToast(form.dataset.orderUnsaved, 'warning');
      return;
    }
    savingOrder = true;
    form.inert = true;
    document.querySelector('[data-add-milestone]').disabled = true;
    try {
      const response = await fetch(form.dataset.orderUrl, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
        },
        body: JSON.stringify({ ids: ids.map(Number) }),
      });
      if (!response.ok) throw new Error('Order save failed');
      savedKeys = keys;
      showToast(form.dataset.orderSaved, 'success');
    } catch {
      const byKey = new Map(rows().map((row) => [row.dataset.key, row]));
      previous.forEach((key) => {
        if (byKey.has(key)) list.append(byKey.get(key));
      });
      showToast(form.dataset.orderError, 'error');
    } finally {
      savingOrder = false;
      form.inert = false;
      document.querySelector('[data-add-milestone]').disabled = false;
      refresh();
    }
  };
  const deleteDialog = form.querySelector('[data-delete-dialog]');
  let pendingDelete = null;
  const requestDelete = (row) => {
    pendingDelete = row;
    deleteDialog.querySelector('[data-delete-name]').textContent =
      row.querySelector('input[name$="[title]"]').value ||
      form.dataset.untitled;
    deleteDialog.showModal();
    deleteDialog.querySelector('[data-cancel-delete]').focus();
  };
  deleteDialog
    .querySelector('[data-cancel-delete]')
    .addEventListener('click', () => deleteDialog.close());
  deleteDialog.addEventListener('close', () => {
    pendingDelete = null;
  });
  deleteDialog
    .querySelector('[data-confirm-delete]')
    .addEventListener('click', () => {
      if (!pendingDelete) return;
      if (urls.has(pendingDelete)) URL.revokeObjectURL(urls.get(pendingDelete));
      urls.delete(pendingDelete);
      pendingDelete.remove();
      deleteDialog.close();
      refresh();
      (
        cards.querySelector('.story-card-select') ||
        document.querySelector('[data-add-milestone]')
      ).focus();
    });
  const createDraft = () => {
    const existing = rows().find(
      (row) => !row.querySelector('input[name$="[id]"]').value,
    );
    if (existing) return existing;
    list.insertAdjacentHTML(
      'beforeend',
      template.innerHTML.replaceAll('__KEY__', String(nextKey++)),
    );
    return list.lastElementChild;
  };
  const refresh = () => {
    if (!rows().length) activeRow = createDraft();
    const items = rows();
    const persisted = items.filter(
      (row) => row.querySelector('input[name$="[id]"]').value,
    );
    if (!items.includes(activeRow)) activeRow = items[0] || null;
    form.querySelector('[data-story-empty]').hidden = persisted.length > 0;
    form.querySelector('[data-editor-empty]').hidden = items.length > 0;
    form.querySelector('[data-milestone-count]').textContent = persisted.length;
    cards.replaceChildren();
    items.forEach((row, index) => {
      row.hidden = row !== activeRow;
      row.querySelector('[data-milestone-number]').textContent = String(
        index + 1,
      ).padStart(2, '0');
      row.querySelector('[data-move-up]').disabled = index === 0;
      row.querySelector('[data-move-down]').disabled =
        index === items.length - 1;
      row.querySelector('[data-story-position]').value = index + 1;
      row.querySelector('[data-story-position]').max = items.length;
      row.querySelector('[data-character-count]').textContent =
        `${row.querySelector('[data-story-description]').value.length} / 3,000`;
      const isSaved = Boolean(row.querySelector('input[name$="[id]"]').value);
      row.querySelector('[data-editor-title]').textContent = isSaved
        ? form.dataset.editLabel
        : form.dataset.addLabel;
      if (!isSaved) return;
      const card = document.createElement('div');
      card.className = `story-card${row === activeRow ? ' is-active' : ''}`;
      card.dataset.key = row.dataset.key;
      const handle = document.createElement('button');
      handle.type = 'button';
      handle.className = 'story-drag-handle';
      handle.setAttribute('aria-label', form.dataset.dragLabel);
      handle.title = form.dataset.dragLabel;
      handle.innerHTML =
        '<i class="bx bx-dots-vertical-rounded" aria-hidden="true"></i>';
      handle.addEventListener('keydown', (event) => {
        if (!['ArrowUp', 'ArrowDown'].includes(event.key)) return;
        event.preventDefault();
        if (savingOrder) return;
        const previous = rows().map((item) => item.dataset.key);
        const sibling =
          event.key === 'ArrowUp'
            ? row.previousElementSibling
            : row.nextElementSibling;
        if (!sibling) return;
        if (event.key === 'ArrowUp') list.insertBefore(row, sibling);
        else list.insertBefore(sibling, row);
        refresh();
        saveOrder(previous);
        [...cards.children]
          .find((item) => item.dataset.key === row.dataset.key)
          ?.querySelector('.story-drag-handle')
          .focus();
      });
      const select = document.createElement('button');
      select.type = 'button';
      select.className = 'story-card-select';
      select.setAttribute('aria-pressed', String(row === activeRow));
      const image = row.querySelector('[data-story-image]');
      const thumbnail = document.createElement(image.hidden ? 'span' : 'img');
      thumbnail.className = 'story-card-thumbnail';
      if (!image.hidden) {
        thumbnail.src = image.src;
        thumbnail.alt = '';
      } else {
        thumbnail.classList.add('bx', 'bx-image');
        thumbnail.setAttribute('aria-hidden', 'true');
      }
      const text = document.createElement('span');
      const title = document.createElement('strong');
      title.textContent =
        row.querySelector('input[name$="[title]"]').value ||
        form.dataset.untitled;
      title.title = title.textContent;
      const date = document.createElement('small');
      const rawDate = row.querySelector('input[type="date"]').value;
      date.textContent = rawDate
        ? new Intl.DateTimeFormat(document.documentElement.lang).format(
            new Date(`${rawDate}T12:00:00`),
          )
        : form.dataset.noDate;
      text.append(title, date);
      select.append(thumbnail, text);
      select.addEventListener('click', () => {
        activeRow = row;
        refresh();
      });
      const visibility = document.createElement('button');
      visibility.type = 'button';
      visibility.className = 'story-card-visibility';
      visibility.setAttribute('role', 'switch');
      visibility.setAttribute(
        'aria-label',
        `${form.dataset.visibleLabel}: ${title.textContent}`,
      );
      const checkbox = row.querySelector('[data-story-visible]');
      visibility.setAttribute('aria-checked', String(checkbox.checked));
      visibility.addEventListener('click', () => {
        checkbox.checked = !checkbox.checked;
        visibility.setAttribute('aria-checked', String(checkbox.checked));
      });
      const menu = document.createElement('details');
      menu.className = 'story-card-menu';
      const trigger = document.createElement('summary');
      trigger.setAttribute('aria-label', form.dataset.optionsLabel);
      trigger.innerHTML =
        '<i class="bx bx-dots-vertical-rounded" aria-hidden="true"></i>';
      const options = document.createElement('div');
      options.className = 'story-card-options';
      const edit = document.createElement('button');
      edit.type = 'button';
      edit.textContent = form.dataset.editLabel;
      edit.addEventListener('click', () => {
        activeRow = row;
        refresh();
        row.querySelector('input[required]').focus();
      });
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.textContent = form.dataset.deleteLabel;
      remove.addEventListener('click', () => {
        menu.open = false;
        requestDelete(row);
      });
      options.append(edit, remove);
      menu.append(trigger, options);
      menu.addEventListener('toggle', () => {
        if (menu.open) {
          const rect = trigger.getBoundingClientRect();
          options.style.position = 'fixed';
          options.style.right = `${Math.max(8, window.innerWidth - rect.right)}px`;
          options.style.top = `${Math.max(8, Math.min(rect.bottom + 4, window.innerHeight - 110))}px`;
          options.style.bottom = 'auto';
        }
        if (menu.open)
          cards.querySelectorAll('details[open]').forEach((other) => {
            if (other !== menu) other.open = false;
          });
      });
      card.append(handle, select, visibility, menu);
      cards.append(card);
    });
  };
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.story-card-menu'))
      cards.querySelectorAll('details[open]').forEach((menu) => {
        menu.open = false;
      });
  });
  cards.addEventListener('keydown', (event) => {
    if (event.key === 'Escape')
      cards.querySelectorAll('details[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary').focus();
      });
  });
  let drag = null;
  let dragFrame;
  const finishDrag = (commit) => {
    if (!drag) return;
    cancelAnimationFrame(dragFrame);
    if (drag.handle.hasPointerCapture(drag.pointerId))
      drag.handle.releasePointerCapture(drag.pointerId);
    const previous = rows().map((row) => row.dataset.key);
    if (commit && drag.moved) {
      const byKey = new Map(rows().map((row) => [row.dataset.key, row]));
      [...cards.children].forEach((card) =>
        list.append(byKey.get(card.dataset.key)),
      );
    }
    rows()
      .filter((row) => !row.querySelector('input[name$="[id]"]').value)
      .forEach((row) => list.append(row));
    const key = drag.card.dataset.key;
    const moved = drag.moved;
    drag = null;
    refresh();
    if (commit && moved) saveOrder(previous);
    if (moved)
      [...cards.children]
        .find((item) => item.dataset.key === key)
        ?.querySelector('.story-drag-handle')
        .focus({ preventScroll: true });
  };
  const moveDrag = () => {
    if (!drag) return;
    if (drag.moved) {
      const bounds = cards.getBoundingClientRect();
      if (drag.y < bounds.top + 40) cards.scrollTop -= 7;
      else if (drag.y > bounds.bottom - 40) cards.scrollTop += 7;
      const siblings = [...cards.children].filter((item) => item !== drag.card);
      const next = siblings.find((item) => {
        const rect = item.getBoundingClientRect();
        return drag.y < rect.top + rect.height / 2;
      });
      cards.insertBefore(drag.card, next || null);
    }
    dragFrame = requestAnimationFrame(moveDrag);
  };
  cards.addEventListener('pointerdown', (event) => {
    const handle = event.target.closest('.story-drag-handle');
    if (!handle || event.button !== 0 || drag || savingOrder) return;
    event.preventDefault();
    handle.focus({ preventScroll: true });
    handle.setPointerCapture(event.pointerId);
    drag = {
      handle,
      card: handle.closest('.story-card'),
      pointerId: event.pointerId,
      y: event.clientY,
      startY: event.clientY,
      moved: false,
    };
    dragFrame = requestAnimationFrame(moveDrag);
  });
  cards.addEventListener('pointermove', (event) => {
    if (!drag || event.pointerId !== drag.pointerId) return;
    drag.y = event.clientY;
    if (Math.abs(drag.y - drag.startY) > 5) {
      drag.moved = true;
      drag.card.classList.add('is-dragging');
    }
  });
  cards.addEventListener('pointerup', (event) => {
    if (drag && event.pointerId === drag.pointerId) finishDrag(true);
  });
  cards.addEventListener('pointercancel', () => finishDrag(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drag) {
      event.preventDefault();
      finishDrag(false);
    }
  });
  const setImage = (row, source) => {
    const image = row.querySelector('[data-story-image]');
    image.hidden = !source;
    if (source) image.src = source;
    else image.removeAttribute('src');
    row.querySelector('[data-photo-hint]').hidden = Boolean(source);
    row.querySelector('[data-remove-photo]').hidden = !source;
    refresh();
  };
  document
    .querySelector('[data-add-milestone]')
    .addEventListener('click', () => {
      const draft = rows().find(
        (row) => !row.querySelector('input[name$="[id]"]').value,
      );
      if (!draft && rows().length >= 30)
        return showToast(form.dataset.limit, 'warning');
      activeRow = draft || createDraft();
      refresh();
      activeRow.querySelector('input[required]').focus();
    });
  list.addEventListener('click', (event) => {
    const row = event.target.closest('[data-milestone]');
    if (!row) return;
    if (event.target.closest('[data-remove-milestone]')) {
      requestDelete(row);
    } else if (
      event.target.closest('[data-move-up]') &&
      row.previousElementSibling
    ) {
      list.insertBefore(row, row.previousElementSibling);
    } else if (
      event.target.closest('[data-move-down]') &&
      row.nextElementSibling
    ) {
      list.insertBefore(row.nextElementSibling, row);
    } else if (event.target.closest('[data-remove-photo]')) {
      row.querySelector('[data-story-photo]').value = '';
      row.querySelector('[data-remove-photo-value]').value = '1';
      if (urls.has(row)) URL.revokeObjectURL(urls.get(row));
      urls.delete(row);
      setImage(row, '');
    }
    refresh();
  });
  list.addEventListener('input', (event) => {
    if (!event.target.matches('[data-story-position]')) refresh();
  });
  list.addEventListener('change', (event) => {
    if (!event.target.matches('[data-story-position]')) return;
    const row = event.target.closest('[data-milestone]');
    const others = rows().filter((item) => item !== row);
    const position = Math.max(
      0,
      Math.min(others.length, Number(event.target.value || 1) - 1),
    );
    list.insertBefore(row, others[position] || null);
    refresh();
  });
  form.noValidate = true;
  form.addEventListener('submit', (event) => {
    if (savingOrder) {
      event.preventDefault();
      return;
    }
    const emptyDraftFields = [];
    if (initialIds.length) {
      rows()
        .filter((row) => !row.querySelector('input[name$="[id]"]').value)
        .forEach((row) => {
          const hasContent = [
            ...row.querySelectorAll(
              'input:not([type=hidden]):not([type=checkbox]):not([type=number]), textarea',
            ),
          ].some((input) =>
            input.type === 'file' ? input.files.length > 0 : input.value.trim(),
          );
          if (!hasContent)
            row.querySelectorAll('input, textarea').forEach((input) => {
              input.disabled = true;
              emptyDraftFields.push(input);
            });
        });
    }
    const invalid = [...form.elements].find(
      (input) => input.willValidate && !input.validity.valid,
    );
    if (!invalid) return;
    event.preventDefault();
    emptyDraftFields.forEach((input) => {
      input.disabled = false;
    });
    const row = invalid.closest('[data-milestone]');
    if (row) {
      activeRow = row;
      refresh();
    }
    const details = invalid.closest('details');
    if (details) details.open = true;
    invalid.focus();
    invalid.reportValidity();
  });
  list.addEventListener('change', async (event) => {
    if (!event.target.matches('[data-story-photo]')) return;
    const input = event.target;
    const row = input.closest('[data-milestone]');
    const file = input.files[0];
    if (!file) return;
    if (
      !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
      file.size > 2 * 1024 * 1024
    ) {
      input.value = '';
      return showToast(form.dataset.photoError, 'error');
    }
    const source = URL.createObjectURL(file);
    try {
      const image = new Image();
      image.src = source;
      await image.decode();
      if (!row.isConnected || input.files[0] !== file) {
        URL.revokeObjectURL(source);
        return;
      }
      if (urls.has(row)) URL.revokeObjectURL(urls.get(row));
      urls.set(row, source);
      row.querySelector('[data-remove-photo-value]').value = '0';
      setImage(row, source);
    } catch {
      URL.revokeObjectURL(source);
      input.value = '';
      showToast(form.dataset.photoError, 'error');
    }
  });
  form.addEventListener('reset', (event) => {
    event.preventDefault();
    urls.forEach((url) => URL.revokeObjectURL(url));
    urls.clear();
    list.innerHTML = initial;
    const byKey = new Map(rows().map((row) => [row.dataset.key, row]));
    savedKeys.forEach((key) => {
      if (byKey.has(key)) list.append(byKey.get(key));
    });
    activeRow = list.firstElementChild;
    refresh();
  });
  refresh();
}
