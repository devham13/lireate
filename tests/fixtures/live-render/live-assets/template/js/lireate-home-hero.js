(function () {
  'use strict';

  var body = document.body;
  var isHome = body && body.classList.contains('lireate-home-dark');
  var isCmsHero = body && body.classList.contains('lireate-cms-page-dark') &&
    document.querySelector('.main-screen.lireate-hero-v4');
  if (!body || (!isHome && !isCmsHero)) {
    return;
  }

  body.classList.add('lx-home-js');

  var motionOk = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (motionOk) {
    body.classList.add('lx-motion-ok');
  }

  var counterRoot = document.documentElement;
  var counterId = parseInt(counterRoot.getAttribute('data-lireate-metrika-id') || '0', 10);

  function reachGoal(goalId) {
    if (!goalId || typeof window.ym !== 'function' || !counterId) {
      return;
    }
    try {
      window.ym(counterId, 'reachGoal', goalId);
    } catch (e) {
      /* ignore */
    }
  }

  document.addEventListener('click', function (ev) {
    var t = ev.target;
    if (!t || !t.closest) {
      return;
    }
    var el = t.closest('[data-lx-ym-goal]');
    if (el) {
      reachGoal(el.getAttribute('data-lx-ym-goal'));
    }
  });

  if (!motionOk || typeof IntersectionObserver === 'undefined') {
    return;
  }

  var countEls = document.querySelectorAll('[data-lx-count-to]');
  if (countEls.length) {
    var countObs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          countObs.unobserve(entry.target);
          var el = entry.target;
          var to = parseInt(el.getAttribute('data-lx-count-to') || '0', 10);
          if (!to) {
            return;
          }
          var start = 0;
          var startTime = null;
          var dur = 650;
          function step(ts) {
            if (!startTime) {
              startTime = ts;
            }
            var p = Math.min(1, (ts - startTime) / dur);
            var val = Math.round(start + (to - start) * p);
            el.textContent = String(val);
            if (p < 1) {
              window.requestAnimationFrame(step);
            }
          }
          window.requestAnimationFrame(step);
        });
      },
      { threshold: 0.4 }
    );
    countEls.forEach(function (el) {
      countObs.observe(el);
    });
  }

  var revealSel =
    '.container.content > .row.col-xs-12, .container.content > #modals, .container.content > .col-xs-12.col-lg-10, .container.content > .product, .lireate-home-blog';
  function markRevealIfVisible(el) {
    var r = el.getBoundingClientRect();
    if (r.top < window.innerHeight * 0.94) {
      el.classList.add('is-inview');
    }
  }

  document.querySelectorAll(revealSel).forEach(function (node) {
    if (node.classList.contains('lireate-hero-v4')) {
      return;
    }
    node.classList.add('lireate-home-reveal');
    markRevealIfVisible(node);
  });

  var revealObs = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-inview');
          revealObs.unobserve(entry.target);
        }
      });
    },
    { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
  );
  document.querySelectorAll('.lireate-home-reveal').forEach(function (el) {
    revealObs.observe(el);
  });
})();
