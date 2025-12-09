(function (window, document) {
  'use strict';

  /**
   * Add delegated event listener with capture to support existing behavior.
   *
   * @param {string} eventName Event name to listen for.
   * @param {string} selector  CSS selector to match against event targets.
   * @param {Function} callback Callback executed when selector matches.
   */
  function addDelegatedListener(eventName, selector, callback) {
    if (!eventName || typeof selector !== 'string' || typeof callback !== 'function') {
      return;
    }

    document.addEventListener(
      eventName,
      function (event) {
        const target = event.target instanceof Element ? event.target.closest(selector) : null;

        if (target) {
          callback(event, target);
        }
      },
      { capture: true }
    );
  }

  /**
   * Track events if emindyAssess helpers are available.
   *
   * @param {string} action Action name for analytics.
   * @param {string} label  Label value associated with the action.
   * @param {string} value  Numeric value or stringified metric.
   */
  function sanitizeTrackValue(value) {
    if (typeof value === 'undefined' || value === null) {
      return '';
    }

    if (typeof value === 'string') {
      return value;
    }

    try {
      return String(value);
    } catch (error) {
      return '';
    }
  }

  function trackEvent(action, label, value) {
    const assess = window.emindyAssess;
    const helpers = assess && assess.helpers;
    const track = helpers && typeof helpers.track === 'function' ? helpers.track : null;

    if (!track) {
      return;
    }

    const safeAction = sanitizeTrackValue(action);
    const safeLabel = sanitizeTrackValue(label);
    const safeValue = sanitizeTrackValue(value);

    try {
      track(safeAction, safeLabel, safeValue, window.em_post_id || 0);
    } catch (error) {
      // Fail silently to avoid impacting UX when analytics tracking is unavailable.
    }
  }

  // Lyte: کلیک روی placeholder (div.lyte)
  addDelegatedListener('click', '.lyte, .lyte-wrapper', function () {
    trackEvent('video_play', 'lyte', '1');
  });

  // Chapters: لینک‌ها داخل .em-chapters
  addDelegatedListener('click', '.em-chapters a', function (event, link) {
    const linkText = (link.textContent || '').trim();
    const linkHref = link instanceof HTMLAnchorElement ? link.href : '';

    trackEvent('chapter_click', linkText, linkHref);
  });

  // Transcript copy: دکمه
  addDelegatedListener('click', '.em-transcript__copy', function () {
    trackEvent('transcript_copy', '', '1');
  });
})(window, document);
