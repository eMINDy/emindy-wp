(function(){
  function $(sel,root=document){ return root.querySelector(sel); }

  function scoreToBand(x){
    if (x<=4)  return {band:'Minimal',           advice:'Keep gentle routines.'};
    if (x<=9)  return {band:'Mild',              advice:'Simple practices may help.'};
    if (x<=14) return {band:'Moderate',          advice:'Consider regular practices.'};
    if (x<=19) return {band:'Moderately severe', advice:'Consider talking to a professional.'};
    return        {band:'Severe',                advice:'Consider professional support; reach out if you feel unsafe.'};
  }

  function init(form){
    if (form._emBound) return;  // جلوگیری از دوبار بایند شدن
    form._emBound = true;

    const kind     = 'phq9';
    const result   = $('.em-phq9__result', form);
    const scoreEl  = $('.em-phq9__score',  form);
    const btnReset = $('.em-phq9__reset',  form);
    const btnPrint = $('.em-phq9__print',  form);
    const btnCopy  = $('.em-phq9__copy',   form);
    const btnLink  = $('.em-phq9__sharelink', form);
    const btnEmail = $('.em-phq9__email',     form);

    form._lastScore   = null;
    form._lastSummary = '';

    form.addEventListener('submit', function(e){
      e.preventDefault();

      let sum = 0;
      for (let i=0;i<9;i++){
        const v = form.querySelector(`input[name="phq9_q${i}"]:checked`);
        sum += v ? Number(v.value) : 0;
      }
      const band = scoreToBand(sum);
      const txt  = `Score: ${sum} / 27 — ${band.band}. ${band.advice}`;

      form._lastScore   = sum;
      form._lastSummary = txt;

      scoreEl.textContent = txt;
      result.hidden = false;
      result.scrollIntoView({behavior:'smooth', block:'start'});
      if (history.replaceState) history.replaceState(null,'',location.href);

      // Track submit
      try{ emindyAssess?.helpers?.track?.('assessment_submit', kind, String(sum), (window.em_post_id||0)); }catch(e){}
    });

    btnReset && btnReset.addEventListener('click', function(){
      form.reset();
      result.hidden = true;
      form._lastScore = null;
      form._lastSummary = '';
    });

    btnPrint && btnPrint.addEventListener('click', function(){ window.print(); });

    btnCopy && btnCopy.addEventListener('click', async function(){
      if (!form._lastSummary){ alert('Please complete the form first.'); return; }
      try{
        await navigator.clipboard.writeText(form._lastSummary);
        btnCopy.textContent = 'Copied ✔';
        setTimeout(()=>btnCopy.textContent='Copy summary',1200);
      }catch(e){}
    });

    btnLink && btnLink.addEventListener('click', async function(){
      if (form._lastScore == null){ alert('Please complete the form first.'); return; }
      try{
        const url = await emindyAssess.helpers.signURL(kind, form._lastScore);
        await navigator.clipboard.writeText(url);
        alert('Link copied');
        try{ emindyAssess.helpers.track('assessment_sharelink', kind, String(form._lastScore), (window.em_post_id||0)); }catch(e){}
      }catch(e){ alert('Failed to create link'); }
    });

    btnEmail && btnEmail.addEventListener('click', function(){
      if (!form._lastSummary){ alert('Please complete the form first.'); return; }
      emindyAssess.helpers.emailSummary(kind, form._lastSummary)
        .then(()=>{ try{ emindyAssess.helpers.track('assessment_email', kind, '1', (window.em_post_id||0)); }catch(e){} });
    });
  }

    document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('form.em-phq9').forEach(init);
  
    const subject = encodeURIComponent(`Your eMINDy ${toolName} summary`);
    const body = encodeURIComponent(
      `Hi,\nHere’s your educational self-check summary from eMINDy.\n\n` +
      `Tool: ${toolName}\nScore: ${score}\nSeverity: ${severity}\nDate: ${new Date().toLocaleDateString()}\nLink: ${shareUrl}\n\n` +
      `Note: This is for education only, not a diagnosis. If you feel unsafe, visit the Emergency page.`
    );
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
  
        
    });
})();
