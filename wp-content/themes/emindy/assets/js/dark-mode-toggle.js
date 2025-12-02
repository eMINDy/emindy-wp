/*
 * eMINDy Dark Mode Toggle
 *
 * This script toggles between light and dark themes by setting a data attribute
 * on the <html> element. The user’s choice is persisted in localStorage so
 * their preference is remembered across sessions. If no preference is stored
 * the script falls back to the system’s prefers-color-scheme media query.
 */

(function(){
  // Select the root element (documentElement covers <html>)
  const root = document.documentElement;

  /**
   * Apply a theme by toggling the data-em-theme attribute on the root. All
   * CSS variables are defined relative to this attribute in style.css.
   *
   * @param {string} theme 'light' or 'dark'
   */
  function applyTheme(theme) {
    if (theme === 'dark') {
      root.setAttribute('data-em-theme', 'dark');
    } else {
      root.setAttribute('data-em-theme', 'light');
    }
  }

  // Determine the initial theme: check localStorage, otherwise system preference
  let current = localStorage.getItem('emTheme');
  if (!current) {
    current = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  applyTheme(current);

  // When the document is fully loaded, attach a click handler to the toggle
  window.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('em-dark-mode-toggle');
    if (!toggle) return;

    // Update the button label based on current theme
    const darkLabel  = toggle.getAttribute('data-dark-label')  || 'Light';
    const lightLabel = toggle.getAttribute('data-light-label') || 'Dark';
    toggle.textContent = (current === 'dark') ? darkLabel : lightLabel;

    toggle.addEventListener('click', () => {
      // Switch themes
      current = (current === 'dark') ? 'light' : 'dark';
      applyTheme(current);
      localStorage.setItem('emTheme', current);
      // Update button label
      toggle.textContent = (current === 'dark') ? darkLabel : lightLabel;
    });
  });
})();