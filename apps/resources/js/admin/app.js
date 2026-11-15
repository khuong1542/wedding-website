import '../bootstrap';
import './toast';

const admin = document.querySelector('[data-admin]');

if (admin) {
  const toggle = admin.querySelector('[data-sidebar-toggle]');
  const rootItems = admin.querySelectorAll(
    '.admin-nav-group > [data-menu-level] > [data-menu-item]',
  );
  const setOpen = (item, open) => {
    const button = item.querySelector(':scope > [data-menu-toggle]');
    const panel = item.querySelector(':scope > .admin-submenu');
    if (!button || !panel) return;
    button.setAttribute('aria-expanded', String(open));
    panel.inert = !open;
    item.classList.toggle('is-open', open);
  };
  const closeSiblings = (item) => {
    [...item.parentElement.children].forEach((sibling) => {
      if (sibling !== item) setOpen(sibling, false);
    });
  };
  const closeFlyouts = () => {
    [...rootItems].forEach((item) => {
      item.classList.remove('is-hovered');
      setOpen(item, false);
    });
  };
  toggle.addEventListener('click', () => {
    const collapsed = admin.classList.toggle('is-collapsed');
    closeFlyouts();
    toggle.setAttribute('aria-expanded', String(!collapsed));
    toggle.setAttribute(
      'aria-label',
      collapsed ? toggle.dataset.labelExpand : toggle.dataset.labelCollapse,
    );
  });
  admin.querySelectorAll('[data-menu-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const item = button.parentElement;
      const open = button.getAttribute('aria-expanded') !== 'true';
      closeSiblings(item);
      setOpen(item, open);
    });
  });
  [...rootItems].forEach((item) => {
    const reveal = () => {
      if (!admin.classList.contains('is-collapsed')) return;
      closeSiblings(item);
      const rect = item.getBoundingClientRect();
      item.style.setProperty(
        '--flyout-top',
        `${Math.max(8, Math.min(rect.top, window.innerHeight - 300))}px`,
      );
      item.classList.add('is-hovered');
      setOpen(item, true);
    };
    const hide = () => {
      if (!admin.classList.contains('is-collapsed')) return;
      item.classList.remove('is-hovered');
      setOpen(item, false);
    };
    item.addEventListener('mouseenter', reveal);
    item.addEventListener('mouseleave', hide);
    item.addEventListener('focusin', reveal);
    item.addEventListener('focusout', (event) => {
      if (!item.contains(event.relatedTarget)) hide();
    });
  });
  admin.querySelector('.admin-navigation').addEventListener('scroll', () => {
    if (admin.classList.contains('is-collapsed')) closeFlyouts();
  });
  window.addEventListener('resize', () => {
    if (admin.classList.contains('is-collapsed')) closeFlyouts();
  });
  admin.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const item = event.target.closest('[data-menu-item].is-open');
    if (item) {
      item.querySelector(':scope > [data-menu-toggle]').focus();
      setOpen(item, false);
      item.classList.remove('is-hovered');
    }
  });
  admin.querySelectorAll('[aria-current="page"]').forEach((link) => {
    let item = link.parentElement.parentElement.closest('[data-menu-item]');
    while (item) {
      setOpen(item, true);
      item = item.parentElement.closest('[data-menu-item]');
    }
  });
  if (window.matchMedia('(max-width: 600px)').matches) toggle.click();

  const scrollToActiveModule = async () => {
    const navigation = admin.querySelector('.admin-navigation');
    const activeLink = navigation.querySelector('[aria-current="page"]');
    if (!activeLink) return;
    await document.fonts.ready;
    // Wait for ancestor menus to finish expanding before measuring the link.
    await new Promise((resolve) => requestAnimationFrame(resolve));
    await Promise.allSettled(
      navigation
        .getAnimations({ subtree: true })
        .map((animation) => animation.finished),
    );
    const target = admin.classList.contains('is-collapsed')
      ? [...rootItems]
          .find((item) => item.contains(activeLink))
          ?.querySelector(':scope > .admin-menu-link')
      : activeLink;
    if (!target) return;
    const containerRect = navigation.getBoundingClientRect();
    const targetRect = target.getBoundingClientRect();
    navigation.scrollTop +=
      targetRect.top +
      targetRect.height / 2 -
      (containerRect.top + navigation.clientHeight / 2);
  };
  if (document.readyState === 'complete') scrollToActiveModule();
  else window.addEventListener('load', scrollToActiveModule, { once: true });
  new MutationObserver(() => scrollToActiveModule()).observe(
    admin.querySelector('.admin-navigation'),
    { subtree: true, attributes: true, attributeFilter: ['aria-current'] },
  );

  const themeButtons = [...admin.querySelectorAll('[data-theme-option]')];
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  let theme = document.documentElement.dataset.themePreference || 'system';
  const applyTheme = () => {
    document.documentElement.dataset.theme =
      theme === 'system' ? (media.matches ? 'dark' : 'light') : theme;
    document.documentElement.dataset.themePreference = theme;
    themeButtons.forEach((button) => {
      button.setAttribute(
        'aria-pressed',
        String(button.dataset.themeOption === theme),
      );
    });
  };
  themeButtons.forEach((button) =>
    button.addEventListener('click', () => {
      theme = button.dataset.themeOption;
      applyTheme();
      try {
        localStorage.setItem('wedding-admin-theme', theme);
      } catch {}
    }),
  );
  applyTheme();
  media.addEventListener('change', applyTheme);
  window.addEventListener('storage', (event) => {
    if (event.key !== 'wedding-admin-theme') return;
    theme = ['system', 'light', 'dark'].includes(event.newValue)
      ? event.newValue
      : 'system';
    applyTheme();
  });
  const languageToggle = admin.querySelector('[data-language-toggle]');
  const languagePanel = admin.querySelector('#admin-language-panel');
  const closeLanguage = () => {
    languagePanel.hidden = true;
    languageToggle.setAttribute('aria-expanded', 'false');
  };
  languageToggle.addEventListener('click', () => {
    languagePanel.hidden = !languagePanel.hidden;
    languageToggle.setAttribute('aria-expanded', String(!languagePanel.hidden));
  });
  languageToggle.addEventListener('keydown', (event) => {
    if (event.key !== 'ArrowDown') return;
    event.preventDefault();
    languagePanel.hidden = false;
    languageToggle.setAttribute('aria-expanded', 'true');
    languagePanel.querySelector('a').focus();
  });
  document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-language]')) closeLanguage();
  });
  document.addEventListener('focusin', (event) => {
    if (!event.target.closest('[data-language]')) closeLanguage();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !languagePanel.hidden) {
      closeLanguage();
      languageToggle.focus();
    }
  });
  const notificationToggle = admin.querySelector('[data-notification-toggle]');
  const notificationPanel = admin.querySelector('#admin-notification-panel');
  const closeNotifications = () => {
    notificationPanel.hidden = true;
    notificationToggle.setAttribute('aria-expanded', 'false');
  };
  notificationToggle.addEventListener('click', () => {
    notificationPanel.hidden = !notificationPanel.hidden;
    notificationToggle.setAttribute(
      'aria-expanded',
      String(!notificationPanel.hidden),
    );
  });
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.admin-notifications')) closeNotifications();
  });
  document.addEventListener('focusin', (event) => {
    if (!event.target.closest('.admin-notifications')) closeNotifications();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !notificationPanel.hidden) {
      closeNotifications();
      notificationToggle.focus();
    }
  });
}
