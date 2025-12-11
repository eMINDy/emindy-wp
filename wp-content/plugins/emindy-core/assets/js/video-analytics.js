(function (window, document) {
  'use strict';

  if (!window || !document || !document.addEventListener) {
    return;
  }

  /**
   * Delegated event listener helper.
   *
   * @param {string} eventName Event name to listen for.
   * @param {string} selector  CSS selector to match against event targets.
   * @param {Function} callback Callback executed when selector matches.
   * @param {AddEventListenerOptions|boolean} [options] addEventListener options.
   */
  function addDelegatedListener(eventName, selector, callback, options) {
    if (!eventName || typeof selector !== 'string' || typeof callback !== 'function') {
      return;
    }

    var opts = options;
    if (typeof opts === 'undefined') {
      // Use capture to ensure we catch events even if other handlers stop propagation.
      opts = { capture: true };
    }

    document.addEventListener(
      eventName,
      function (event) {
        var target = event && event.target ? event.target : null;

        if (!(target instanceof Element)) {
          return;
        }

        var matched = closestMatch(target, selector);
        if (!matched) {
          return;
        }

        callback(event, matched);
      },
      opts
    );
  }

  /**
   * Find the closest ancestor (including the element itself) matching selector.
   * Includes a small fallback for older browsers without Element.closest.
   *
   * @param {Element} el
   * @param {string} selector
   * @returns {Element|null}
   */
  function closestMatch(el, selector) {
    if (!el || !selector) {
      return null;
    }

    if (typeof el.closest === 'function') {
      return el.closest(selector);
    }

    var node = el;
    while (node && node.nodeType === 1) {
      if (node.matches && node.matches(selector)) {
        return node;
      }
      node = node.parentElement;
    }

    return null;
  }

  /**
   * Convert a value to a safe string for analytics.
   *
   * @param {*} value
   * @returns {string}
   */
  function sanitizeTrackValue(value) {
    if (value === undefined || value === null) {
      return '';
    }

    if (typeof value === 'string') {
      return value.trim();
    }

    try {
      return String(value).trim();
    } catch (error) {
      return '';
    }
  }

  /**
   * Resolve current post ID for analytics.
   *
   * Prioritises:
   *   1. window.em_post_id
   *   2. data-em-post-id on <html> or <body>
   *
   * @returns {number}
   */
  function getPostId() {
    var id = window.em_post_id;

    if (typeof id === 'number' && !isNaN(id) && id > 0) {
      return id;
    }

    if (typeof id === 'string' && id) {
      var parsed = parseInt(id, 10);
      if (!isNaN(parsed) && parsed > 0) {
        return parsed;
      }
    }

    var html = document.documentElement;
    var body = document.body;
    var attr =
      (html && html.getAttribute && html.getAttribute('data-em-post-id')) ||
      (body && body.getAttribute && body.getAttribute('data-em-post-id')) ||
      '';

    if (attr) {
      var parsedAttr = parseInt(attr, 10);
      if (!isNaN(parsedAttr) && parsedAttr > 0) {
        return parsedAttr;
      }
    }

    return 0;
  }

  /**
   * Send a tracking event via emindyAssess helpers if available.
   *
   * Expected signature of helpers.track:
   *   track(action, label, value, postId)
   *
   * @param {string} action Event type / action key.
   * @param {string} label  Human-readable label.
   * @param {string|number} value Numeric or string value.
   */
  function trackEvent(action, label, value) {
    var assess = window.emindyAssess;
    var helpers = assess && assess.helpers;
    var track = helpers && typeof helpers.track === 'function' ? helpers.track : null;

    if (!track) {
      return;
    }

    var safeAction = sanitizeTrackValue(action);
    if (!safeAction) {
      // Server requires a non-empty type/action; avoid pointless calls.
      return;
    }

    var safeLabel = sanitizeTrackValue(label);
    var safeValue = sanitizeTrackValue(value);
    var postId = getPostId();

    try {
      track(safeAction, safeLabel, safeValue, postId);
    } catch (error) {
      // Fail silently to avoid impacting UX when analytics tracking is unavailable.
    }
  }

  // ---------------------------------------
  // Video-related analytics bindings
  // ---------------------------------------

  var SELECTORS = Object.freeze({
    lyte: '.lyte, .lyte-wrapper',
    chapters: '.em-chapters a',
    transcriptCopy: '.em-transcript__copy'
  });

  var EVENT_TYPES = Object.freeze({
    videoPlay: 'video_play',
    chapterClick: 'chapter_click',
    transcriptCopy: 'transcript_copy'
  });

  /**
   * Track click on Lyte placeholder (video play intent).
   */
  addDelegatedListener('click', SELECTORS.lyte, function (_event, el) {
    var provider =
      (el && typeof el.getAttribute === 'function'
        ? el.getAttribute('data-em-video-provider')
        : '') || 'lyte';

    trackEvent(EVENT_TYPES.videoPlay, provider, '1');
  });

  /**
   * Track clicks on chapter links within .em-chapters.
   */
  addDelegatedListener('click', SELECTORS.chapters, function (_event, link) {
    var text = (link.textContent || '').trim();
    var href = link instanceof HTMLAnchorElement ? link.href : '';

    // Prefer explicit data attributes if present (e.g., data-em-chapter, data-em-time).
    var label =
      (link && link.getAttribute && link.getAttribute('data-em-chapter')) || text;
    var value =
      (link && link.getAttribute && link.getAttribute('data-em-time')) || href;

    trackEvent(EVENT_TYPES.chapterClick, label, value);
  });

  /**
   * Track transcript copy button usage.
   */
  addDelegatedListener('click', SELECTORS.transcriptCopy, function () {
    trackEvent(EVENT_TYPES.transcriptCopy, '', '1');
  });
})(window, document);
