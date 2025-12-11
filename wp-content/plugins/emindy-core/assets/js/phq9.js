(function () {
  'use strict';

  const KIND = 'phq9';

  const SELECTORS = Object.freeze({
    form: 'form.em-phq9',
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
   * Map PHQ-9 score to severity band.
   * @param {number} score
   * @returns {{band:string, advice:string, severity:'minimal'|'mild'|'moderate'|'moderately-severe'|'severe'}}
   */
  function scoreToBand(score) {
    if (score <= 4) {
      return {
        band: 'Minimal',
        advice: 'Keep gentle routines.',
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
        advice: 'Consider regular practices.',
        severity: 'moderate',
      };
    }
    if (score <= 19) {
      return {
        band: 'Moderately severe',
        advice: 'Consider talking to a professional.',
        severity: 'moderately-severe',
      };
    }
    return {
      band: 'Severe',
      advice: 'Consider professional support; reach out if you feel unsafe.',
      severity: 'severe',
    };
  }

  /**
   * Build a human-readable summary line for the given score.
   * @param {number} score
   * @returns {string}
   */
  function buildSummary(score) {
    const band = scoreToBand(score);
    return 'PHQ-9 Score: ' + score + ' / 27 — ' + band.band + '. ' + band.advice;
  }

  /**
   * Calculate the total PHQ-9 score from the form inputs.
   * Only radios whose name starts with "phq9_q" are included.
   * @param {HTMLFormElement} form
   * @returns {number}
   */
  function calcSum(form) {
    let sum = 0;
    $all('input[name^="phq9_q"]:checked', form).forEach(function (el) {
      const value = Number(el.value);
      if (!Number.isNaN(value)) {
        sum += value;
      }
    });
    return sum;
  }

  /**
   * Verify all PHQ-9 question groups have been answered.
   * Only fieldsets containing radios for "phq9_q*" are considered required.
   * @param {HTMLFormElement} form
   * @returns {boolean}
   */
  function allAnswered(form) {
    const fieldsets = $all('fieldset', form);
    return fieldsets.every(function (fs) {
      const radios = $all('input[type="radio"][name^="phq9_q"]', fs);
      if (!radios.length) {
        // Not a PHQ-9 question group; ignore.
        return true;
      }
      return radios.some(function (radio) {
        return radio.checked;
      });
    });
  }

  /**
   * Find the first unanswered PHQ-9 question group, if any.
   * @param {HTMLFormElement} form
   * @returns {HTMLElement|null}
   */
  function findFirstUnansweredFieldset(form) {
    const fieldsets = $all('fieldset', form);
    for (let i = 0; i < fieldsets.length; i++) {
      const fs = fieldsets[i];
      const radios = $all('input[type="radio"][name^="phq9_q"]', fs);
      if (!radios.length) {
        continue;
      }
      const noneChecked = radios.every(function (radio) {
        return !radio.checked;
      });
      if (noneChecked) {
        return fs;
      }
    }
    return null;
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
    form._lastSummary = buildSummary(sum);

    if (scoreEl) {
      scoreEl.textContent = form._lastSummary;
      scoreEl.setAttribute('data-severity', band.severity);
      scoreEl.setAttribute('aria-live', 'polite');
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
      if (
        typeof history !== 'undefined' &&
        history &&
        typeof history.replaceState === 'function'
      ) {
        history.replaceState(null, '', window.location.href);
      }
    } catch (error) {
      // Ignore history cleanup failures.
    }
  }

  /**
   * Bind behaviors to a PHQ-9 form.
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
        const firstUnanswered = findFirstUnansweredFieldset(form);

        if (firstUnanswered) {
          firstUnanswered.setAttribute('aria-invalid', 'true');

          const firstRadio = $(
            'input[type="radio"][name^="phq9_q"]',
            firstUnanswered
          );

          if (typeof firstUnanswered.scrollIntoView === 'function') {
            try {
              firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch (error) {
              // Non-blocking
            }
          }

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

      // Clear any previous aria-invalid flags
      $all('fieldset[aria-invalid="true"]', form).forEach(function (fs) {
        fs.removeAttribute('aria-invalid');
      });

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
        scoreEl.removeAttribute('aria-live');

        if (btnCopy) {
          btnCopy.textContent = UI_TEXT.copyButtonDefault;
        }

        $all('fieldset[aria-invalid="true"]', form).forEach(function (fs) {
          fs.removeAttribute('aria-invalid');
        });

        trackEvent('assessment_reset', '1');
      });
    }

    // Print button
    if (btnPrint) {
      btnPrint.addEventListener('click', function () {
        window.print();
        trackEvent('assessment_print', '1');
      });
    }

    // Copy summary button
    if (btnCopy) {
      if (
        !navigator.clipboard ||
        typeof navigator.clipboard.writeText !== 'function'
      ) {
        btnCopy.disabled = true;
        btnCopy.setAttribute('aria-disabled', 'true');
      }

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
            trackEvent('assessment_copy', '1');
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
