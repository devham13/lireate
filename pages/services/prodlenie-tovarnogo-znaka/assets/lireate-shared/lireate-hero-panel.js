(function () {
  'use strict';
  if (document.documentElement.classList.contains('lx-no-motion')) return;

  var panels = document.querySelectorAll('.lx-glass.lx-glass-anim');
  panels.forEach(function (panel) {
    panel.classList.add('lx-glass-anim-ready');
    var steps = panel.querySelectorAll('.lx-route-steps > li');
    if (!steps.length) return;
    var route = panel.querySelector('.lx-route');
    if (!route || route.querySelector('.lx-route-runner')) return;
    var runner = document.createElement('span');
    runner.className = 'lx-route-runner';
    runner.setAttribute('aria-hidden', 'true');
    route.appendChild(runner);

    var idx = 0;
    var pauseMs = 900;
    var stepMs = 1100;

    function light(i) {
      steps.forEach(function (li, n) {
        li.classList.toggle('lx-route-lit', n === i);
        li.classList.toggle('lx-route-done', n < i);
      });
      runner.style.setProperty('--lx-route-step', String(i));
    }

    function tick() {
      light(idx);
      idx += 1;
      if (idx >= steps.length) {
        idx = 0;
        setTimeout(function () {
          steps.forEach(function (li) {
            li.classList.remove('lx-route-lit', 'lx-route-done');
          });
          setTimeout(tick, pauseMs);
        }, pauseMs);
        return;
      }
      setTimeout(tick, stepMs);
    }

    if ('IntersectionObserver' in window) {
      var started = false;
      var obs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting && !started) {
              started = true;
              setTimeout(tick, 400);
              obs.disconnect();
            }
          });
        },
        { threshold: 0.35 }
      );
      obs.observe(panel);
    } else {
      setTimeout(tick, 400);
    }
  });
})();
