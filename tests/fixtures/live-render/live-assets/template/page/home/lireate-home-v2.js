/* LIREATE home v2 (2026-11-25): reveal, counters, progress, sticky subnav, team carousel, steps. ES5-safe. */
(function () {
  'use strict';
  var root = document.getElementById('lx-home-v2');
  if (!root || !document.querySelectorAll) { return; }
  var html = document.documentElement;
  var rm = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };
  var motionOK = !rm.matches;
  function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || root).querySelectorAll(sel)); }
  function each(list, fn) { for (var i = 0; i < list.length; i++) { fn(list[i], i); } }

  /* Keyboard access for span[role=button].js-modal-form (main.js binds click). */
  each(qsa('.js-modal-form[role="button"]'), function (el) {
    el.addEventListener('keydown', function (ev) {
      var k = ev.key || ev.keyCode;
      if (k === 'Enter' || k === ' ' || k === 'Spacebar' || k === 13 || k === 32) { ev.preventDefault(); el.click(); }
    });
  });

  /* Metrika goals for every [data-lx-ym-goal] on the page (menu, footer, home).
     lireate-service-metrika-goals.js skips these when lireate-home-hero.js is loaded, and
     lireate-home-hero.js itself only runs on the old dark home (adds body.lx-home-js).
     So on v2 this is the single sender; if the old handler is active, we stay silent. */
  var counterId = parseInt(html.getAttribute('data-lireate-metrika-id') || '0', 10);
  document.addEventListener('click', function (ev) {
    if (document.body && document.body.classList.contains('lx-home-js')) { return; }
    var t = ev.target;
    while (t && t.nodeType === 1 && !t.getAttribute('data-lx-ym-goal')) { t = t.parentNode; }
    if (!t || t.nodeType !== 1 || typeof window.ym !== 'function' || !counterId) { return; }
    try { window.ym(counterId, 'reachGoal', t.getAttribute('data-lx-ym-goal')); } catch (e) { /* ignore */ }
  });

  /* Staggered reveal; content is visible without JS / with reduced motion. */
  var reveals = qsa('.hx-reveal');
  if (motionOK && 'IntersectionObserver' in window) {
    html.classList.add('hx-motion-ready');
    var io = new IntersectionObserver(function (entries) {
      each(entries, function (en) {
        if (!en.isIntersecting) { return; }
        en.target.classList.add('hx-visible');
        io.unobserve(en.target);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -28px 0px' });
    each(reveals, function (el) { io.observe(el); });
    /* Safety net: never leave content hidden. */
    setTimeout(function () { each(reveals, function (el) { var r = el.getBoundingClientRect(); if (r.top < window.innerHeight) { el.classList.add('hx-visible'); } }); }, 1200);
  } else {
    each(reveals, function (el) { el.classList.add('hx-visible'); });
  }

  /* Counters on scroll. */
  var counts = qsa('.hx-count');
  function runCount(el) {
    var from = parseInt(el.getAttribute('data-from') || '0', 10);
    var to = parseInt(el.getAttribute('data-to') || '0', 10);
    if (!to) { return; }
    var t0 = null, dur = 1100;
    function step(ts) {
      if (!t0) { t0 = ts; }
      var p = Math.min(1, (ts - t0) / dur);
      var e = 1 - Math.pow(1 - p, 3);
      el.textContent = String(Math.round(from + (to - from) * e));
      if (p < 1) { window.requestAnimationFrame(step); } else { el.textContent = String(to); }
    }
    window.requestAnimationFrame(step);
  }
  if (motionOK && 'IntersectionObserver' in window && window.requestAnimationFrame) {
    var cio = new IntersectionObserver(function (entries) {
      each(entries, function (en) {
        if (!en.isIntersecting) { return; }
        cio.unobserve(en.target);
        runCount(en.target);
      });
    }, { threshold: 0.5 });
    each(counts, function (el) { cio.observe(el); });
  }

  /* Scroll progress + sticky subnav shadow + active anchor. */
  var progress = root.querySelector('.hx-scroll-progress');
  var subnav = document.getElementById('hxSubnav');
  var navLinks = subnav ? qsa('.hx-subnav-links a', subnav) : [];
  var ticking = false;
  function syncScroll() {
    ticking = false;
    var y = window.pageYOffset || html.scrollTop || 0;
    var max = (html.scrollHeight || document.body.scrollHeight) - window.innerHeight;
    var v = max > 0 ? Math.min(1, Math.max(0, y / max)) : 0;
    if (progress) { progress.style.transform = 'scaleX(' + v.toFixed(4) + ')'; }
    if (subnav) {
      var top = subnav.getBoundingClientRect().top;
      var stuckAt = parseFloat(window.getComputedStyle(subnav).top) || 0;
      subnav.classList.toggle('hx-is-scrolled', y > 40 && top <= stuckAt + 1);
    }
    var cur = null;
    each(navLinks, function (a) {
      var id = a.getAttribute('href').slice(1);
      var sec = document.getElementById(id);
      if (sec && sec.getBoundingClientRect().top < window.innerHeight * 0.35) { cur = a; }
    });
    each(navLinks, function (a) { a.classList.toggle('hx-current', a === cur); });
  }
  function onScroll() { if (!ticking) { ticking = true; window.requestAnimationFrame ? window.requestAnimationFrame(syncScroll) : syncScroll(); } }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
  syncScroll();

  /* Smooth anchor scroll inside the page, offset for sticky bars. */
  each(navLinks, function (a) {
    a.addEventListener('click', function (ev) {
      var sec = document.getElementById(a.getAttribute('href').slice(1));
      if (!sec) { return; }
      ev.preventDefault();
      var off = (subnav ? subnav.offsetHeight : 0) + (parseFloat(window.getComputedStyle(subnav).top) || 0) + 12;
      var y = sec.getBoundingClientRect().top + (window.pageYOffset || 0) - off;
      try { window.scrollTo({ top: y, behavior: motionOK ? 'smooth' : 'auto' }); } catch (e) { window.scrollTo(0, y); }
      if (window.history && history.replaceState) { history.replaceState(null, '', '#' + sec.id); }
    });
  });

  /* Hero parallax + card spotlight (fine pointers only). */
  var fine = window.matchMedia && window.matchMedia('(pointer:fine)').matches;
  var hero = root.querySelector('.hx-hero');
  var heroRight = root.querySelector('.hx-hero-right');
  if (hero && heroRight && motionOK && fine) {
    hero.addEventListener('pointermove', function (ev) {
      var r = hero.getBoundingClientRect();
      var x = (ev.clientX - r.left) / r.width - 0.5, y = (ev.clientY - r.top) / r.height - 0.5;
      heroRight.style.setProperty('--hero-x', (x * 9).toFixed(2) + 'px');
      heroRight.style.setProperty('--hero-y', (y * 7).toFixed(2) + 'px');
    }, { passive: true });
    hero.addEventListener('pointerleave', function () { heroRight.style.setProperty('--hero-x', '0px'); heroRight.style.setProperty('--hero-y', '0px'); });
  }
  if (motionOK && fine) {
    each(qsa('.hx-service-card,.hx-why-card,.hx-case-card,.hx-blog-card,.hx-price-card'), function (card) {
      card.addEventListener('pointermove', function (ev) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', (ev.clientX - r.left) + 'px');
        card.style.setProperty('--my', (ev.clientY - r.top) + 'px');
      }, { passive: true });
    });
  }

  /* Team carousel: filters, arrows, keyboard, gentle autoplay when in view. */
  var track = document.getElementById('hxTeamTrack');
  var filters = qsa('.hx-filter');
  if (track) {
    var cards = qsa('.hx-team-card', track);
    var visibleCards = function () { return cards.filter(function (c) { return !c.hidden; }); };
    var stepWidth = function () {
      var c = visibleCards()[0];
      if (!c) { return 224; }
      var gap = parseFloat(window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap) || 18;
      return c.getBoundingClientRect().width + gap;
    };
    var scrollToX = function (x) { try { track.scrollTo({ left: x, behavior: motionOK ? 'smooth' : 'auto' }); } catch (e) { track.scrollLeft = x; } };
    var moveTeam = function (dir) {
      var end = track.scrollWidth - track.clientWidth;
      if (dir > 0 && track.scrollLeft >= end - 10) { scrollToX(0); }
      else if (dir < 0 && track.scrollLeft <= 2) { scrollToX(end); }
      else { scrollToX(track.scrollLeft + stepWidth() * dir); }
    };
    var prev = document.getElementById('hxPrevTeam'), next = document.getElementById('hxNextTeam');
    if (prev) { prev.addEventListener('click', function () { moveTeam(-1); }); }
    if (next) { next.addEventListener('click', function () { moveTeam(1); }); }
    track.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowRight') { ev.preventDefault(); moveTeam(1); }
      if (ev.key === 'ArrowLeft') { ev.preventDefault(); moveTeam(-1); }
    });
    each(filters, function (btn) {
      btn.addEventListener('click', function () {
        var f = btn.getAttribute('data-filter');
        each(filters, function (b) { var on = b === btn; b.classList.toggle('hx-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        each(cards, function (c) {
          var show = f === 'all' || c.getAttribute('data-group') === f;
          c.hidden = !show;
          c.classList.remove('hx-filter-enter');
          if (show && motionOK) { void c.offsetWidth; c.classList.add('hx-filter-enter'); }
        });
        track.scrollLeft = 0;
      });
    });
    var paused = false, onScreen = false;
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) { onScreen = en[0] && en[0].isIntersecting; }, { threshold: 0.35 }).observe(track);
    } else { onScreen = true; }
    each(['mouseenter', 'focusin', 'pointerdown', 'touchstart'], function (e) { track.addEventListener(e, function () { paused = true; }, { passive: true }); });
    each(['mouseleave', 'focusout'], function (e) { track.addEventListener(e, function () { paused = false; }); });
    each(filters.concat([prev, next]), function (b) { if (b) { b.addEventListener('focus', function () { paused = true; }); b.addEventListener('blur', function () { paused = false; }); } });
    var auto = setInterval(function () {
      if (onScreen && !paused && !document.hidden && motionOK && track.scrollWidth > track.clientWidth + 10) { moveTeam(1); }
    }, 5800);
    window.addEventListener('pagehide', function () { clearInterval(auto); });
  }

  /* Process steps (tabs). */
  var steps = qsa('.hx-process-card[data-step]');
  var live = document.getElementById('hxProcessLive');
  var titles = ['Постановка задачи', 'Подготовка документов', 'Сопровождение до результата'];
  function setStep(btn) {
    each(steps, function (el) { var on = el === btn; el.classList.toggle('hx-active', on); el.setAttribute('aria-selected', on ? 'true' : 'false'); el.setAttribute('tabindex', on ? '0' : '-1'); });
    var n = parseInt(btn.getAttribute('data-step'), 10) || 0;
    if (!live) { return; }
    var draw = function () {
      live.querySelector('.hx-process-indicator').textContent = '0' + (n + 1) + ' / 03';
      live.querySelector('.hx-process-live-name').textContent = titles[n];
      live.classList.remove('hx-is-transitioning');
    };
    if (motionOK) { live.classList.add('hx-is-transitioning'); setTimeout(draw, 160); } else { draw(); }
  }
  each(steps, function (btn, i) {
    btn.setAttribute('tabindex', i === 0 ? '0' : '-1');
    btn.addEventListener('click', function () { setStep(btn); });
    btn.addEventListener('keydown', function (ev) {
      var d = ev.key === 'ArrowRight' || ev.key === 'ArrowDown' ? 1 : (ev.key === 'ArrowLeft' || ev.key === 'ArrowUp' ? -1 : 0);
      if (!d) { return; }
      ev.preventDefault();
      var nb = steps[(i + d + steps.length) % steps.length];
      nb.focus(); setStep(nb);
    });
  });
})();
