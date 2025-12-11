(function () {
  'use strict';

  /**
   * eMINDy Theme Toggle
   * Applies and persists light/dark theme using data-em-theme on <html>.
   * Also keeps any [data-action="toggle-theme"] controls in sync.
   */

  var THEME_STORAGE_KEY = 'emindy_theme';
  var ALLOWED_THEMES = ['light', 'dark'];
  var FALLBACK_THEME = 'light';
  var ATTR_NAME = 'data-em-theme';
  var rootElement = document.documentElement || document.body || null;

  var TOGGLE_COPY = {
    dark: {
      ariaLabel: 'Switch to light mode',
      title: 'Switch to Light',
      icon: '🌞'
    },
    light: {
      ariaLabel: 'Switch to dark mode',
      title: 'Switch to Dark',
      icon: '🌓'
    }
  };

  // -----------------------------
  // Helpers
  // -----------------------------

  function isAllowedTheme(theme) {
    return ALLOWED_THEMES.indexOf(theme) !== -1;
  }

  function sanitizeTheme(theme) {
    if (typeof theme !== 'string') {
      return FALLBACK_THEME;
    }
    var normalized = theme.toLowerCase();
    return isAllowedTheme(normalized) ? normalized : FALLBACK_THEME;
  }

  function getStoredTheme() {
    try {
      var stored = window.localStorage.getItem(THEME_STORAGE_KEY);
      return stored ? sanitizeTheme(stored) : null;
    } catch (e) {
      return null;
    }
  }

  function persistTheme(theme) {
    try {
      window.localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {
      // localStorage might be unavailable (private mode, etc.).
    }
  }

  function getHtmlTheme() {
    if (!rootElement) {
      return null;
    }
    var attr = rootElement.getAttribute(ATTR_NAME);
    return attr ? sanitizeTheme(attr) : null;
  }

  function getSystemPreference() {
    try {
      if (typeof window.matchMedia !== 'function') {
        return null;
      }
      var mq = window.matchMedia('(prefers-color-scheme: dark)');
      if (!mq) {
        return null;
      }
      return mq.matches ? 'dark' : 'light';
    } catch (e) {
      return null;
    }
  }

  function currentTheme() {
    var fromHtml = getHtmlTheme();
    if (fromHtml) {
      return sanitizeTheme(fromHtml);
    }

    var fromStorage = getStoredTheme();
    if (fromStorage) {
      return sanitizeTheme(fromStorage);
    }

    var fromSystem = getSystemPreference();
    if (fromSystem) {
      return sanitizeTheme(fromSystem);
    }

    return FALLBACK_THEME;
  }

  function resolveControlCopy(control, theme) {
    var dataset = control && control.dataset ? control.dataset : {};
    var capitalized = theme.charAt(0).toUpperCase() + theme.slice(1);

    // Support both data-labelLight / data-labelDark etc.
    var labelKey = 'label' + capitalized;
    var titleKey = 'title' + capitalized;
    var iconKey = 'icon' + capitalized;

    return {
      ariaLabel: dataset[labelKey] || TOGGLE_COPY[theme].ariaLabel,
      title: dataset[titleKey] || TOGGLE_COPY[theme].title,
      icon: dataset[iconKey] || TOGGLE_COPY[theme].icon
    };
  }

  function updateToggleControls(normalizedTheme) {
    var isDark = normalizedTheme === 'dark';
    var buttons = document.querySelectorAll('[data-action="toggle-theme"]');

    if (!buttons.length) {
      return;
    }

    Array.prototype.forEach.call(buttons, function (btn) {
      var copy = resolveControlCopy(btn, normalizedTheme);

      // Ensure <button> elements do not submit forms by default.
      if (btn.tagName === 'BUTTON' && !btn.hasAttribute('type')) {
        btn.setAttribute('type', 'button');
      }

      btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      btn.setAttribute('aria-label', copy.ariaLabel);
      btn.title = copy.title;

      // Prefer icon-only buttons: replace textContent.
      // If markup with inner spans is needed, use data attributes and custom JS.
      btn.textContent = copy.icon;
    });
  }

  function dispatchThemeEvent(theme, source) {
    if (!rootElement) {
      return;
    }

    try {
      var ev = new CustomEvent('emindy:theme-change', {
        bubbles: false,
        cancelable: false,
        detail: {
          theme: theme,
          source: source || 'unknown'
        }
      });
      rootElement.dispatchEvent(ev);
    } catch (e) {
      // Older browsers without CustomEvent support can ignore this.
    }
  }

  function applyTheme(theme, options) {
    options = options || {};
    var normalized = sanitizeTheme(theme);

    if (!rootElement) {
      return;
    }

    var previous = getHtmlTheme();

    rootElement.setAttribute(ATTR_NAME, normalized);

    // Persist only when explicitly allowed (defaults to true).
    if (options.persist !== false) {
      persistTheme(normalized);
    }

    if (options.updateControls !== false) {
      updateToggleControls(normalized);
    }

    if (options.emitEvent !== false && previous !== normalized) {
      dispatchThemeEvent(normalized, options.source || 'user');
    }
  }

  function toggleTheme() {
    var next = currentTheme() === 'dark' ? 'light' : 'dark';
    applyTheme(next, { source: 'user', persist: true });
  }

  // -----------------------------
  // Public API (optional)
  // -----------------------------

  var api = {
    get: currentTheme,
    set: function (theme) {
      applyTheme(theme, { source: 'api', persist: true });
    },
    toggle: toggleTheme,
    onChange: function (handler) {
      if (!rootElement || typeof handler !== 'function') {
        return;
      }
      rootElement.addEventListener('emindy:theme-change', function (ev) {
        if (ev && ev.detail && ev.detail.theme) {
          handler(ev.detail.theme, ev.detail);
        }
      });
    }
  };

  try {
    window.emindyTheme = window.emindyTheme || api;
  } catch (e) {
    // In very restricted environments, window might not be writable.
  }

  // -----------------------------
  // Event wiring
  // -----------------------------

  // Click delegation for any toggle control (supports multiple and dynamic buttons).
  document.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest
      ? e.target.closest('[data-action="toggle-theme"]')
      : null;

    if (!btn) {
      return;
    }

    e.preventDefault();
    toggleTheme();
  });

  // React to system preference changes only if user has not chosen manually.
  (function wireSystemPreferenceListener() {
    var mq;
    try {
      mq = typeof window.matchMedia === 'function'
        ? window.matchMedia('(prefers-color-scheme: dark)')
        : null;
    } catch (e) {
      mq = null;
    }

    if (!mq) {
      return;
    }

    var handler = function (ev) {
      // If user has chosen a theme (stored), respect that and ignore system changes.
      if (getStoredTheme()) {
        return;
      }
      var theme = ev.matches ? 'dark' : 'light';
      applyTheme(theme, { source: 'system', persist: false });
    };

    if (typeof mq.addEventListener === 'function') {
      mq.addEventListener('change', handler);
    } else if (typeof mq.addListener === 'function') {
      // Legacy API
      mq.addListener(handler);
    }
  })();

  // -----------------------------
  // Initialisation
  // -----------------------------

  // Apply initial theme as early as possible to reduce flash:
  // 1) stored preference
  // 2) existing data-em-theme attribute
  // 3) system preference
  // 4) fallback
  (function initialApply() {
    var stored = getStoredTheme();
    var initial =
      stored ||
      getHtmlTheme() ||
      getSystemPreference() ||
      FALLBACK_THEME;

    applyTheme(initial, {
      updateControls: false, // controls may not exist yet
      emitEvent: false,
      source: stored ? 'stored' : 'init',
      // Do NOT persist here if it comes from system or fallback:
      // only user actions should create a stored preference.
      persist: !!stored
    });
  })();

  // Once DOM is ready, sync actual controls in the document.
  document.addEventListener('DOMContentLoaded', function () {
    updateToggleControls(currentTheme());
  });
})();
