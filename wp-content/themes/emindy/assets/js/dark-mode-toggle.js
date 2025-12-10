(() => {
  'use strict';

  const STORAGE_KEY = 'emindy-theme';
  const THEME_ATTR = 'data-em-theme';
  const DARK = 'dark';
  const LIGHT = 'light';
  const ROOT = document.documentElement;

  let toggles = [];

  const readStoredTheme = () => {
    try {
      const value = localStorage.getItem(STORAGE_KEY);
      if (value === DARK || value === LIGHT) {
        return value;
      }
    } catch (error) {
      // Ignore storage read errors (e.g., private mode).
    }
    return null;
  };

  const getLabelsFor = (toggle) => {
    if (!toggle || !toggle.dataset) {
      return { light: '', dark: '' };
    }

    const dark =
      toggle.dataset.labelDark ||
      toggle.dataset.darkLabel ||
      '';
    const light =
      toggle.dataset.labelLight ||
      toggle.dataset.lightLabel ||
      '';

    return { light, dark };
  };

  const syncToggleState = (theme) => {
    const isDark = theme === DARK;

    toggles.forEach((toggle) => {
      if (!toggle) {
        return;
      }

      toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');

      const labels = getLabelsFor(toggle);
      const label = isDark
        ? (labels.dark || labels.light)
        : (labels.light || labels.dark);

      if (label) {
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
      }
    });
  };

  const applyTheme = (theme, persist = true) => {
    const nextTheme = theme === DARK ? DARK : LIGHT;

    if (!ROOT) {
      return;
    }

    ROOT.setAttribute(THEME_ATTR, nextTheme);

    // Hint to the browser for native UI styling.
    try {
      ROOT.style.colorScheme = nextTheme;
    } catch (error) {
      // Older browsers may not support colorScheme; ignore.
    }

    syncToggleState(nextTheme);

    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, nextTheme);
      } catch (error) {
        // Ignore storage write errors (e.g., private mode).
      }
    }
  };

  const detectInitialTheme = () => {
    const stored = readStoredTheme();
    if (stored) {
      return { theme: stored, fromStorage: true };
    }

    if (ROOT) {
      const attrTheme = ROOT.getAttribute(THEME_ATTR);
      if (attrTheme === DARK || attrTheme === LIGHT) {
        return { theme: attrTheme, fromStorage: false };
      }
    }

    let prefersDark = false;
    try {
      if (window.matchMedia) {
        prefersDark = window
          .matchMedia('(prefers-color-scheme: dark)')
          .matches;
      }
    } catch (error) {
      // Ignore prefers-color-scheme errors.
    }

    return { theme: prefersDark ? DARK : LIGHT, fromStorage: false };
  };

  const handleToggleClick = () => {
    const current =
      ROOT && ROOT.getAttribute(THEME_ATTR) === DARK ? DARK : LIGHT;
    const next = current === DARK ? LIGHT : DARK;
    applyTheme(next, true);
  };

  const init = () => {
    if (!ROOT) {
      return;
    }

    // Support both the canonical data-action API and the legacy ID.
    toggles = Array.from(
      document.querySelectorAll(
        '[data-action="toggle-theme"], #em-dark-mode-toggle'
      )
    );

    const { theme, fromStorage } = detectInitialTheme();
    applyTheme(theme, fromStorage);

    if (toggles.length) {
      toggles.forEach((toggle) => {
        toggle.addEventListener('click', handleToggleClick);
      });
    }

    // If no explicit user choice yet, follow system preference changes.
    if (!fromStorage && window.matchMedia) {
      try {
        const mediaQuery = window.matchMedia(
          '(prefers-color-scheme: dark)'
        );
        const listener = (event) => {
          const nextTheme = event.matches ? DARK : LIGHT;
          applyTheme(nextTheme, false);
        };

        if (typeof mediaQuery.addEventListener === 'function') {
          mediaQuery.addEventListener('change', listener);
        } else if (typeof mediaQuery.addListener === 'function') {
          mediaQuery.addListener(listener);
        }
      } catch (error) {
        // Ignore matchMedia listener errors.
      }
    }

    // Keep multiple tabs/windows in sync.
    window.addEventListener('storage', (event) => {
      if (event.key !== STORAGE_KEY) {
        return;
      }

      const newValue = event.newValue;
      if (newValue === DARK || newValue === LIGHT) {
        applyTheme(newValue, false);
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
