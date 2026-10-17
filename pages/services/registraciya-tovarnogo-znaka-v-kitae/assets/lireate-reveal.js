(function () {
  'use strict';
  var root = document.querySelector('.lireate-cn-page');
  if (!root) return;

  var revealItems = root.querySelectorAll('.lx-reveal');
  if ('IntersectionObserver' in window) {
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

  var counters = root.querySelectorAll('[data-lx-count]');
  function animateCounter(el) {
    var target = parseFloat(el.getAttribute('data-lx-count') || '0');
    var suffix = el.getAttribute('data-lx-suffix') || '';
    var prefix = el.getAttribute('data-lx-prefix') || '';
    var duration = 850;
    var start = performance.now();
    function frame(now) {
      var progress = Math.min((now - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = prefix + Math.round(target * eased) + suffix;
      if (progress < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }
  if ('IntersectionObserver' in window) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !entry.target.dataset.lxDone) {
          entry.target.dataset.lxDone = '1';
          animateCounter(entry.target);
          co.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });
    counters.forEach(function (c) { co.observe(c); });
  } else {
    counters.forEach(animateCounter);
  }
})();
