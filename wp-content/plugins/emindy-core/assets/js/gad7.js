(function(){
  'use strict';

  /**
   * Shorthand query helpers.
   * @param {string} sel
   * @param {Document|Element} [root=document]
   * @returns {Element|null|Element[]}
   */
  function $(sel, root = document){ return root.querySelector(sel); }
  function $all(sel, root = document){ return Array.prototype.slice.call(root.querySelectorAll(sel)); }

  // 0–4 minimal, 5–9 mild, 10–14 moderate, 15–21 severe
  function scoreToBand(x){
    if (x <= 4)  return {band:'Minimal',  advice:'Light routines may help.'};
    if (x <= 9)  return {band:'Mild',     advice:'Simple practices may help.'};
    if (x <= 14) return {band:'Moderate', advice:'Regular practices can support you.'};
    return            {band:'Severe',     advice:'Consider professional support; reach out if needed.'};
  }

  function calcSum(form){
    let sum = 0;
    $all('input[name^="gad7_q"]:checked', form).forEach(function(el){
      const v = Number(el.value);
      if (!Number.isNaN(v)) sum += v;
    });
    return sum;
  }

  function render(form, sum){
    const band    = scoreToBand(sum);
    const result  = $('.em-phq9__result', form);
    const scoreEl = $('.em-phq9__score',  form);

    form._lastScore   = sum;
    form._lastSummary = 'GAD-7 Score: ' + sum + ' / 21 — ' + band.band + '. ' + band.advice;

    if (scoreEl){ scoreEl.textContent = form._lastSummary; }
    if (result){
      result.hidden = false;
      // اسکرول ملایم؛ اگر بلاک وجود دارد
      if (typeof result.scrollIntoView === 'function'){
        try{ result.scrollIntoView({behavior:'smooth', block:'start'}); }catch(e){}
      }
    }
    try{ emindyAssess && emindyAssess.helpers && emindyAssess.helpers.track('assessment_submit','gad7', String(sum), (window.em_post_id||0)); }catch(e){}
  }

  function bindForm(form){
    if (!form || form._emBound) return;
    form._emBound = true;

    const btnReset = $('.em-phq9__reset',  form);
    const btnPrint = $('.em-phq9__print',  form);
    const btnCopy  = $('.em-phq9__copy',   form);
    const btnLink  = $('.em-phq9__sharelink', form);
    const btnEmail = $('.em-phq9__email',     form);

    form._lastScore = null;
    form._lastSummary = '';

    // submit
    form.addEventListener('submit', function(e){
      e.preventDefault();
      // اگر کاربر همه را تیک نزده باشد، required خود مرورگر مانع submit واقعی می‌شود
      // اما ما هم یک چک محافظتی می‌کنیم:
      const answered = $all('fieldset', form).every(function(fs){
        return $all('input[type="radio"]:checked', fs).length === 1;
      });
      if (!answered){
        // اجازه بدهید خود مرورگر پیام required را مدیریت کند:
        const firstUnanswered = $all('fieldset', form).find(function(fs){
          return $all('input[type="radio"]:checked', fs).length === 0;
        });
        if (firstUnanswered){
          const firstRadio = $('input[type="radio"]', firstUnanswered);
          if (firstRadio){
            try{ firstRadio.focus(); }catch(e){}
          }
        }
        return;
      }

      const sum = calcSum(form);
      render(form, sum);
      // تمیز‌کاری URL
      try{ history.replaceState && history.replaceState(null,'',location.href); }catch(e){}
    });

    // live-recalc روی تغییر گزینه‌ها (اختیاری اما مفید)
    form.addEventListener('change', function(ev){
      if (!ev.target || ev.target.type !== 'radio') return;
      // فقط وقتی قبلاً نتیجه نشان داده‌ایم، لایو آپدیت کن
      const resultEl = $('.em-phq9__result', form);
      const resultShown = resultEl && !resultEl.hidden;
      if (resultShown){
        render(form, calcSum(form));
      }
    });

    // کنترل دکمه‌ها
    btnReset && btnReset.addEventListener('click', function(){
      form.reset();
      const result = $('.em-phq9__result', form);
      if (result) result.hidden = true;
      form._lastScore = null;
      form._lastSummary = '';
    });
    btnPrint && btnPrint.addEventListener('click', function(){ window.print(); });
    btnCopy && btnCopy.addEventListener('click', function(){
      if (!form._lastSummary){ alert('Please complete the form first.'); return; }
      if (navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(form._lastSummary).then(function(){
          btnCopy.textContent = 'Copied ✔';
          setTimeout(function(){ btnCopy.textContent = 'Copy summary'; }, 1200);
        }).catch(function(){});
      }
    });
    btnLink && btnLink.addEventListener('click', function(){
      if (form._lastScore == null){ alert('Please complete the form first.'); return; }
      if (!(window.emindyAssess && emindyAssess.helpers && emindyAssess.helpers.signURL)){
        alert('Share link service unavailable.');
        return;
      }
      emindyAssess.helpers.signURL('gad7', form._lastScore).then(function(url){
        if (navigator.clipboard && navigator.clipboard.writeText){
          return navigator.clipboard.writeText(url);
        }
        return Promise.reject();
      }).then(function(){
        alert('Link copied');
        try{ emindyAssess.helpers.track('assessment_sharelink','gad7', String(form._lastScore), (window.em_post_id||0)); }catch(e){}
      }).catch(function(){ alert('Failed to create link'); });
    });
    btnEmail && btnEmail.addEventListener('click', function(){
      if (!form._lastSummary){ alert('Please complete the form first.'); return; }
      if (!(window.emindyAssess && emindyAssess.helpers && emindyAssess.helpers.emailSummary)){
        alert('Email service unavailable.');
        return;
      }
      emindyAssess.helpers.emailSummary('gad7', form._lastSummary)
        .then(function(){ try{ emindyAssess.helpers.track('assessment_email','gad7','1',(window.em_post_id||0)); }catch(e){} });
    });
  }

  function initAll(){
    $all('form.em-gad7').forEach(bindForm);
  }

  // ضد-دیفر: اگر DOM آماده است، همین حالا؛ وگرنه منتظر.
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initAll, {once:true});
  } else {
    initAll();
  }

  // اگر صفحه با ناوبری‌های بدون رفرش (e.g., بعضی تم‌ها) تغییر کند:
  window.addEventListener('pageshow', initAll);
})();
