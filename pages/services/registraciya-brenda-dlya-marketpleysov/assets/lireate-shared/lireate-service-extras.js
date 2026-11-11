(function () {
  'use strict';
  var root = document.querySelector('.lireate-svc-page, .lireate-cn-page');
  if (!root) return;
  var reduced = document.documentElement.classList.contains('lx-no-motion');

  if (!reduced) {
    root.querySelectorAll('.lx-hero-cta .lx-btn-primary, #contact .lx-btn-primary').forEach(function (btn) {
      btn.classList.add('lx-cta-glow');
    });
  }

  function formatCount(n, el) {
    var sep = el.getAttribute('data-lx-count-sep');
    if (sep === 'space') {
      return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    return String(n);
  }

  function animateCount(el) {
    var endAttr = el.getAttribute('data-lx-count-end');
    if (!endAttr) return;
    var target = parseFloat(endAttr.replace(/\s/g, '').replace(',', '.'));
    if (isNaN(target)) return;
    var prefix = el.getAttribute('data-lx-count-prefix') || '';
    var suffix = el.getAttribute('data-lx-count-suffix') || '';
    var dur = 900;
    var t0 = performance.now();
    function frame(now) {
      var p = Math.min(1, (now - t0) / dur);
      var eased = 1 - Math.pow(1 - p, 3);
      var val = Math.round(target * eased);
      el.textContent = prefix + formatCount(val, el) + suffix;
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  if (!reduced && 'IntersectionObserver' in window) {
    var counters = root.querySelectorAll('[data-lx-count]');
    var cObs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCount(entry.target);
            cObs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );
    counters.forEach(function (el) {
      cObs.observe(el);
    });
  }

  var stepCards = root.querySelectorAll('#program .lx-card');
  if (!reduced && stepCards.length && 'IntersectionObserver' in window) {
    stepCards.forEach(function (card, i) {
      card.style.setProperty('--lx-step-i', String(i));
      card.classList.add('lx-step-card');
    });
    var sObs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('lx-step-lit');
          }
        });
      },
      { threshold: 0.25, rootMargin: '0px 0px -8% 0px' }
    );
    stepCards.forEach(function (card) {
      sObs.observe(card);
    });
  }
})();
