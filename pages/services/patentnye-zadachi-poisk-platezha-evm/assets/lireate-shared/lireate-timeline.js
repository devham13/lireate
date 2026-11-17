(function () {
  'use strict';
  if (document.documentElement.classList.contains('lx-no-motion')) return;

  document.querySelectorAll('[data-lx-timeline]').forEach(function (track) {
    var steps = track.querySelectorAll('.lx-timeline-step');
    var marker = track.querySelector('.lx-timeline-mascot');
    if (!steps.length || !marker) return;

    function setActive(el) {
      steps.forEach(function (s) {
        s.classList.toggle('lx-timeline-step--active', s === el);
      });
      var tr = track.getBoundingClientRect();
      var sr = el.getBoundingClientRect();
      var top = sr.top - tr.top + 8;
      marker.style.transform = 'translateY(' + top + 'px)';
    }

    if ('IntersectionObserver' in window) {
      var current = steps[0];
      var obs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
              current = entry.target;
              setActive(current);
            }
          });
        },
        { threshold: [0.5, 0.75], rootMargin: '-5% 0px -25% 0px' }
      );
      steps.forEach(function (s) {
        obs.observe(s);
      });
      setActive(current);
    }
  });
})();
