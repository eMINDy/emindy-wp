(() => {
  const STORAGE_KEY = 'emindy-theme';
  const DARK = 'dark';
  const LIGHT = 'light';

  const applyTheme = (root, toggle, theme) => {
    const nextTheme = theme === DARK ? DARK : LIGHT;

    root.setAttribute('data-em-theme', nextTheme);
    toggle.setAttribute('aria-pressed', nextTheme === DARK ? 'true' : 'false');

    const label = nextTheme === DARK ? toggle.dataset.darkLabel : toggle.dataset.lightLabel;
    if (label) {
      toggle.setAttribute('aria-label', label);
    }

    try {
      localStorage.setItem(STORAGE_KEY, nextTheme);
    } catch (error) {
      // Ignore storage write errors (e.g., private mode).
    }
  };

  const init = () => {
    const root = document.documentElement;
    const toggle = document.getElementById('em-dark-mode-toggle');

    if (!root || !toggle) {
      return;
    }

    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    let savedTheme = null;

    try {
      savedTheme = localStorage.getItem(STORAGE_KEY);
    } catch (error) {
      // Ignore storage read errors.
    }

    const initialTheme = savedTheme || (prefersDark ? DARK : LIGHT);
    applyTheme(root, toggle, initialTheme);

    toggle.addEventListener('click', () => {
      const currentTheme = root.getAttribute('data-em-theme') === DARK ? DARK : LIGHT;
      const nextTheme = currentTheme === DARK ? LIGHT : DARK;
      applyTheme(root, toggle, nextTheme);
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
