import { showToast } from '../toast';
import { showValidationErrors } from '../validation';

const root = document.querySelector('[data-page-builder]');
if (root) {
  const seed = JSON.parse(root.querySelector('[data-pb-seed]').textContent);
  const labels = seed.labels;
  const types = seed.types;
  let blocks = seed.blocks;
  let active = blocks[0]?.id;
  let revision = Number(root.dataset.revision);
  let device = 'desktop';
  let saved = JSON.stringify(blocks);
  let saving = false;
  const undo = [];
  const redo = [];
  const canvas = root.querySelector('[data-pb-canvas]');
  const inspector = root.querySelector('[data-pb-inspector]');
  const status = root.querySelector('[data-pb-status]');
  const token = inspector.querySelector('[name="_token"]').value;
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
  const create = (type) => ({
    id: uuid(),
    type,
    name: labels[type],
    title: type === 'hero' || type === 'heading' ? labels.sample_title : '',
    text: ['hero', 'text', 'quote'].includes(type) ? labels.sample_text : '',
    url: '',
    button_label: labels.sample_button,
    align: 'center',
    background: '#FFF9FB',
    padding: type === 'divider' ? 12 : 32,
    height: type === 'hero' ? 320 : type === 'spacer' ? 60 : 0,
    desktop: true,
    tablet: true,
    mobile: true,
    animation: 'none',
    duration: 600,
  });
  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };
  const iconButton = (action, icon, label) => {
    const button = element('button');
    button.type = 'button';
    button.dataset.action = action;
    button.setAttribute('aria-label', label);
    button.title = label;
    button.append(element('i', `bx bx-${icon}`));
    return button;
  };
  const snapshot = () => JSON.stringify(blocks);
  const remember = () => {
    undo.push(snapshot());
    if (undo.length > 50) undo.shift();
    redo.length = 0;
  };
  const updateStatus = () => {
    root.querySelector('[data-pb-undo]').disabled = !undo.length;
    root.querySelector('[data-pb-redo]').disabled = !redo.length;
    status.textContent = snapshot() === saved ? labels.clean : labels.dirty;
  };
  const openTab = (group, name) => {
    const prefix = group === 'library' ? 'library' : 'inspector';
    root
      .querySelectorAll(`[data-pb-${prefix}-tab]`)
      .forEach((button) =>
        button.setAttribute(
          'aria-selected',
          String(
            button.dataset[
              group === 'library' ? 'pbLibraryTab' : 'pbInspectorTab'
            ] === name,
          ),
        ),
      );
    root
      .querySelectorAll(
        group === 'library' ? '[data-pb-library]' : '[data-pb-panel]',
      )
      .forEach((panel) => {
        panel.hidden =
          (group === 'library'
            ? panel.dataset.pbLibrary
            : panel.dataset.pbPanel) !== name;
      });
  };
  const renderInspector = () => {
    const block = blocks.find((item) => item.id === active);
    inspector.hidden = !block;
    root.querySelector('[data-pb-no-selection]').hidden = !!block;
    if (!block) return;
    inspector
      .querySelectorAll('[name]:not([name="_token"])')
      .forEach((input) => {
        if (input.type === 'checkbox') input.checked = !!block[input.name];
        else input.value = block[input.name] ?? '';
      });
    const allowed = {
      name: true,
      title: ['hero', 'heading', 'image'].includes(block.type),
      text: ['hero', 'text', 'quote'].includes(block.type),
      url: ['hero', 'image', 'button'].includes(block.type),
      button_label: block.type === 'button',
    };
    inspector.querySelectorAll('[data-pb-field]').forEach((field) => {
      field.hidden = !allowed[field.dataset.pbField];
    });
    inspector.querySelector('label[for="pb-url"]').textContent = [
      'hero',
      'image',
    ].includes(block.type)
      ? labels.image_url
      : labels.url;
    inspector
      .querySelectorAll('.admin-field-error')
      .forEach((node) => node.remove());
    inspector.querySelectorAll('[aria-invalid]').forEach((input) => {
      input.removeAttribute('aria-invalid');
      input.removeAttribute('aria-describedby');
    });
  };
  const renderCanvas = () => {
    canvas.replaceChildren();
    canvas.dataset.device = device;
    if (!blocks.length) canvas.append(element('p', 'pb-empty', labels.empty));
    blocks.forEach((block) => {
      const card = element(
        'article',
        `pb-block${active === block.id ? ' is-selected' : ''}${!block[device] ? ' is-device-hidden' : ''}`,
      );
      card.dataset.blockId = block.id;
      const toolbar = element('div', 'pb-block-toolbar');
      toolbar.append(element('span', 'pb-block-name', block.name));
      const actions = element('div', 'pb-block-controls');
      const handle = iconButton('drag', 'grid-vertical', labels.drag);
      handle.draggable = true;
      actions.append(
        handle,
        iconButton('up', 'chevron-up', labels.move_up),
        iconButton('down', 'chevron-down', labels.move_down),
        iconButton('duplicate', 'copy', labels.duplicate),
        iconButton('delete', 'trash', labels.delete),
      );
      toolbar.append(actions);
      const body = element('div', `pb-block-body pb-type-${block.type}`);
      body.style.background = block.background;
      body.style.padding = `${block.padding}px`;
      body.style.minHeight = `${block.height}px`;
      body.style.textAlign = block.align;
      if (['hero', 'image'].includes(block.type)) {
        if (/^https?:\/\//i.test(block.url)) {
          const image = element('img');
          image.src = block.url;
          image.alt = block.title || '';
          image.draggable = false;
          body.append(image);
        } else body.append(element('div', 'pb-image-placeholder', '♡'));
      }
      if (['hero', 'heading'].includes(block.type))
        body.append(
          element(block.type === 'hero' ? 'h2' : 'h3', '', block.title),
        );
      if (['hero', 'text'].includes(block.type))
        body.append(element('p', '', block.text));
      if (block.type === 'quote')
        body.append(element('blockquote', '', block.text));
      if (block.type === 'button')
        body.append(element('span', 'pb-content-button', block.button_label));
      if (block.type === 'divider') body.append(element('hr'));
      card.append(toolbar, body);
      if (!block[device])
        card.append(element('small', 'pb-hidden-note', labels.hidden));
      canvas.append(card);
    });
    updateStatus();
  };
  const render = () => {
    renderCanvas();
    renderInspector();
  };
  const insert = (type, at = blocks.length) => {
    if (!Object.hasOwn(types, type)) return;
    if (blocks.length >= 50) return showToast(labels.limit, 'warning');
    remember();
    const block = create(type);
    blocks.splice(at, 0, block);
    active = block.id;
    render();
  };
  root.querySelectorAll('[data-pb-add]').forEach((button) => {
    button.addEventListener('click', () => insert(button.dataset.pbAdd));
    button.addEventListener('dragstart', (event) => {
      event.dataTransfer.setData(
        'application/json',
        JSON.stringify({ type: button.dataset.pbAdd }),
      );
      event.dataTransfer.effectAllowed = 'copy';
    });
  });
  canvas.addEventListener('click', (event) => {
    const card = event.target.closest('[data-block-id]');
    if (!card) return;
    active = card.dataset.blockId;
    const action = event.target.closest('[data-action]')?.dataset.action;
    const index = blocks.findIndex((block) => block.id === active);
    if (action === 'duplicate') {
      if (blocks.length >= 50) return showToast(labels.limit, 'warning');
      remember();
      const copy = structuredClone(blocks[index]);
      copy.id = uuid();
      blocks.splice(index + 1, 0, copy);
      active = copy.id;
    } else if (action === 'delete') {
      remember();
      blocks.splice(index, 1);
      active = blocks[index]?.id || blocks[index - 1]?.id;
    } else if (['up', 'down'].includes(action)) {
      const destination = index + (action === 'up' ? -1 : 1);
      if (destination >= 0 && destination < blocks.length) {
        remember();
        [blocks[index], blocks[destination]] = [
          blocks[destination],
          blocks[index],
        ];
      }
    }
    render();
  });
  canvas.addEventListener('dragstart', (event) => {
    const handle = event.target.closest('[data-action="drag"]');
    if (!handle) {
      event.preventDefault();
      return;
    }
    event.dataTransfer.setData(
      'application/json',
      JSON.stringify({ id: handle.closest('[data-block-id]').dataset.blockId }),
    );
    event.dataTransfer.effectAllowed = 'move';
  });
  canvas.addEventListener('dragover', (event) => {
    event.preventDefault();
    canvas.classList.add('is-dragging-over');
  });
  canvas.addEventListener('dragleave', (event) => {
    if (!canvas.contains(event.relatedTarget))
      canvas.classList.remove('is-dragging-over');
  });
  canvas.addEventListener('drop', (event) => {
    event.preventDefault();
    canvas.classList.remove('is-dragging-over');
    let data;
    try {
      data = JSON.parse(event.dataTransfer.getData('application/json'));
    } catch {
      return;
    }
    const target = event.target.closest('[data-block-id]');
    let at = target
      ? blocks.findIndex((block) => block.id === target.dataset.blockId)
      : blocks.length;
    if (
      target &&
      event.clientY >
        target.getBoundingClientRect().top +
          target.getBoundingClientRect().height / 2
    )
      at++;
    if (data.type) return insert(data.type, at);
    const from = blocks.findIndex((block) => block.id === data.id);
    if (from < 0) return;
    if (from < at) at--;
    if (from === at) return;
    remember();
    const [block] = blocks.splice(from, 1);
    blocks.splice(at, 0, block);
    active = block.id;
    render();
  });
  inspector.addEventListener('submit', (event) => event.preventDefault());
  inspector.addEventListener('input', (event) => {
    const input = event.target;
    const block = blocks.find((item) => item.id === active);
    if (!block || !Object.hasOwn(block, input.name)) return;
    remember();
    block[input.name] =
      input.type === 'checkbox'
        ? input.checked
        : input.type === 'number'
          ? Number(input.value)
          : input.value;
    renderCanvas();
  });
  root.querySelector('[data-pb-deselect]').addEventListener('click', () => {
    active = null;
    render();
  });
  root.querySelector('[data-pb-undo]').addEventListener('click', () => {
    if (!undo.length) return;
    redo.push(snapshot());
    blocks = JSON.parse(undo.pop());
    active = blocks[0]?.id;
    render();
  });
  root.querySelector('[data-pb-redo]').addEventListener('click', () => {
    if (!redo.length) return;
    undo.push(snapshot());
    blocks = JSON.parse(redo.pop());
    active = blocks[0]?.id;
    render();
  });
  root
    .querySelectorAll('[data-pb-library-tab]')
    .forEach((button) =>
      button.addEventListener('click', () =>
        openTab('library', button.dataset.pbLibraryTab),
      ),
    );
  root
    .querySelectorAll('[data-pb-inspector-tab]')
    .forEach((button) =>
      button.addEventListener('click', () =>
        openTab('inspector', button.dataset.pbInspectorTab),
      ),
    );
  root.querySelectorAll('[data-pb-layout]').forEach((button) =>
    button.addEventListener('click', () => {
      const layout =
        button.dataset.pbLayout === 'starter'
          ? ['hero', 'text']
          : ['heading', 'text', 'quote'];
      if (blocks.length + layout.length > 50)
        return showToast(labels.limit, 'warning');
      remember();
      const additions = layout.map(create);
      blocks.push(...additions);
      active = additions[0].id;
      render();
    }),
  );
  const showErrors = (errors) => {
    const first = Object.keys(errors).find((key) => /^blocks\.\d+\./.test(key));
    if (first) {
      const [, index, field] = first.split('.');
      active = blocks[Number(index)]?.id;
      render();
      openTab(
        'inspector',
        ['background', 'padding', 'height'].includes(field)
          ? 'style'
          : ['desktop', 'tablet', 'mobile', 'animation', 'duration'].includes(
                field,
              )
            ? 'advanced'
            : 'general',
      );
      const local = {};
      Object.entries(errors).forEach(([key, messages]) => {
        if (key.startsWith(`blocks.${index}.`))
          local[key.split('.')[2]] = messages;
      });
      showValidationErrors(local, inspector);
    }
    showToast(Object.values(errors).flat()[0] || labels.error, 'error');
  };
  document.querySelectorAll('[data-pb-save]').forEach((button) =>
    button.addEventListener('click', async () => {
      if (saving) return;
      saving = true;
      document.querySelectorAll('[data-pb-save]').forEach((item) => {
        item.disabled = true;
      });
      const sent = snapshot();
      try {
        const response = await fetch(root.dataset.saveUrl, {
          method: 'PUT',
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
          },
          body: JSON.stringify({
            blocks: JSON.parse(sent),
            revision,
            action: button.dataset.pbSave,
          }),
        });
        const result = await response.json();
        if (response.status === 422 && result.errors)
          return showErrors(result.errors);
        if (!response.ok) throw new Error(result.message || labels.error);
        revision = result.revision;
        saved = sent;
        updateStatus();
        root.querySelector('[data-pb-published]').hidden = !result.published_at;
        showToast(result.message, 'success');
      } catch (error) {
        showToast(error.message || labels.error, 'error');
      } finally {
        saving = false;
        document.querySelectorAll('[data-pb-save]').forEach((item) => {
          item.disabled = false;
        });
      }
    }),
  );
  const dialog = root.querySelector('[data-pb-dialog]');
  const previewContent = root.querySelector('[data-pb-preview-content]');
  const previewStatus = root.querySelector('[data-pb-preview-status]');
  let previewController;
  let previewSequence = 0;
  const resizePreview = () => {
    const frame = previewContent.querySelector('iframe');
    if (!frame) return;
    const width = { desktop: 1280, tablet: 768, mobile: 375 }[device];
    const scale = Math.min(previewContent.clientWidth / width, 1);
    frame.style.width = `${width}px`;
    frame.style.height = `${620 / scale}px`;
    frame.style.transform = `scale(${scale})`;
  };
  new ResizeObserver(resizePreview).observe(previewContent);
  root.querySelectorAll('[data-pb-device]').forEach((button) =>
    button.addEventListener('click', () => {
      device = button.dataset.pbDevice;
      root
        .querySelectorAll('[data-pb-device]')
        .forEach((item) =>
          item.setAttribute('aria-pressed', String(item === button)),
        );
      renderCanvas();
      resizePreview();
    }),
  );
  document
    .querySelector('[data-pb-preview]')
    .addEventListener('click', async () => {
      previewController?.abort();
      const sequence = ++previewSequence;
      previewController = new AbortController();
      previewContent.replaceChildren();
      previewStatus.textContent = labels.loading;
      if (!dialog.open) dialog.showModal();
      try {
        const response = await fetch(root.dataset.previewUrl, {
          method: 'POST',
          credentials: 'same-origin',
          signal: previewController.signal,
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
          },
          body: JSON.stringify({ blocks }),
        });
        const result = await response.json();
        if (sequence !== previewSequence) return;
        if (response.status === 422 && result.errors) {
          dialog.close();
          showErrors(result.errors);
          return;
        }
        if (!response.ok) throw new Error(result.message || labels.error);
        const frame = element('iframe');
        frame.title = labels.preview;
        frame.setAttribute('sandbox', '');
        frame.hidden = true;
        frame.addEventListener(
          'load',
          () => {
            if (sequence === previewSequence) {
              previewStatus.textContent = '';
              frame.hidden = false;
              resizePreview();
            }
          },
          { once: true },
        );
        frame.srcdoc = result.html;
        previewContent.replaceChildren(frame);
        resizePreview();
      } catch (error) {
        if (error.name !== 'AbortError' && sequence === previewSequence)
          previewStatus.textContent = labels.error;
      }
    });
  root
    .querySelector('[data-pb-close-preview]')
    .addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => {
    previewSequence++;
    previewController?.abort();
    previewContent.replaceChildren();
  });
  render();
}
