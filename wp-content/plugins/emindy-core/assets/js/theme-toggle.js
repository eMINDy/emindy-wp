(function () {
  'use strict';

  var THEME_STORAGE_KEY = 'emindy_theme';
  var ALLOWED_THEMES = ['light', 'dark'];

  function sanitizeTheme(theme) {
    return ALLOWED_THEMES.indexOf(theme) !== -1 ? theme : 'light';
  }

  function apply(theme) {
    var normalized = sanitizeTheme(theme);
    document.documentElement.setAttribute('data-theme', normalized);

    try {
      localStorage.setItem(THEME_STORAGE_KEY, normalized);
    } catch (e) {}

    document.querySelectorAll('[data-action="toggle-theme"]').forEach(function (btn) {
      var isDark = normalized === 'dark';

      btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      btn.textContent = isDark ? '🌞' : '🌓';
      btn.title = isDark ? 'Switch to Light' : 'Switch to Dark';
    });
  }

  function current() {
    var htmlTheme = document.documentElement.getAttribute('data-theme');
    return sanitizeTheme(htmlTheme || 'light');
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
    var mm = window.matchMedia('(prefers-color-scheme: dark)');
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
  } catch (e) {}

  // آماده‌سازی اولیه بعد از DOM
  document.addEventListener('DOMContentLoaded', function () {
    var storedTheme = getStoredTheme();

    if (storedTheme) {
      apply(storedTheme);
      return;
    }

    try {
      var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      apply(prefersDark ? 'dark' : current());
    } catch (e) {
      apply(current());
    }
  });
})();
