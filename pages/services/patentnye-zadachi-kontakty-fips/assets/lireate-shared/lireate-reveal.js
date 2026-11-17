(function () {
  'use strict';
  var root = document.querySelector('.lireate-svc-page, .lireate-cn-page');
  if (!root) return;

  var revealItems = root.querySelectorAll('.lx-reveal');
  var skipMotion = document.documentElement.classList.contains('lx-no-motion');

  if (skipMotion) {
    revealItems.forEach(function (item) { item.classList.add('lx-active'); });
  } else if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('lx-active');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -5% 0px' });
    revealItems.forEach(function (item) { observer.observe(item); });
  } else {
    revealItems.forEach(function (item) { item.classList.add('lx-active'); });
  }

  if (!skipMotion) {
    var stats = root.querySelectorAll('.lx-stat-value');
    stats.forEach(function (el) {
      el.classList.add('lx-stat-pulse');
    });
  }
})();
