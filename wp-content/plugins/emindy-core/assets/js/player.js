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

  function sanitizeStep(step){
    if (!step || typeof step !== 'object'){
      return { label: '', duration: 0, tip: '' };
    }

    const durationValue = Number(step.duration);
    return {
      label: typeof step.label === 'string' ? step.label : '',
      duration: Number.isFinite(durationValue) && durationValue > 0 ? durationValue : 0,
      tip: typeof step.tip === 'string' ? step.tip : '',
    };
  }

  function parseSteps(raw){
    try {
      const parsed = JSON.parse(raw || '[]');
      if (!Array.isArray(parsed)) return [];
      return parsed.map(sanitizeStep);
    } catch (e) {
      return [];
    }
  }

  function buildStepItem(step, index, i18n){
    const li = document.createElement('li');
    li.className = 'em-p__item';
    li.setAttribute('role','button');
    li.setAttribute('tabindex','0');
    li.setAttribute('data-index', String(index));

    const top = document.createElement('div');
    top.className = 'em-p__item-top';

    const stepIndex = document.createElement('span');
    stepIndex.className = 'em-p__item-index';
    stepIndex.textContent = String(index + 1);

    const label = document.createElement('span');
    label.className = 'em-p__item-label';
    label.textContent = step.label;

    const duration = document.createElement('span');
    duration.className = 'em-p__item-dur';
    duration.setAttribute('aria-label', i18n.stepTime);
    duration.textContent = fmtTime(step.duration);

    top.append(stepIndex, label, duration);
    li.appendChild(top);

    if (step.tip){
      const tip = document.createElement('div');
      tip.className = 'em-p__item-tip';
      tip.textContent = step.tip;
      li.appendChild(tip);
    }

    return li;
  }

  function initPlayer(root){
    if (!root || typeof emindyPlayer === 'undefined') return;
    const postId = String(root.getAttribute('data-post') || '').trim();
    const steps = parseSteps(root.getAttribute('data-steps'));
    if (!postId || steps.length===0) return;

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

    if (!ui.list) return;

    // build list
    ui.list.innerHTML = '';
    steps.forEach((s, i)=>{
      ui.list.appendChild(buildStepItem(s, i, emindyPlayer.i18n));
    });

    const totalSecs = steps.reduce((a,s)=>a+Number(s.duration||0),0);
    if (ui.total) ui.total.textContent = String(steps.length);
    if (ui.timeAll) ui.timeAll.textContent = fmtTime(totalSecs);

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
      if (ui.cur) ui.cur.textContent = String(idx+1);
      if (ui.title) ui.title.textContent = steps[idx].label || emindyPlayer.i18n.step;
      remain = Number(steps[idx].duration||0);
      if (ui.timeStep) ui.timeStep.textContent = fmtTime(remain);
      if (ui.timeRemain) ui.timeRemain.textContent = fmtTime(remain);
      updateBar();
      if(announceChange) announce(emindyPlayer.i18n.stepChanged.replace('%s', String(idx+1)));
      persist();
    }
    function updateBar(){
      if (!ui.bar) return;
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
        if (ui.timeRemain) ui.timeRemain.textContent = fmtTime(remain);
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
      if (ui.btnPlay){
        ui.btnPlay.setAttribute('aria-pressed','true');
        ui.btnPlay.textContent = emindyPlayer.i18n.pause;
      }
      announce(emindyPlayer.i18n.started);
      rafId = requestAnimationFrame(tick);
      persist();
    }
    function pause(){
      if (!playing) return;
      playing = false;
      if (ui.btnPlay){
        ui.btnPlay.setAttribute('aria-pressed','false');
        ui.btnPlay.textContent = emindyPlayer.i18n.start;
      }
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
        if (ui.bar) ui.bar.style.width = '100%';
      }
    }
    function reset(){
      pause();
      select(0);
      clearProgress(postId);
      announce(emindyPlayer.i18n.reset);
    }
    function persist(){
      writeProgress(postId, { idx, remain: Math.max(0, Math.floor(remain||0)), playing:false }); // همیشه در pause ذخیره می‌شود
    }

    // events
    if (ui.btnPrev) ui.btnPrev.addEventListener('click', prev);
    if (ui.btnNext) ui.btnNext.addEventListener('click', ()=>next(false));
    if (ui.btnPlay) ui.btnPlay.addEventListener('click', toggle);
    if (ui.btnReset) ui.btnReset.addEventListener('click', reset);

    ui.list.addEventListener('click', e=>{
      const target = e.target;
      if (!(target instanceof Element)) return;
      const li = target.closest('.em-p__item');
      if (!li) return;
      pause(); select(Number(li.getAttribute('data-index')||'0'));
    });
    ui.list.addEventListener('keydown', e=>{
      if (e.key==='Enter' || e.key===' '){
        const target = e.target;
        if (!(target instanceof Element)) return;
        const li = target.closest('.em-p__item');
        if (li){ e.preventDefault(); pause(); select(Number(li.getAttribute('data-index')||'0')); }
      }
    });

    // restore progress
    const saved = readProgress(postId);
    if (saved && typeof saved.idx === 'number'){
      idx = saved.idx; select(idx, false);
      if (typeof saved.remain === 'number' && ui.timeRemain) { remain = Math.max(0, saved.remain); ui.timeRemain.textContent = fmtTime(remain); }
    }else{
      select(0, false);
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.em-player[data-steps]').forEach(initPlayer);
  });
})();
