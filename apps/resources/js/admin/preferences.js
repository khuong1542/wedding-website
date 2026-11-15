(() => {
  let preference = 'system';
  try {
    const saved = localStorage.getItem('wedding-admin-theme');
    if (['system', 'light', 'dark'].includes(saved)) preference = saved;
  } catch {}
  document.documentElement.dataset.themePreference = preference;
  document.documentElement.dataset.theme =
    preference === 'system'
      ? window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light'
      : preference;
})();
