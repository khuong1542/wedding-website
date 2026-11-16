import { showValidationErrors } from '../validation';
const root = document.querySelector('[data-appearance]');
if (root) {
  const form = root.querySelector('[data-appearance-form]');
  const viewport = root.querySelector('[data-appearance-viewport]');
  const loading = root.querySelector('[data-appearance-loading]');
  const retry = root.querySelector('[data-appearance-retry]');
  const colors = [
    'primary',
    'secondary',
    'accent',
    'background',
    'text',
    'border',
  ];
  let device = 'desktop';
  let frame;
  let controller;
  let sequence = 0;
  let timer;
  const resize = () => {
    if (!frame) return;
    const width = { desktop: 1280, tablet: 768, mobile: 375 }[device];
    const scale = Math.min(viewport.clientWidth / width, 1);
    frame.style.width = `${width}px`;
    frame.style.height = `${viewport.clientHeight / scale}px`;
    frame.style.transform = `scale(${scale})`;
    frame.style.marginLeft = `${Math.max(0, (viewport.clientWidth - width * scale) / 2)}px`;
  };
  new ResizeObserver(resize).observe(viewport);
  const fontFamily = (key) =>
    key === 'serif' ? "'Noto Serif', serif" : "'Noto Sans', sans-serif";
  const updateSample = () => {
    root.querySelector('[data-heading-sample]').style.fontFamily = fontFamily(
      form.elements.namedItem('heading_font').value,
    );
    const sample = root.querySelector('[data-body-sample]');
    sample.style.fontFamily = fontFamily(
      form.elements.namedItem('body_font').value,
    );
    sample.style.fontSize = `${form.elements.namedItem('font_size').value}px`;
  };
  const render = async () => {
    if (
      [...form.elements].some(
        (input) => input.willValidate && !input.validity.valid,
      )
    )
      return;
    const current = ++sequence;
    controller?.abort();
    controller = new AbortController();
    loading.textContent = loading.dataset.label;
    retry.hidden = true;
    const payload = new FormData(form);
    payload.delete('_method');
    try {
      const response = await fetch(form.dataset.previewUrl, {
        method: 'POST',
        body: payload,
        credentials: 'same-origin',
        cache: 'no-store',
        signal: controller.signal,
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
      const next = document.createElement('iframe');
      next.title = viewport.dataset.previewTitle;
      next.setAttribute('sandbox', '');
      next.hidden = true;
      next.addEventListener(
        'load',
        () => {
          if (current !== sequence) return;
          loading.textContent = '';
          next.hidden = false;
          resize();
        },
        { once: true },
      );
      next.srcdoc = result.html;
      frame = next;
      viewport.replaceChildren(next);
      resize();
    } catch (error) {
      if (error.name === 'AbortError' || current !== sequence) return;
      loading.textContent = root.dataset.error;
      retry.hidden = false;
    }
  };
  loading.dataset.label = loading.textContent;
  const schedule = () => {
    updateSample();
    clearTimeout(timer);
    sequence++;
    controller?.abort();
    timer = setTimeout(render, 400);
  };
  const setPalette = (palette) => {
    colors.forEach((color, index) => {
      form.elements.namedItem(color).value = palette[index];
      root.querySelector(`[data-color-picker="${color}"]`).value =
        palette[index];
    });
    schedule();
  };
  root
    .querySelectorAll('[data-appearance-theme]')
    .forEach((input) =>
      input.addEventListener('change', () =>
        setPalette(JSON.parse(input.dataset.colors)),
      ),
    );
  root
    .querySelectorAll('[data-appearance-palette]')
    .forEach((button) =>
      button.addEventListener('click', () =>
        setPalette(JSON.parse(button.dataset.appearancePalette)),
      ),
    );
  root.querySelectorAll('[data-color-picker]').forEach((input) =>
    input.addEventListener('input', () => {
      form.elements.namedItem(input.dataset.colorPicker).value =
        input.value.toUpperCase();
    }),
  );
  root.querySelectorAll('[data-color-value]').forEach((input) =>
    input.addEventListener('input', () => {
      if (/^#[a-f\d]{6}$/i.test(input.value))
        root.querySelector(
          `[data-color-picker="${input.dataset.colorValue}"]`,
        ).value = input.value;
    }),
  );
  form.addEventListener('input', schedule);
  form.addEventListener('change', schedule);
  form.addEventListener('reset', () =>
    setTimeout(() => {
      colors.forEach((color) => {
        root.querySelector(`[data-color-picker="${color}"]`).value =
          form.elements.namedItem(color).value;
      });
      schedule();
    }, 0),
  );
  root.querySelectorAll('[data-appearance-device]').forEach((button) =>
    button.addEventListener('click', () => {
      device = button.dataset.appearanceDevice;
      root
        .querySelectorAll('[data-appearance-device]')
        .forEach((item) =>
          item.setAttribute('aria-pressed', String(item === button)),
        );
      resize();
    }),
  );
  root.querySelectorAll('[data-appearance-tab]').forEach((tab) =>
    tab.addEventListener('click', () => {
      root
        .querySelectorAll('[data-appearance-tab]')
        .forEach((item) => item.classList.toggle('is-primary', item === tab));
    }),
  );
  retry.addEventListener('click', render);
  updateSample();
  render();
}
