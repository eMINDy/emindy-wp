(function(){
  'use strict';

  const KIND = 'phq9';

  /**
   * Shorthand query helper.
   * @param {string} sel
   * @param {Document|Element} [root=document]
   * @returns {Element|null}
   */
  function $(sel, root = document) {
    return root.querySelector(sel);
  }

  /**
   * Return an array of elements matching the selector.
   * @param {string} sel
   * @param {Document|Element} [root=document]
   * @returns {Element[]}
   */
  function $all(sel, root = document) {
    return Array.prototype.slice.call(root.querySelectorAll(sel));
  }

  /**
   * Map PHQ-9 score to severity band.
   * @param {number} score
   * @returns {{band:string, advice:string, severity:'minimal'|'mild'|'moderate'|'moderately-severe'|'severe'}}
   */
  function scoreToBand(score) {
    if (score <= 4) {
      return { band: 'Minimal', advice: 'Keep gentle routines.', severity: 'minimal' };
    }
    if (score <= 9) {
      return { band: 'Mild', advice: 'Simple practices may help.', severity: 'mild' };
    }
    if (score <= 14) {
      return { band: 'Moderate', advice: 'Consider regular practices.', severity: 'moderate' };
    }
    if (score <= 19) {
      return { band: 'Moderately severe', advice: 'Consider talking to a professional.', severity: 'moderately-severe' };
    }
    return { band: 'Severe', advice: 'Consider professional support; reach out if you feel unsafe.', severity: 'severe' };
  }

  /**
   * Calculate the total PHQ-9 score from the form inputs.
   * @param {HTMLFormElement} form
   * @returns {number}
   */
  function calcSum(form) {
    let sum = 0;
    $all('input[name^="phq9_q"]:checked', form).forEach(function(el) {
      const value = Number(el.value);
      if (!Number.isNaN(value)) {
        sum += value;
      }
    });
    return sum;
  }

  /**
   * Render the computed score to the UI.
   * @param {HTMLFormElement} form
   * @param {number} sum
   */
  function render(form, sum) {
    const band = scoreToBand(sum);
    const result = $('.em-phq9__result', form);
    const scoreEl = $('.em-phq9__score', form);

    form._lastScore = sum;
    form._lastSummary = `Score: ${sum} / 27 — ${band.band}. ${band.advice}`;

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

    try {
      if (typeof emindyAssess === 'object') {
        emindyAssess?.helpers?.track?.('assessment_submit', KIND, String(sum), (window.em_post_id || 0));
      }
    } catch (error) {
      // Tracking is non-blocking.
    }

    try {
      if (history.replaceState) {
        history.replaceState(null, '', location.href);
      }
    } catch (error) {
      // Ignore history cleanup failures.
    }
  }

  /**
   * Verify all questions have been answered.
   * @param {HTMLFormElement} form
   * @returns {boolean}
   */
  function allAnswered(form) {
    const fieldsets = $all('fieldset', form);
    return fieldsets.every(function(fs) {
      return $all('input[type="radio"]:checked', fs).length === 1;
    });
  }

  /**
   * Bind behaviors to a PHQ-9 form.
   * @param {HTMLFormElement} form
   */
  function init(form) {
    if (!form || form._emBound) {
      return; // جلوگیری از دوبار بایند شدن
    }
    form._emBound = true;

    const result = $('.em-phq9__result', form);
    const scoreEl = $('.em-phq9__score', form);
    const btnReset = $('.em-phq9__reset', form);
    const btnPrint = $('.em-phq9__print', form);
    const btnCopy = $('.em-phq9__copy', form);
    const btnLink = $('.em-phq9__sharelink', form);
    const btnEmail = $('.em-phq9__email', form);

    if (!result || !scoreEl) {
      return;
    }

    form._lastScore = null;
    form._lastSummary = '';

    form.addEventListener('submit', function(event) {
      event.preventDefault();

      if (!allAnswered(form)) {
        const firstUnanswered = $all('fieldset', form).find(function(fs) {
          return $all('input[type="radio"]:checked', fs).length === 0;
        });
        if (firstUnanswered) {
          const firstRadio = $('input[type="radio"]', firstUnanswered);
          if (firstRadio && typeof firstRadio.focus === 'function') {
            try { firstRadio.focus(); } catch (error) {}
          }
        }
        return;
      }

      const sum = calcSum(form);
      render(form, sum);
    });

    if (btnReset) {
      btnReset.addEventListener('click', function() {
        form.reset();
        result.hidden = true;
        form._lastScore = null;
        form._lastSummary = '';
        scoreEl.textContent = '';
        scoreEl.removeAttribute('data-severity');
      });
    }

    if (btnPrint) {
      btnPrint.addEventListener('click', function() {
        window.print();
      });
    }

    if (btnCopy) {
      btnCopy.addEventListener('click', async function() {
        if (!form._lastSummary) {
          alert('Please complete the form first.');
          return;
        }
        if (!navigator.clipboard || !navigator.clipboard.writeText) {
          return;
        }
        try {
          await navigator.clipboard.writeText(form._lastSummary);
          btnCopy.textContent = 'Copied ✔';
          setTimeout(function() {
            btnCopy.textContent = 'Copy summary';
          }, 1200);
        } catch (error) {
          // Clipboard failures are silently ignored to avoid blocking the UI.
        }
      });
    }

    if (btnLink) {
      btnLink.addEventListener('click', async function() {
        if (form._lastScore == null) {
          alert('Please complete the form first.');
          return;
        }
        if (!(window.emindyAssess && emindyAssess.helpers && emindyAssess.helpers.signURL)) {
          alert('Share link service unavailable.');
          return;
        }
        try {
          const url = await emindyAssess.helpers.signURL(KIND, form._lastScore);
          if (!url) {
            throw new Error('Unable to create link');
          }
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(url);
          }
          alert('Link copied');
          try {
            emindyAssess.helpers.track('assessment_sharelink', KIND, String(form._lastScore), (window.em_post_id || 0));
          } catch (error) {
            // Tracking is non-blocking.
          }
        } catch (error) {
          alert('Failed to create link');
        }
      });
    }

    if (btnEmail) {
      btnEmail.addEventListener('click', function() {
        if (!form._lastSummary) {
          alert('Please complete the form first.');
          return;
        }
        if (!(window.emindyAssess && emindyAssess.helpers && emindyAssess.helpers.emailSummary)) {
          alert('Email is unavailable right now.');
          return;
        }
        emindyAssess.helpers.emailSummary(KIND, form._lastSummary).then(function() {
          try {
            emindyAssess.helpers.track('assessment_email', KIND, '1', (window.em_post_id || 0));
          } catch (error) {
            // Tracking is non-blocking.
          }
        }).catch(function() {
          // Email failures are non-blocking for the UI.
        });
      });
    }
  }

  function initAll() {
    $all('form.em-phq9').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll, { once: true });
  } else {
    initAll();
  }

  window.addEventListener('pageshow', initAll);
})();
