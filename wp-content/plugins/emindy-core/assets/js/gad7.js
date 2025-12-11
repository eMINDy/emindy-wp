(function () {
  'use strict';

  const KIND = 'gad7';

  const SELECTORS = Object.freeze({
    form: 'form.em-gad7',
    result: '.em-phq9__result',
    score: '.em-phq9__score',
    reset: '.em-phq9__reset',
    print: '.em-phq9__print',
    copy: '.em-phq9__copy',
    share: '.em-phq9__sharelink',
    email: '.em-phq9__email',
  });

  const UI_TEXT = Object.freeze({
    pleaseComplete: 'Please complete the form first.',
    shareUnavailable: 'Share link service unavailable.',
    shareCopied: 'Link copied',
    shareFailed: 'Failed to create link',
    emailUnavailable: 'Email service unavailable.',
    copyButtonDefault: 'Copy summary',
    copyButtonSuccess: 'Copied ✔',
  });

  /**
   * Shorthand query helper.
   * @param {string} sel
   * @param {Document|Element} [root=document]
   * @returns {Element|null}
   */
  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  /**
   * Return an array of elements matching the selector.
   * @param {string} sel
   * @param {Document|Element} [root=document]
   * @returns {Element[]}
   */
  function $all(sel, root) {
    return Array.prototype.slice.call(
      (root || document).querySelectorAll(sel)
    );
  }

  /**
   * Safe accessor for emindyAssess helpers namespace.
   * @returns {{track?:Function, signURL?:Function, emailSummary?:Function}|null}
   */
  function getHelpers() {
    if (
      typeof window !== 'undefined' &&
      window.emindyAssess &&
      window.emindyAssess.helpers
    ) {
      return window.emindyAssess.helpers;
    }
    return null;
  }

  /**
   * Track an assessment-related event, if tracking is available.
   * @param {string} eventName
   * @param {string} value
   */
  function trackEvent(eventName, value) {
    const helpers = getHelpers();
    if (!helpers || typeof helpers.track !== 'function') {
      return;
    }
    try {
      helpers.track(eventName, KIND, value, window.em_post_id || 0);
    } catch (error) {
      // Tracking is non-blocking.
    }
  }

  /**
   * Map GAD-7 score to severity band.
   * @param {number} score
   * @returns {{band:string, advice:string, severity:'minimal'|'mild'|'moderate'|'severe'}}
   */
  function scoreToBand(score) {
    if (score <= 4) {
      return {
        band: 'Minimal',
        advice: 'Light routines may help.',
        severity: 'minimal',
      };
    }
    if (score <= 9) {
      return {
        band: 'Mild',
        advice: 'Simple practices may help.',
        severity: 'mild',
      };
    }
    if (score <= 14) {
      return {
        band: 'Moderate',
        advice: 'Regular practices can support you.',
        severity: 'moderate',
      };
    }
    return {
      band: 'Severe',
      advice: 'Consider professional support; reach out if needed.',
      severity: 'severe',
    };
  }

  /**
   * Calculate the total GAD-7 score from the form inputs.
   * Only radios whose name starts with "gad7_q" are included.
   * @param {HTMLFormElement} form
   * @returns {number}
   */
  function calcSum(form) {
    let sum = 0;
    $all('input[name^="gad7_q"]:checked', form).forEach(function (el) {
      const value = Number(el.value);
      if (!Number.isNaN(value)) {
        sum += value;
      }
    });
    return sum;
  }

  /**
   * Verify all GAD-7 question groups have been answered.
   * Only fieldsets containing radios for "gad7_q*" are considered required.
   * @param {HTMLFormElement} form
   * @returns {boolean}
   */
  function allAnswered(form) {
    const fieldsets = $all('fieldset', form);
    return fieldsets.every(function (fs) {
      const radios = $all('input[type="radio"][name^="gad7_q"]', fs);
      if (!radios.length) {
        // Not a GAD-7 question group; ignore.
        return true;
      }
      return radios.some(function (radio) {
        return radio.checked;
      });
    });
  }

  /**
   * Render the computed score and accompanying messaging.
   * @param {HTMLFormElement} form
   * @param {number} sum
   */
  function render(form, sum) {
    const band = scoreToBand(sum);
    const result = $(SELECTORS.result, form);
    const scoreEl = $(SELECTORS.score, form);

    form._lastScore = sum;
    form._lastSummary =
      'GAD-7 Score: ' + sum + ' / 21 — ' + band.band + '. ' + band.advice;

    if (scoreEl) {
      scoreEl.textContent = form._lastSummary;
      scoreEl.setAttribute('data-severity', band.severity);
    }

    if (result) {
      result.hidden = false;
      if (typeof result.scrollIntoView === 'function') {
        try {
          result.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
          // Non-blocking if smooth scroll is unsupported.
        }
      }
    }

    trackEvent('assessment_submit', String(sum));

    try {
      if (history && typeof history.replaceState === 'function') {
        history.replaceState(null, '', window.location.href);
      }
    } catch (error) {
      // Ignore history cleanup failures.
    }
  }

  /**
   * Bind behaviors to a GAD-7 form.
   * @param {HTMLFormElement} form
   */
  function init(form) {
    if (!form || form._emBound) {
      return;
    }
    form._emBound = true;

    const result = $(SELECTORS.result, form);
    const scoreEl = $(SELECTORS.score, form);
    const btnReset = $(SELECTORS.reset, form);
    const btnPrint = $(SELECTORS.print, form);
    const btnCopy = $(SELECTORS.copy, form);
    const btnLink = $(SELECTORS.share, form);
    const btnEmail = $(SELECTORS.email, form);

    if (!result || !scoreEl) {
      return;
    }

    form._lastScore = null;
    form._lastSummary = '';

    // Submit handler
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      if (!allAnswered(form)) {
        const firstUnanswered = $all('fieldset', form).find(function (fs) {
          const radios = $all(
            'input[type="radio"][name^="gad7_q"]',
            fs
          );
          if (!radios.length) {
            return false;
          }
          return radios.every(function (radio) {
            return !radio.checked;
          });
        });

        if (firstUnanswered) {
          const firstRadio = $(
            'input[type="radio"][name^="gad7_q"]',
            firstUnanswered
          );
          if (firstRadio && typeof firstRadio.focus === 'function') {
            try {
              firstRadio.focus();
            } catch (error) {
              // Non-blocking
            }
          }
        }
        return;
      }

      const sum = calcSum(form);
      render(form, sum);
    });

    // Live recalculation when radios change, if result is already visible
    form.addEventListener('change', function (event) {
      const target = event.target;
      if (!target || target.type !== 'radio') {
        return;
      }
      const resultShown = result && !result.hidden;
      if (resultShown) {
        render(form, calcSum(form));
      }
    });

    // Reset button
    if (btnReset) {
      btnReset.addEventListener('click', function () {
        form.reset();
        result.hidden = true;
        form._lastScore = null;
        form._lastSummary = '';
        scoreEl.textContent = '';
        scoreEl.removeAttribute('data-severity');
      });
    }

    // Print button
    if (btnPrint) {
      btnPrint.addEventListener('click', function () {
        window.print();
      });
    }

    // Copy summary button
    if (btnCopy) {
      btnCopy.addEventListener('click', function () {
        if (!form._lastSummary) {
          window.alert(UI_TEXT.pleaseComplete);
          return;
        }
        if (
          !navigator.clipboard ||
          typeof navigator.clipboard.writeText !== 'function'
        ) {
          return;
        }
        navigator.clipboard
          .writeText(form._lastSummary)
          .then(function () {
            btnCopy.textContent = UI_TEXT.copyButtonSuccess;
            window.setTimeout(function () {
              btnCopy.textContent = UI_TEXT.copyButtonDefault;
            }, 1200);
          })
          .catch(function () {
            // Clipboard failures are silently ignored.
          });
      });
    }

    // Share link button
    if (btnLink) {
      btnLink.addEventListener('click', function () {
        if (form._lastScore == null) {
          window.alert(UI_TEXT.pleaseComplete);
          return;
        }

        const helpers = getHelpers();
        if (!helpers || typeof helpers.signURL !== 'function') {
          window.alert(UI_TEXT.shareUnavailable);
          return;
        }

        helpers
          .signURL(KIND, form._lastScore)
          .then(function (url) {
            if (!url) {
              throw new Error('Unable to create link');
            }
            if (
              navigator.clipboard &&
              typeof navigator.clipboard.writeText === 'function'
            ) {
              return navigator.clipboard.writeText(url);
            }
            return null;
          })
          .then(function () {
            window.alert(UI_TEXT.shareCopied);
            trackEvent('assessment_sharelink', String(form._lastScore));
          })
          .catch(function () {
            window.alert(UI_TEXT.shareFailed);
          });
      });
    }

    // Email summary button
    if (btnEmail) {
      btnEmail.addEventListener('click', function () {
        if (!form._lastSummary) {
          window.alert(UI_TEXT.pleaseComplete);
          return;
        }

        const helpers = getHelpers();
        if (!helpers || typeof helpers.emailSummary !== 'function') {
          window.alert(UI_TEXT.emailUnavailable);
          return;
        }

        helpers
          .emailSummary(KIND, form._lastSummary)
          .then(function () {
            trackEvent('assessment_email', '1');
          })
          .catch(function () {
            // Email failures are non-blocking for the UI.
          });
      });
    }
  }

  function initAll() {
    $all(SELECTORS.form).forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll, { once: true });
  } else {
    initAll();
  }

  window.addEventListener('pageshow', initAll);
})();
