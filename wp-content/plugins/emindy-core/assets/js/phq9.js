(function(){
  'use strict';

  function $(sel, root = document) {
    return root.querySelector(sel);
  }

  function scoreToBand(score) {
    if (score <= 4) {
      return { band: 'Minimal', advice: 'Keep gentle routines.' };
    }
    if (score <= 9) {
      return { band: 'Mild', advice: 'Simple practices may help.' };
    }
    if (score <= 14) {
      return { band: 'Moderate', advice: 'Consider regular practices.' };
    }
    if (score <= 19) {
      return { band: 'Moderately severe', advice: 'Consider talking to a professional.' };
    }
    return { band: 'Severe', advice: 'Consider professional support; reach out if you feel unsafe.' };
  }

  function init(form) {
    if (form._emBound) {
      return; // جلوگیری از دوبار بایند شدن
    }
    form._emBound = true;

    const kind = 'phq9';
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

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      let sum = 0;
      for (let i = 0; i < 9; i++) {
        const selected = form.querySelector(`input[name="phq9_q${i}"]:checked`);
        sum += selected ? Number(selected.value) : 0;
      }
      const band = scoreToBand(sum);
      const summaryText = `Score: ${sum} / 27 — ${band.band}. ${band.advice}`;

      form._lastScore = sum;
      form._lastSummary = summaryText;

      scoreEl.textContent = summaryText;
      result.hidden = false;
      result.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (history.replaceState) {
        history.replaceState(null, '', location.href);
      }

      try {
        emindyAssess?.helpers?.track?.('assessment_submit', kind, String(sum), (window.em_post_id || 0));
      } catch (error) {
        // Tracking is non-blocking.
      }
    });

    if (btnReset) {
      btnReset.addEventListener('click', function () {
        form.reset();
        result.hidden = true;
        form._lastScore = null;
        form._lastSummary = '';
      });
    }

    if (btnPrint) {
      btnPrint.addEventListener('click', function () {
        window.print();
      });
    }

    if (btnCopy) {
      btnCopy.addEventListener('click', async function () {
        if (!form._lastSummary) {
          alert('Please complete the form first.');
          return;
        }
        try {
          await navigator.clipboard.writeText(form._lastSummary);
          btnCopy.textContent = 'Copied ✔';
          setTimeout(() => {
            btnCopy.textContent = 'Copy summary';
          }, 1200);
        } catch (error) {
          // Clipboard failures are silently ignored to avoid blocking the UI.
        }
      });
    }

    if (btnLink) {
      btnLink.addEventListener('click', async function () {
        if (form._lastScore == null) {
          alert('Please complete the form first.');
          return;
        }
        try {
          const url = await emindyAssess?.helpers?.signURL?.(kind, form._lastScore);
          if (!url) {
            throw new Error('Unable to create link');
          }
          await navigator.clipboard.writeText(url);
          alert('Link copied');
          try {
            emindyAssess?.helpers?.track?.('assessment_sharelink', kind, String(form._lastScore), (window.em_post_id || 0));
          } catch (error) {
            // Tracking is non-blocking.
          }
        } catch (error) {
          alert('Failed to create link');
        }
      });
    }

    if (btnEmail) {
      btnEmail.addEventListener('click', function () {
        if (!form._lastSummary) {
          alert('Please complete the form first.');
          return;
        }
        if (typeof emindyAssess?.helpers?.emailSummary !== 'function') {
          alert('Email is unavailable right now.');
          return;
        }
        emindyAssess.helpers.emailSummary(kind, form._lastSummary).then(function () {
          try {
            emindyAssess.helpers.track('assessment_email', kind, '1', (window.em_post_id || 0));
          } catch (error) {
            // Tracking is non-blocking.
          }
        });
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.em-phq9').forEach(init);
  });
})();
