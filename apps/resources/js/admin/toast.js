const region = document.querySelector('[data-toast-region]');
const types = {
  success: 'check-circle',
  error: 'x-circle',
  info: 'info-circle',
  warning: 'error',
};

export function showToast(message, type = 'info', duration = 5000) {
  if (!region || typeof message !== 'string' || !message.trim()) return;
  if (!Object.hasOwn(types, type)) type = 'info';
  duration = Number.isFinite(duration) ? Math.max(1000, duration) : 5000;
  const toast = document.createElement('div');
  toast.className = `admin-toast admin-toast-${type}`;
  toast.setAttribute(
    'role',
    type === 'error' || type === 'warning' ? 'alert' : 'status',
  );
  const icon = document.createElement('i');
  icon.className = `bx bx-${types[type]} admin-toast-icon`;
  icon.setAttribute('aria-hidden', 'true');
  const content = document.createElement('div');
  content.className = 'admin-toast-content';
  const title = document.createElement('strong');
  title.textContent = region.dataset[type];
  const text = document.createElement('p');
  text.textContent = message;
  content.append(title, text);
  const close = document.createElement('button');
  close.type = 'button';
  close.className = 'admin-toast-close';
  close.setAttribute('aria-label', region.dataset.close);
  close.innerHTML = '<i class="bx bx-x" aria-hidden="true"></i>';
  const progress = document.createElement('span');
  progress.className = 'admin-toast-progress';
  progress.setAttribute('aria-hidden', 'true');
  toast.append(icon, content, close, progress);
  let remaining = duration;
  let previous = performance.now();
  let hovered = false;
  let focused = false;
  let frame;
  const dismiss = () => {
    cancelAnimationFrame(frame);
    toast.remove();
  };
  toast.dismiss = dismiss;
  close.addEventListener('click', dismiss);
  toast.addEventListener('mouseenter', () => {
    hovered = true;
  });
  toast.addEventListener('mouseleave', () => {
    hovered = false;
    previous = performance.now();
  });
  toast.addEventListener('focusin', () => {
    focused = true;
  });
  toast.addEventListener('focusout', (event) => {
    focused = toast.contains(event.relatedTarget);
    previous = performance.now();
  });
  toast.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') dismiss();
  });
  const tick = (now) => {
    if (!hovered && !focused) remaining -= now - previous;
    previous = now;
    progress.style.transform = `scaleX(${Math.max(0, remaining / duration)})`;
    if (remaining <= 0) dismiss();
    else frame = requestAnimationFrame(tick);
  };
  if (region.children.length >= 5) region.firstElementChild.dismiss();
  region.append(toast);
  frame = requestAnimationFrame(tick);
  return dismiss;
}

window.toast = Object.fromEntries(
  Object.keys(types).map((type) => [
    type,
    (message, duration) => showToast(message, type, duration),
  ]),
);

document.querySelectorAll('[data-toast-message]').forEach((seed) => {
  showToast(seed.dataset.toastMessage, seed.dataset.toastType);
  seed.remove();
});
