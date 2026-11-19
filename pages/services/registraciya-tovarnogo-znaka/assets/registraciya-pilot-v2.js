(function () {
  'use strict';

  var reduceMotion = false;
  try {
    reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  } catch (e) {
    reduceMotion = false;
  }

  function initPilotReveal() {
    if (reduceMotion) {
      document.querySelectorAll('.lx-pilot-reveal').forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }
    var nodes = document.querySelectorAll('.lx-pilot-reveal');
    if (!nodes.length || typeof IntersectionObserver === 'undefined') {
      nodes.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }
    var obs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            obs.unobserve(entry.target);
          }
        });
      },
      { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );
    nodes.forEach(function (el) {
      obs.observe(el);
    });
  }

  function initFaqPilot() {
    document.querySelectorAll('.lx-faq--pilot').forEach(function (detail) {
      var summary = detail.querySelector('summary');
      if (!summary) {
        return;
      }
      summary.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          if (typeof detail.toggle === 'function') {
            detail.toggle();
          }
        }
      });
    });
  }

  function initChipIcons() {
    if (typeof window.lireateApplyMessengerIcons !== 'function') {
      return;
    }
    document.querySelectorAll('a.lx-pilot-contact-chip[data-lx-icon]').forEach(function (el) {
      window.lireateApplyMessengerIcons(el);
    });
  }

  function boot() {
    initPilotReveal();
    initFaqPilot();
    initChipIcons();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();