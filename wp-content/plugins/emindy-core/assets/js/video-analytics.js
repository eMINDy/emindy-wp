(function(){
  function on(e,sel,cb){ document.addEventListener(e,ev=>{ const t=ev.target.closest(sel); if(t) cb(ev,t); },true); }
  // Lyte: کلیک روی placeholder (div.lyte)
  on('click','.lyte, .lyte-wrapper', ()=>{ try{ emindyAssess.helpers.track('video_play','lyte','1',(window.em_post_id||0)); }catch(e){} });

  // Chapters: لینک‌ها داخل .em-chapters
  on('click','.em-chapters a', (ev,a)=>{ try{
    const t=a.textContent.trim();
    emindyAssess.helpers.track('chapter_click',t,a.href,(window.em_post_id||0));
  }catch(e){} });

  // Transcript copy: دکمه
  on('click','.em-transcript__copy', ()=>{ try{ emindyAssess.helpers.track('transcript_copy','', '1',(window.em_post_id||0)); }catch(e){} });
})();
