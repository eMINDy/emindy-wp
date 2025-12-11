// Shared helpers for assessments
window.emindyAssess = window.emindyAssess || {};

(function (NS, root) {
  'use strict';

  if (!NS) {
    return;
  }

  if (typeof root.fetch !== 'function') {
    // Graceful no-op for environments without fetch.
    return;
  }

  /**
   * Default form headers for AJAX requests.
   * @type {Readonly<Record<string, string>>}
   */
  const FORM_HEADERS = Object.freeze({
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
  });

  /**
   * Lightweight i18n helper.
   * Allows other scripts to override UI strings via `window.emindyAssess.i18n`.
   *
   * Example:
   * window.emindyAssess.i18n = {
   *   enterEmail: 'ایمیل خود را وارد کنید:',
   *   invalidEmail: 'لطفاً یک ایمیل معتبر وارد کنید.',
   *   sent: 'ارسال شد ✔',
   *   failed: 'ارسال ناموفق بود',
   *   missingEndpoint: 'نقطهٔ ارسال در دسترس نیست.',
   *   requestFailed: 'درخواست ناموفق بود.',
   *   invalidResponse: 'پاسخ سرور معتبر نیست.',
   *   signFailed: 'امضای لینک ناموفق بود.',
   * };
   */
  const i18n = NS.i18n || {};

  /**
   * Resolve a localized string with fallback.
   * @param {string} key
   * @param {string} fallback
   * @returns {string}
   */
  function t(key, fallback) {
    if (i18n && typeof i18n[key] === 'string' && i18n[key]) {
      return i18n[key];
    }
    return fallback;
  }

  /**
   * Get the configured AJAX URL.
   * @returns {string}
   */
  function getAjaxURL() {
    return typeof NS.ajax === 'string' && NS.ajax ? NS.ajax : '';
  }

  /**
   * Get the configured nonce.
   * @returns {string}
   */
  function getNonce() {
    return typeof NS.nonce === 'string' ? NS.nonce : '';
  }

  /**
   * Build URLSearchParams from a plain object, skipping undefined values.
   * @param {Record<string, any>} params
   * @returns {URLSearchParams}
   */
  function buildBody(params) {
    const body = new URLSearchParams();

    if (!params || typeof params !== 'object') {
      return body;
    }

    Object.keys(params).forEach(function (key) {
      const value = params[key];
      if (typeof value !== 'undefined' && value !== null) {
        body.append(key, String(value));
      }
    });

    return body;
  }

  /**
   * Send a POST request to the configured AJAX endpoint and parse JSON.
   * @param {Record<string, string|number>} params Request parameters.
   * @returns {Promise<any>} Parsed JSON response.
   */
  async function sendRequest(params) {
    const ajaxURL = getAjaxURL();

    if (!ajaxURL) {
      throw new Error(t('missingEndpoint', 'Missing AJAX endpoint'));
    }

    const response = await root.fetch(ajaxURL, {
      method: 'POST',
      headers: FORM_HEADERS,
      credentials: 'same-origin',
      body: buildBody(params),
    });

    if (!response.ok) {
      throw new Error(
        t('requestFailed', 'Request failed') + ' (' + response.status + ')'
      );
    }

    try {
      return await response.json();
    } catch (error) {
      throw new Error(t('invalidResponse', 'Invalid server response'));
    }
  }

  /**
   * Sign and return a shareable URL for an assessment score.
   * @param {string} type Assessment type key.
   * @param {string|number} score Numeric score to sign.
   * @returns {Promise<string>} Signed URL string.
   */
  async function signURL(type, score) {
    try {
      const safeType = typeof type === 'string' ? type : '';
      const safeScore =
        typeof score === 'number' || typeof score === 'string' ? score : '';

      const j = await sendRequest({
        action: 'emindy_sign_result',
        _ajax_nonce: getNonce(),
        type: safeType,
        score: safeScore,
      });

      if (j && j.success && j.data && j.data.url) {
        return j.data.url;
      }
    } catch (e) {
      console.error(e);
    }

    throw new Error(t('signFailed', 'Sign failed'));
  }

  /**
   * Prompt for an email address and request the assessment summary be sent.
   * @param {string} kind Assessment kind.
   * @param {string} summary Summary body text.
   * @returns {Promise<{ok?:boolean,cancel?:boolean}>} Result status.
   */
  async function emailSummary(kind, summary) {
    const emailInput = root.prompt(t('enterEmail', 'Enter your email:'));
    const email = typeof emailInput === 'string' ? emailInput.trim() : '';

    if (!email) {
      return { cancel: true };
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      root.alert(t('invalidEmail', 'Please enter a valid email.'));
      return { cancel: true };
    }

    try {
      const safeKind = typeof kind === 'string' ? kind : '';
      const safeSummary =
        typeof summary === 'string' ? summary : String(summary ?? '');

      const j = await sendRequest({
        action: 'emindy_send_assessment',
        _ajax_nonce: getNonce(),
        kind: safeKind,
        summary: safeSummary,
        email: email,
      });

      if (j && j.success) {
        root.alert(t('sent', 'Sent ✔'));
        return { ok: true };
      }

      root.alert(
        (j && j.data ? String(j.data) : '') ||
          t('failed', 'Failed')
      );
    } catch (e) {
      console.error(e);
      root.alert(t('failed', 'Failed'));
    }

    return { ok: false };
  }

  /**
   * Track an assessment-related interaction via AJAX.
   * Fire-and-forget; errors are logged only.
   *
   * @param {string} type Event type.
   * @param {string} [label]
   * @param {string|number} [value]
   * @param {number} [post]
   * @returns {Promise<void>}
   */
  async function track(type, label = '', value = '', post = 0) {
    if (!type) {
      return;
    }

    const ajaxURL = getAjaxURL();
    if (!ajaxURL) {
      return;
    }

    try {
      await root.fetch(ajaxURL, {
        method: 'POST',
        headers: FORM_HEADERS,
        credentials: 'same-origin',
        body: buildBody({
          action: 'emindy_track',
          _ajax_nonce: getNonce(),
          type: typeof type === 'string' ? type : '',
          label: typeof label === 'string' ? label : '',
          value:
            typeof value === 'string' || typeof value === 'number'
              ? value
              : '',
          post: Number.isFinite(post) ? post : 0,
        }),
      });
    } catch (e) {
      console.error(e);
    }
  }

  // Export helpers on the namespace (merging with any existing helpers).
  const helpers = NS.helpers || {};

  helpers.sendRequest = sendRequest;
  helpers.signURL = signURL;
  helpers.emailSummary = emailSummary;
  helpers.track = track;

  NS.helpers = helpers;
})(window.emindyAssess, window);
