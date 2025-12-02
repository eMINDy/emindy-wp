(function(){
  const LS_KEY = 'emindy_player_progress';

  function $(sel, root=document){ return root.querySelector(sel); }
  function $all(sel, root=document){ return Array.from(root.querySelectorAll(sel)); }

  function readProgress(postId){
    try{
      const all = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
      return all[String(postId)] || null;
    }catch(e){ return null; }
  }
  function writeProgress(postId, data){
    try{
      const all = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
      all[String(postId)] = data;
      localStorage.setItem(LS_KEY, JSON.stringify(all));
    }catch(e){}
  }
  function clearProgress(postId){
    try{
      const all = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
      delete all[String(postId)];
      localStorage.setItem(LS_KEY, JSON.stringify(all));
    }catch(e){}
  }

  function fmtTime(sec){
    sec = Math.max(0, Math.floor(sec||0));
    const m = Math.floor(sec/60);
    const s = sec % 60;
    return `${m}:${s.toString().padStart(2,'0')}`;
  }

  function initPlayer(root){
    if (!root) return;
    const postId = root.getAttribute('data-post');
    const steps = JSON.parse(root.getAttribute('data-steps') || '[]');
    if (!Array.isArray(steps) || steps.length===0) return;

    const ui = {
      title: $('.em-p__title', root),
      list: $('.em-p__list', root),
      cur: $('.em-p__cur', root),
      total: $('.em-p__total', root),
      bar: $('.em-p__bar', root),
      btnPrev: $('.em-p__prev', root),
      btnNext: $('.em-p__next', root),
      btnPlay: $('.em-p__play', root),
      btnReset: $('.em-p__reset', root),
      live: $('.em-p__live', root),
      timeRemain: $('.em-p__remain', root),
      timeStep: $('.em-p__step-time', root),
      timeAll: $('.em-p__all-time', root),
    };

    // build list
    ui.list.innerHTML = '';
    steps.forEach((s, i)=>{
      const li = document.createElement('li');
      li.className = 'em-p__item';
      li.setAttribute('role','button');
      li.setAttribute('tabindex','0');
      li.setAttribute('data-index', String(i));
      li.innerHTML = `
        <div class="em-p__item-top">
          <span class="em-p__item-index">${i+1}</span>
          <span class="em-p__item-label">${(s.label||'').toString()}</span>
          <span class="em-p__item-dur" aria-label="${emindyPlayer.i18n.stepTime}">${fmtTime(Number(s.duration||0))}</span>
        </div>
        ${s.tip ? `<div class="em-p__item-tip">${s.tip}</div>` : '' }
      `;
      ui.list.appendChild(li);
    });

    const totalSecs = steps.reduce((a,s)=>a+Number(s.duration||0),0);
    ui.total.textContent = String(steps.length);
    ui.timeAll.textContent = fmtTime(totalSecs);

    // state
    let idx = 0;
    let playing = false;
    let t0 = null;
    let remain = 0;
    let rafId = 0;

    function announce(msg){
      if (ui.live){ ui.live.textContent=''; ui.live.textContent = msg; }
    }
    function select(i, announceChange=true){
      idx = Math.max(0, Math.min(steps.length-1, i));
      $all('.em-p__item', ui.list).forEach((li, k)=>{
        li.classList.toggle('is-active', k===idx);
        if (k===idx) li.setAttribute('aria-current','step'); else li.removeAttribute('aria-current');
      });
      ui.cur.textContent = String(idx+1);
      ui.title.textContent = steps[idx].label || emindyPlayer.i18n.step;
      remain = Number(steps[idx].duration||0);
      ui.timeStep.textContent = fmtTime(remain);
      ui.timeRemain.textContent = fmtTime(remain);
      updateBar();
      if(announceChange) announce(emindyPlayer.i18n.stepChanged.replace('%s', String(idx+1)));
      persist();
    }
    function updateBar(){
      const p = ((idx)/Math.max(1,steps.length)) * 100;
      ui.bar.style.width = p+'%';
    }
    function tick(ts){
      if (!playing) return;
      if (t0==null) t0 = ts;
      const dt = (ts - t0)/1000.0;
      if (dt >= 1){
        t0 = ts;
        remain = Math.max(0, remain-1);
        ui.timeRemain.textContent = fmtTime(remain);
        if (remain<=0){
          next(true);
          return;
        }
      }
      rafId = requestAnimationFrame(tick);
    }
    function play(){
      if (playing) return;
      playing = true;
      ui.btnPlay.setAttribute('aria-pressed','true');
      ui.btnPlay.textContent = emindyPlayer.i18n.pause;
      announce(emindyPlayer.i18n.started);
      rafId = requestAnimationFrame(tick);
      persist();
    }
    function pause(){
      if (!playing) return;
      playing = false;
      ui.btnPlay.setAttribute('aria-pressed','false');
      ui.btnPlay.textContent = emindyPlayer.i18n.start;
      if (rafId) cancelAnimationFrame(rafId);
      rafId = 0;
      t0 = null;
      announce(emindyPlayer.i18n.paused);
      persist();
    }
    function toggle(){
      if (playing) pause(); else play();
    }
    function prev(){
      pause();
      select(idx-1);
    }
    function next(auto=false){
      pause();
      if (idx < steps.length-1){
        select(idx+1, !auto);
      } else {
        announce(emindyPlayer.i18n.completed);
        ui.bar.style.width = '100%';
      }
    }
    function reset(){
      pause();
      select(0);
      clearProgress(postId);
      announce(emindyPlayer.i18n.reset);
    }
    function persist(){
      writeProgress(postId, { idx, remain, playing:false }); // همیشه در pause ذخیره می‌شود
    }

    // events
    ui.btnPrev.addEventListener('click', prev);
    ui.btnNext.addEventListener('click', ()=>next(false));
    ui.btnPlay.addEventListener('click', toggle);
    ui.btnReset.addEventListener('click', reset);

    ui.list.addEventListener('click', e=>{
      const li = e.target.closest('.em-p__item');
      if (!li) return;
      pause(); select(Number(li.getAttribute('data-index')||'0'));
    });
    ui.list.addEventListener('keydown', e=>{
      if (e.key==='Enter' || e.key===' '){
        const li = e.target.closest('.em-p__item');
        if (li){ e.preventDefault(); pause(); select(Number(li.getAttribute('data-index')||'0')); }
      }
    });

    // restore progress
    const saved = readProgress(postId);
    if (saved && typeof saved.idx === 'number'){
      idx = saved.idx; select(idx, false);
      if (typeof saved.remain === 'number') { remain = Math.max(0, saved.remain); ui.timeRemain.textContent = fmtTime(remain); }
    }else{
      select(0, false);
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.em-player[data-steps]').forEach(initPlayer);
  });
})();
