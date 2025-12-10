(() => {
  const STORAGE_KEY = 'emindy-theme';
  const DARK = 'dark';
  const LIGHT = 'light';
  const ROOT = document.documentElement;

  const applyTheme = (root, toggle, theme, persist = true) => {
    const nextTheme = theme === DARK ? DARK : LIGHT;

    root.setAttribute('data-em-theme', nextTheme);
    toggle.setAttribute('aria-pressed', nextTheme === DARK ? 'true' : 'false');

    const label = nextTheme === DARK ? toggle.dataset.labelDark : toggle.dataset.labelLight;
    if (label) {
      toggle.setAttribute('aria-label', label);
    }

    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, nextTheme);
      } catch (error) {
        // Ignore storage write errors (e.g., private mode).
      }
    }
  };

  const init = () => {
    const toggle = document.getElementById('em-dark-mode-toggle');

    if (!ROOT || !toggle) {
      return;
    }

    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    let savedTheme = null;

    try {
      savedTheme = localStorage.getItem(STORAGE_KEY);
    } catch (error) {
      // Ignore storage read errors.
    }

    const initialTheme = savedTheme || ROOT.getAttribute('data-em-theme') || (prefersDark ? DARK : LIGHT);
    applyTheme(ROOT, toggle, initialTheme, Boolean(savedTheme));

    toggle.addEventListener('click', () => {
      const currentTheme = ROOT.getAttribute('data-em-theme') === DARK ? DARK : LIGHT;
      const nextTheme = currentTheme === DARK ? LIGHT : DARK;
      applyTheme(ROOT, toggle, nextTheme);
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
