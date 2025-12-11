(function () {
  'use strict';

  /**
   * eMINDy Exercise Player
   * Progressive enhancement for the [em_player] shortcode.
   * Relies on a root element with class .em-player and data attributes:
   *   - data-post   : post ID
   *   - data-steps  : JSON array of steps [{label, duration, tip}]
   */

  const STORAGE_KEY = 'emindy_player_progress';

  /**
   * Resolve / normalize global namespace and i18n labels.
   */
  const globalNS = window.emindyPlayer || {};
  const DEFAULT_I18N = {
    stepLabelFallback: 'Step',
    stepTime: 'Step duration',
    stepChanged: 'Moved to step %s',
    started: 'Practice started',
    paused: 'Practice paused',
    completed: 'Practice completed',
    reset: 'Progress reset',
    start: 'Start',
    pause: 'Pause'
  };
  const I18N = Object.freeze(Object.assign({}, DEFAULT_I18N, globalNS.i18n || {}));
  window.emindyPlayer = Object.assign(globalNS, { i18n: I18N });

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function $all(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function safeParseJSON(str, fallback) {
    if (!str || typeof str !== 'string') {
      return fallback;
    }
    try {
      return JSON.parse(str);
    } catch (e) {
      return fallback;
    }
  }

  function hasLocalStorage() {
    try {
      const testKey = '__emindy_test__';
      window.localStorage.setItem(testKey, '1');
      window.localStorage.removeItem(testKey);
      return true;
    } catch (e) {
      return false;
    }
  }

  const STORAGE_AVAILABLE = hasLocalStorage();

  function readAllProgress() {
    if (!STORAGE_AVAILABLE) {
      return {};
    }
    try {
      const raw = window.localStorage.getItem(STORAGE_KEY);
      if (!raw) {
        return {};
      }
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (e) {
      return {};
    }
  }

  function writeAllProgress(all) {
    if (!STORAGE_AVAILABLE) {
      return;
    }
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(all || {}));
    } catch (e) {
      // Ignore quota / privacy errors – player still works without persistence.
    }
  }

  function readProgress(postId) {
    if (!postId) {
      return null;
    }
    const all = readAllProgress();
    return all[String(postId)] || null;
  }

  function writeProgress(postId, data) {
    if (!postId || !data) {
      return;
    }
    const all = readAllProgress();
    all[String(postId)] = {
      idx: typeof data.idx === 'number' ? data.idx : 0,
      remain: typeof data.remain === 'number' ? Math.max(0, data.remain) : 0
    };
    writeAllProgress(all);
  }

  function clearProgress(postId) {
    if (!postId) {
      return;
    }
    const all = readAllProgress();
    if (Object.prototype.hasOwnProperty.call(all, String(postId))) {
      delete all[String(postId)];
      writeAllProgress(all);
    }
  }

  function formatTime(sec) {
    let value = Math.max(0, Math.floor(sec || 0));
    const minutes = Math.floor(value / 60);
    const seconds = value % 60;
    return String(minutes) + ':' + String(seconds).padStart(2, '0');
  }

  function sanitizeStep(step) {
    if (!step || typeof step !== 'object') {
      return { label: '', duration: 0, tip: '' };
    }
    const durationValue = Number(step.duration);
    return {
      label: typeof step.label === 'string' ? step.label : '',
      duration: Number.isFinite(durationValue) && durationValue > 0 ? durationValue : 0,
      tip: typeof step.tip === 'string' ? step.tip : ''
    };
  }

  function parseSteps(raw) {
    const parsed = safeParseJSON(raw, []);
    if (!Array.isArray(parsed)) {
      return [];
    }
    return parsed.map(sanitizeStep);
  }

  function buildStepItem(step, index) {
    const li = document.createElement('li');
    li.className = 'em-p__item';
    li.setAttribute('role', 'button');
    li.setAttribute('tabindex', '0');
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
    duration.setAttribute('aria-label', I18N.stepTime);
    duration.textContent = formatTime(step.duration);

    top.append(stepIndex, label, duration);
    li.appendChild(top);

    if (step.tip) {
      const tip = document.createElement('div');
      tip.className = 'em-p__item-tip';
      tip.textContent = step.tip;
      li.appendChild(tip);
    }

    return li;
  }

  function initPlayer(root) {
    if (!root) {
      return;
    }

    const postId = String(root.getAttribute('data-post') || '').trim();
    const steps = parseSteps(root.getAttribute('data-steps'));
    if (!postId || !steps.length) {
      return;
    }

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
      timeAll: $('.em-p__all-time', root)
    };

    if (!ui.list) {
      return;
    }

    // Build list
    ui.list.innerHTML = '';
    steps.forEach(function (step, index) {
      ui.list.appendChild(buildStepItem(step, index));
    });

    const totalSeconds = steps.reduce(function (acc, step) {
      return acc + Number(step.duration || 0);
    }, 0);

    if (ui.total) {
      ui.total.textContent = String(steps.length);
    }
    if (ui.timeAll) {
      ui.timeAll.textContent = formatTime(totalSeconds);
    }

    if (ui.bar) {
      ui.bar.setAttribute('role', 'progressbar');
      ui.bar.setAttribute('aria-valuemin', '0');
      ui.bar.setAttribute('aria-valuemax', String(steps.length));
      ui.bar.setAttribute('aria-valuenow', '0');
    }

    // State
    let idx = 0;
    let playing = false;
    let t0 = null;
    let remain = 0;
    let rafId = 0;

    function announce(message) {
      if (!ui.live || !message) {
        return;
      }
      ui.live.textContent = '';
      ui.live.textContent = message;
    }

    function updateBar() {
      if (!ui.bar) {
        return;
      }
      const completedSteps = idx;
      const total = Math.max(1, steps.length);
      const percent = (completedSteps / total) * 100;
      ui.bar.style.width = String(percent) + '%';
      ui.bar.setAttribute('aria-valuenow', String(completedSteps));
    }

    function persist() {
      writeProgress(postId, {
        idx: idx,
        remain: Math.max(0, Math.floor(remain || 0))
      });
    }

    function select(nextIndex, announceChange) {
      if (announceChange === void 0) {
        announceChange = true;
      }

      idx = Math.max(0, Math.min(steps.length - 1, nextIndex));

      const items = $all('.em-p__item', ui.list);
      items.forEach(function (li, i) {
        const isActive = i === idx;
        li.classList.toggle('is-active', isActive);
        if (isActive) {
          li.setAttribute('aria-current', 'step');
        } else {
          li.removeAttribute('aria-current');
        }
      });

      if (ui.cur) {
        ui.cur.textContent = String(idx + 1);
      }

      if (ui.title) {
        ui.title.textContent = steps[idx].label || I18N.stepLabelFallback;
      }

      remain = Number(steps[idx].duration || 0);
      if (ui.timeStep) {
        ui.timeStep.textContent = formatTime(steps[idx].duration || 0);
      }
      if (ui.timeRemain) {
        ui.timeRemain.textContent = formatTime(remain);
      }

      updateBar();

      if (announceChange) {
        announce(I18N.stepChanged.replace('%s', String(idx + 1)));
      }

      persist();
    }

    function tick(timestamp) {
      if (!playing) {
        return;
      }

      if (t0 === null) {
        t0 = timestamp;
      }

      const delta = (timestamp - t0) / 1000;

      if (delta >= 1) {
        t0 = timestamp;
        remain = Math.max(0, remain - 1);

        if (ui.timeRemain) {
          ui.timeRemain.textContent = formatTime(remain);
        }

        persist();

        if (remain <= 0) {
          next(true);
          return;
        }
      }

      rafId = window.requestAnimationFrame(tick);
    }

    function play() {
      if (playing) {
        return;
      }

      playing = true;

      if (ui.btnPlay) {
        ui.btnPlay.setAttribute('aria-pressed', 'true');
        ui.btnPlay.textContent = I18N.pause;
      }

      announce(I18N.started);
      rafId = window.requestAnimationFrame(tick);
      persist();
    }

    function pause() {
      if (!playing) {
        return;
      }

      playing = false;

      if (ui.btnPlay) {
        ui.btnPlay.setAttribute('aria-pressed', 'false');
        ui.btnPlay.textContent = I18N.start;
      }

      if (rafId) {
        window.cancelAnimationFrame(rafId);
      }
      rafId = 0;
      t0 = null;

      announce(I18N.paused);
      persist();
    }

    function togglePlay() {
      if (playing) {
        pause();
      } else {
        play();
      }
    }

    function prev() {
      pause();
      select(idx - 1);
    }

    function next(autoAdvance) {
      pause();

      if (idx < steps.length - 1) {
        select(idx + 1, !autoAdvance);
      } else {
        announce(I18N.completed);
        if (ui.bar) {
          ui.bar.style.width = '100%';
          ui.bar.setAttribute('aria-valuenow', String(steps.length));
        }
        clearProgress(postId);
      }
    }

    function reset() {
      pause();
      select(0);
      clearProgress(postId);
      announce(I18N.reset);
    }

    // Button events
    if (ui.btnPrev) {
      ui.btnPrev.addEventListener('click', prev);
    }
    if (ui.btnNext) {
      ui.btnNext.addEventListener('click', function () {
        next(false);
      });
    }
    if (ui.btnPlay) {
      ui.btnPlay.addEventListener('click', togglePlay);
    }
    if (ui.btnReset) {
      ui.btnReset.addEventListener('click', reset);
    }

    // Step list interactions
    ui.list.addEventListener('click', function (event) {
      const target = event.target;
      if (!target || !(target instanceof Element)) {
        return;
      }
      const li = target.closest('.em-p__item');
      if (!li) {
        return;
      }
      pause();
      select(Number(li.getAttribute('data-index') || '0'));
    });

    ui.list.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        const target = event.target;
        if (!target || !(target instanceof Element)) {
          return;
        }
        const li = target.closest('.em-p__item');
        if (!li) {
          return;
        }
        event.preventDefault();
        pause();
        select(Number(li.getAttribute('data-index') || '0'));
      }
    });

    // Pause when tab becomes hidden to avoid drifting timers.
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        pause();
      }
    });

    // Restore saved progress if available
    const saved = readProgress(postId);
    if (saved && typeof saved.idx === 'number') {
      idx = saved.idx;
      select(idx, false);

      if (typeof saved.remain === 'number') {
        remain = Math.max(0, saved.remain);
        if (ui.timeRemain) {
          ui.timeRemain.textContent = formatTime(remain);
        }
      }
    } else {
      select(0, false);
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    $all('.em-player[data-steps]').forEach(initPlayer);
  });
})();
