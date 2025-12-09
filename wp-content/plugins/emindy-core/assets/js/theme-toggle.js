(function () {
  'use strict';

  var THEME_STORAGE_KEY = 'emindy_theme';
  var ALLOWED_THEMES = ['light', 'dark'];
  var FALLBACK_THEME = 'light';
  var rootElement = document.documentElement;
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

  function sanitizeTheme(theme) {
    return ALLOWED_THEMES.indexOf(theme) !== -1 ? theme : FALLBACK_THEME;
  }

  function persistTheme(theme) {
    try {
      localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {}
  }

  function resolveControlCopy(control, theme) {
    var dataset = control && control.dataset ? control.dataset : {};
    var capitalizedTheme = theme.charAt(0).toUpperCase() + theme.slice(1);

    return {
      ariaLabel: dataset['label' + capitalizedTheme] || TOGGLE_COPY[theme].ariaLabel,
      title: dataset['title' + capitalizedTheme] || TOGGLE_COPY[theme].title,
      icon: dataset['icon' + capitalizedTheme] || TOGGLE_COPY[theme].icon
    };
  }

  function updateToggleControls(normalizedTheme) {
    var isDark = normalizedTheme === 'dark';

    document.querySelectorAll('[data-action="toggle-theme"]').forEach(function (btn) {
      var controlCopy = resolveControlCopy(btn, normalizedTheme);

      btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      btn.setAttribute('aria-label', controlCopy.ariaLabel);
      btn.textContent = controlCopy.icon;
      btn.title = controlCopy.title;
    });
  }

  function apply(theme) {
    var normalized = sanitizeTheme(theme);

    if (!rootElement) {
      return;
    }

    rootElement.setAttribute('data-theme', normalized);
    persistTheme(normalized);
    updateToggleControls(normalized);
  }

  function current() {
    var htmlTheme = rootElement ? rootElement.getAttribute('data-theme') : null;
    return sanitizeTheme(htmlTheme || FALLBACK_THEME);
  }

  function getStoredTheme() {
    try {
      var stored = localStorage.getItem(THEME_STORAGE_KEY);
      return stored ? sanitizeTheme(stored) : null;
    } catch (e) {
      return null;
    }
  }

  function toggle() {
    apply(current() === 'dark' ? 'light' : 'dark');
  }

  // روی کلیک دکمه‌های سوییچ
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-action="toggle-theme"]');
    if (!t) return;
    e.preventDefault();
    toggle();
  });

  // همگام با تغییر سیستم
  try {
    var mm = typeof window.matchMedia === 'function' ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    if (mm) {
      var syncPreferred = function (ev) {
        var stored = getStoredTheme();
        if (!stored) {
          apply(ev.matches ? 'dark' : 'light'); // فقط اگر کاربر دستی عوض نکرده
        }
      };

      if (typeof mm.addEventListener === 'function') {
        mm.addEventListener('change', syncPreferred);
      } else if (typeof mm.addListener === 'function') {
        mm.addListener(syncPreferred);
      }
    }
  } catch (e) {}

  // آماده‌سازی اولیه بعد از DOM
  document.addEventListener('DOMContentLoaded', function () {
    var storedTheme = getStoredTheme();

    if (storedTheme) {
      apply(storedTheme);
      return;
    }

    try {
      var prefersDark = typeof window.matchMedia === 'function' && window.matchMedia('(prefers-color-scheme: dark)').matches;
      apply(prefersDark ? 'dark' : current());
    } catch (e) {
      apply(current());
    }
  });
})();
