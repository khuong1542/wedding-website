import { showToast } from '../toast';
import { showValidationErrors } from '../validation';

const root = document.querySelector('[data-navigation]');
if (root) {
  const seed = JSON.parse(root.querySelector('[data-nf-seed]').textContent);
  const lists = {
    menu: Array.isArray(seed.menu) ? seed.menu : [],
    footer_links: Array.isArray(seed.footer_links) ? seed.footer_links : [],
  };
  const original = structuredClone(lists);
  const labels = seed.labels;
  const form = root.querySelector('[data-nf-form]');
  const jsonInputs = {
    menu: form.querySelector('[data-nf-menu]'),
    footer_links: form.querySelector('[data-nf-links]'),
  };
  let timer;
  let controller;
  let sequence = 0;
  const uuid = () => {
    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 15) | 64;
    bytes[8] = (bytes[8] & 63) | 128;
    return [...bytes]
      .map(
        (byte, index) =>
          `${[4, 6, 8, 10].includes(index) ? '-' : ''}${byte.toString(16).padStart(2, '0')}`,
      )
      .join('');
  };
  const sync = () =>
    Object.keys(lists).forEach((key) => {
      jsonInputs[key].value = JSON.stringify(lists[key]);
    });
  const schedule = () => {
    sync();
    sequence++;
    controller?.abort();
    clearTimeout(timer);
    timer = setTimeout(preview, 500);
  };
  const node = (tag, className, text) => {
    const item = document.createElement(tag);
    if (className) item.className = className;
    if (text !== undefined) item.textContent = text;
    return item;
  };
  const action = (name, icon, label) => {
    const button = node('button');
    button.type = 'button';
    button.dataset.action = name;
    button.setAttribute('aria-label', label);
    button.title = label;
    button.append(node('i', `bx bx-${icon}`));
    return button;
  };
  const renderList = (key) => {
    const body = root.querySelector(`[data-nf-list="${key}"]`);
    body.replaceChildren();
    if (!lists[key].length) {
      const row = node('tr');
      const cell = node('td', 'nf-empty', labels.empty);
      cell.colSpan = 5;
      row.append(cell);
      body.append(row);
    }
    lists[key].forEach((item, index) => {
      const row = node('tr');
      row.dataset.id = item.id;
      const position = node('td');
      const drag = action('drag', 'grid-vertical', labels.drag);
      drag.draggable = true;
      position.append(drag, node('small', '', String(index + 1)));
      row.append(position);
      ['label', 'url'].forEach((field) => {
        const cell = node('td');
        const wrapper = node('div', 'nf-field');
        const input = node('input');
        input.type = 'text';
        input.value = item[field];
        input.maxLength = field === 'label' ? 80 : 1000;
        input.required = true;
        input.dataset.errorName = `${key}[${index}][${field}]`;
        input.setAttribute('aria-label', `${labels[field]} ${index + 1}`);
        input.addEventListener('input', () => {
          item[field] = input.value;
        });
        wrapper.append(input);
        if (field === 'label') {
          const icons = node('select', 'nf-icon-select');
          icons.setAttribute('aria-label', `${labels.icon} ${index + 1}`);
          icons.dataset.errorName = `${key}[${index}][icon]`;
          seed.icons.forEach((icon) => {
            const option = node('option', '', icon);
            option.value = icon;
            option.selected = item.icon === icon;
            icons.append(option);
          });
          icons.addEventListener('change', () => {
            item.icon = icons.value;
          });
          wrapper.append(icons);
        }
        cell.append(wrapper);
        row.append(cell);
      });
      const state = node('td');
      const checkbox = node('input', 'nf-switch');
      checkbox.type = 'checkbox';
      checkbox.checked = item.visible;
      checkbox.setAttribute('role', 'switch');
      checkbox.setAttribute('aria-label', `${labels.visible} ${index + 1}`);
      checkbox.dataset.errorName = `${key}[${index}][visible]`;
      checkbox.addEventListener('change', () => {
        item.visible = checkbox.checked;
      });
      state.append(checkbox);
      row.append(state);
      const controls = node('td');
      const buttons = node('div', 'nf-row-actions');
      const up = action('up', 'chevron-up', labels.up);
      up.disabled = index === 0;
      const down = action('down', 'chevron-down', labels.down);
      down.disabled = index === lists[key].length - 1;
      buttons.append(
        up,
        down,
        action('duplicate', 'copy', labels.duplicate),
        action('delete', 'trash', labels.delete),
      );
      controls.append(buttons);
      row.append(controls);
      body.append(row);
    });
    sync();
  };
  const dialog = root.querySelector('[data-nf-confirm]');
  let pendingDelete;
  Object.keys(lists).forEach((key) => {
    const body = root.querySelector(`[data-nf-list="${key}"]`);
    body.addEventListener('click', (event) => {
      const button = event.target.closest('[data-action]');
      if (!button) return;
      const index = lists[key].findIndex(
        (item) => item.id === button.closest('[data-id]').dataset.id,
      );
      if (index < 0) return;
      if (button.dataset.action === 'delete') {
        pendingDelete = { key, id: lists[key][index].id };
        dialog.querySelector('[data-nf-delete-name]').textContent =
          lists[key][index].label;
        dialog.showModal();
        dialog.querySelector('[data-nf-close]').focus();
        return;
      }
      if (button.dataset.action === 'duplicate') {
        if (lists[key].length >= 20) return showToast(labels.limit, 'warning');
        lists[key].splice(index + 1, 0, { ...lists[key][index], id: uuid() });
      } else if (['up', 'down'].includes(button.dataset.action)) {
        const to = index + (button.dataset.action === 'up' ? -1 : 1);
        if (to < 0 || to >= lists[key].length) return;
        [lists[key][index], lists[key][to]] = [
          lists[key][to],
          lists[key][index],
        ];
      } else return;
      renderList(key);
      schedule();
    });
    body.addEventListener('dragstart', (event) => {
      const handle = event.target.closest('[data-action="drag"]');
      if (!handle) {
        event.preventDefault();
        return;
      }
      event.dataTransfer.setData(
        'application/json',
        JSON.stringify({ key, id: handle.closest('[data-id]').dataset.id }),
      );
      event.dataTransfer.effectAllowed = 'move';
    });
    body.addEventListener('dragover', (event) => event.preventDefault());
    body.addEventListener('drop', (event) => {
      event.preventDefault();
      let data;
      try {
        data = JSON.parse(event.dataTransfer.getData('application/json'));
      } catch {
        return;
      }
      if (data.key !== key) return;
      const from = lists[key].findIndex((item) => item.id === data.id);
      if (from < 0) return;
      const target = event.target.closest('[data-id]');
      let to = target
        ? lists[key].findIndex((item) => item.id === target.dataset.id)
        : lists[key].length;
      if (
        target &&
        event.clientY >
          target.getBoundingClientRect().top +
            target.getBoundingClientRect().height / 2
      )
        to++;
      if (from < to) to--;
      const [item] = lists[key].splice(from, 1);
      lists[key].splice(to, 0, item);
      renderList(key);
      schedule();
    });
  });
  root.querySelectorAll('[data-nf-add]').forEach((button) =>
    button.addEventListener('click', () => {
      const key = button.dataset.nfAdd;
      if (lists[key].length >= 20) return showToast(labels.limit, 'warning');
      lists[key].push({
        id: uuid(),
        label: labels.new_item,
        url: '#home',
        icon: 'heart',
        visible: true,
      });
      renderList(key);
      schedule();
    }),
  );
  dialog
    .querySelector('[data-nf-close]')
    .addEventListener('click', () => dialog.close());
  dialog
    .querySelector('[data-nf-confirm-delete]')
    .addEventListener('click', () => {
      if (pendingDelete) {
        lists[pendingDelete.key] = lists[pendingDelete.key].filter(
          (item) => item.id !== pendingDelete.id,
        );
        renderList(pendingDelete.key);
        schedule();
      }
      dialog.close();
      pendingDelete = null;
    });
  const openFooterTab = (name) => {
    root.querySelectorAll('[data-nf-footer-tab]').forEach((button) => {
      const active = button.dataset.nfFooterTab === name;
      button.setAttribute('aria-selected', String(active));
      button.tabIndex = active ? 0 : -1;
    });
    root.querySelectorAll('[data-nf-panel]').forEach((panel) => {
      panel.hidden = panel.dataset.nfPanel !== name;
    });
  };
  const footerTabs = [...root.querySelectorAll('[data-nf-footer-tab]')];
  footerTabs.forEach((button, index) => {
    button.addEventListener('click', () =>
      openFooterTab(button.dataset.nfFooterTab),
    );
    button.addEventListener('keydown', (event) => {
      const offset =
        event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
      if (!offset) return;
      event.preventDefault();
      const next =
        footerTabs[(index + offset + footerTabs.length) % footerTabs.length];
      openFooterTab(next.dataset.nfFooterTab);
      next.focus();
    });
  });
  form.addEventListener('admin:field-error', (event) => {
    const panel = event.detail.input.closest('[data-nf-panel]');
    if (panel) openFooterTab(panel.dataset.nfPanel);
  });
  form.addEventListener(
    'invalid',
    (event) => {
      const panel = event.target.closest('[data-nf-panel]');
      if (panel) openFooterTab(panel.dataset.nfPanel);
    },
    true,
  );
  root
    .querySelectorAll('[data-nf-tab]')
    .forEach((tab) =>
      tab.addEventListener('click', () =>
        root
          .querySelectorAll('[data-nf-tab]')
          .forEach((item) => item.classList.toggle('is-primary', item === tab)),
      ),
    );
  const logo = form.elements.namedItem('logo');
  const image = root.querySelector('[data-nf-logo-image]');
  const originalImage = image.getAttribute('src') || '';
  const originalRemove = form.elements.namedItem('remove_logo').value;
  let objectUrl;
  const setLogo = (src) => {
    image.hidden = !src;
    if (src) image.src = src;
    else image.removeAttribute('src');
    root.querySelector('[data-nf-logo-hint]').hidden = !!src;
    root.querySelector('[data-nf-remove-logo]').hidden = !src;
  };
  setLogo(originalImage);
  logo.addEventListener('change', () => {
    if (!logo.files[0]) return;
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(logo.files[0]);
    form.elements.namedItem('remove_logo').value = '0';
    setLogo(objectUrl);
  });
  root.querySelector('[data-nf-remove-logo]').addEventListener('click', () => {
    logo.value = '';
    form.elements.namedItem('remove_logo').value = '1';
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    setLogo('');
    schedule();
  });
  const loading = root.querySelector('[data-nf-loading]');
  const loadingText = loading.textContent;
  async function preview() {
    if (
      [...form.querySelectorAll('input,textarea,select')].some(
        (input) => input.willValidate && !input.validity.valid,
      )
    )
      return;
    sync();
    const current = ++sequence;
    controller?.abort();
    controller = new AbortController();
    loading.textContent = loadingText;
    const payload = new FormData(form);
    payload.delete('_method');
    try {
      const response = await fetch(form.dataset.previewUrl, {
        method: 'POST',
        body: payload,
        signal: controller.signal,
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      });
      const result = await response.json();
      if (current !== sequence) return;
      if (response.status === 422 && result.errors) {
        showValidationErrors(result.errors, form);
        loading.textContent = '';
        return;
      }
      if (!response.ok || typeof result.html !== 'string')
        throw new Error('Preview failed');
      root.querySelector('[data-nf-preview-content]').innerHTML = result.html;
      loading.textContent = '';
    } catch (error) {
      if (error.name !== 'AbortError' && current === sequence)
        loading.textContent = root.dataset.error;
    }
  }
  form.addEventListener('input', schedule);
  form.addEventListener('change', schedule);
  form.addEventListener('submit', sync);
  root.querySelector('[data-nf-preview]').addEventListener('click', preview);
  form.addEventListener('reset', () =>
    setTimeout(() => {
      Object.keys(lists).forEach((key) => {
        lists[key] = structuredClone(original[key]);
        renderList(key);
      });
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = null;
      setLogo(originalImage);
      form.elements.namedItem('remove_logo').value = originalRemove;
      schedule();
    }, 0),
  );
  Object.keys(lists).forEach(renderList);
  openFooterTab('content');
  preview();
}
