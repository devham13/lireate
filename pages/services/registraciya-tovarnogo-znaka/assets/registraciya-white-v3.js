/* LIREATE «Регистрация товарного знака» — white v3 motion (25.11.2026).
   Только визуальные эффекты: прогресс-бар, подсветка пункта меню, stagger-появление карточек,
   карусель команды (стрелки). Формы, Метрику и обработчики заявок не трогает. ES5. */
(function () {
  'use strict';
  var body = document.body;
  if (!body || !body.classList.contains('lx-tm-white')) { return; }
  var reduce = false;
  try { reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches; } catch (e) { reduce = false; }
  var hasIO = typeof window.IntersectionObserver !== 'undefined';
  if (!reduce && hasIO) { document.documentElement.classList.add('lx-tw-motion'); }

  function each(list, fn) { Array.prototype.forEach.call(list, fn); }

  /* scroll progress */
  function initProgress() {
    if (reduce) { return; }
    var bar = document.createElement('div');
    bar.className = 'lx-tw-progress';
    bar.setAttribute('aria-hidden', 'true');
    body.appendChild(bar);
    var ticking = false;
    function update() {
      ticking = false;
      var de = document.documentElement;
      var max = (de.scrollHeight - de.clientHeight) || 1;
      var p = Math.min(1, Math.max(0, (window.pageYOffset || de.scrollTop) / max));
      bar.style.transform = 'scaleX(' + p.toFixed(4) + ')';
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
    }, { passive: true });
    update();
  }

  /* current section in the header nav */
  function initNavCurrent() {
    if (!hasIO) { return; }
    var links = document.querySelectorAll('.lx-nav-links a[href^="#"]');
    var map = {};
    var targets = [];
    each(links, function (a) {
      var id = a.getAttribute('href').slice(1);
      var el = id && document.getElementById(id);
      if (el) { map[id] = a; targets.push(el); }
    });
    if (!targets.length) { return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting && map[en.target.id]) {
          each(links, function (a) { a.classList.remove('lx-tw-current'); });
          map[en.target.id].classList.add('lx-tw-current');
        }
      });
    }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
    targets.forEach(function (t) { io.observe(t); });
  }

  /* stagger cards */
  function initStagger() {
    var groups = document.querySelectorAll('.lx-pilot-journey-track, .lx-pilot-included-grid, .lx-card-grid, .lx-price-grid, .lx-related-grid, .lireate-vk-cases-grid');
    each(groups, function (g) { g.classList.add('lx-tw-stagger'); });
    if (reduce || !hasIO) {
      each(groups, function (g) { g.classList.add('lx-tw-in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('lx-tw-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
    each(groups, function (g) { io.observe(g); });
    /* safety: never leave content hidden */
    window.setTimeout(function () {
      each(groups, function (g) {
        var r = g.getBoundingClientRect();
        if (r.top < window.innerHeight) { g.classList.add('lx-tw-in'); }
      });
    }, 2500);
  }

  /* team carousel */
  function initTeam() {
    var grid = document.querySelector('#team .lx-team-grid');
    if (!grid || grid.children.length < 2) { return; }
    var shell = document.createElement('div');
    shell.className = 'lx-tw-team-shell';
    var tools = document.createElement('div');
    tools.className = 'lx-tw-team-tools';
    function mk(label, txt, dir) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'lx-tw-arrow';
      b.setAttribute('aria-label', label);
      b.setAttribute('aria-controls', 'lx-tw-team-track');
      b.textContent = txt;
      b.addEventListener('click', function () {
        var card = grid.querySelector('.lx-team-card');
        var step = card ? card.getBoundingClientRect().width + 18 : 260;
        var left = grid.scrollLeft + dir * step;
        if (dir > 0 && grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 4) { left = 0; }
        if (dir < 0 && grid.scrollLeft <= 4) { left = grid.scrollWidth; }
        if (typeof grid.scrollTo === 'function') {
          grid.scrollTo({ left: left, behavior: reduce ? 'auto' : 'smooth' });
        } else {
          grid.scrollLeft = left;
        }
      });
      return b;
    }
    tools.appendChild(mk('Предыдущие сотрудники', '\u2190', -1));
    tools.appendChild(mk('Следующие сотрудники', '\u2192', 1));
    grid.parentNode.insertBefore(shell, grid);
    shell.appendChild(tools);
    shell.appendChild(grid);
    grid.id = grid.id || 'lx-tw-team-track';
    grid.classList.add('lx-tw-track');
    grid.setAttribute('tabindex', '0');
    grid.setAttribute('aria-label', 'Команда LIREATE, прокрутка');
  }

  function boot() {
    initProgress();
    initNavCurrent();
    initStagger();
    initTeam();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
