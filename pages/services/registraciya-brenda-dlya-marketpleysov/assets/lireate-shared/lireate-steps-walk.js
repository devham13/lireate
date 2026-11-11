(function () {
  'use strict';
  var reduced = document.documentElement.classList.contains('lx-no-motion');

  function mascotSvg() {
    return (
      '<svg class="lx-mascot lx-mascot--walk-mini" viewBox="0 0 72 108" focusable="false" aria-hidden="true">' +
      '<g class="lx-mascot-part lx-mascot-foot lx-mascot-foot--l"><rect x="14" y="94" width="14" height="8" rx="4" fill="#f8fafc"/></g>' +
      '<g class="lx-mascot-part lx-mascot-foot lx-mascot-foot--r"><rect x="44" y="94" width="14" height="8" rx="4" fill="#f8fafc"/></g>' +
      '<g class="lx-mascot-part lx-mascot-hand lx-mascot-hand--l"><rect x="4" y="52" width="10" height="10" rx="2" fill="#f8fafc"/></g>' +
      '<g class="lx-mascot-part lx-mascot-hand lx-mascot-hand--r"><rect x="58" y="52" width="10" height="10" rx="2" fill="#f8fafc"/></g>' +
      '<g class="lx-mascot-part lx-mascot-head"><circle cx="36" cy="28" r="18" fill="#f8fafc"/>' +
      '<rect x="34" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/><rect x="41" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/></g></svg>'
    );
  }

  document.querySelectorAll('[data-lx-steps-walk]').forEach(function (wrap) {
    var steps = wrap.querySelectorAll('.lx-steps-walk-step');
    if (!steps.length) return;
    var slot = wrap.querySelector('.lx-steps-walk-mascot-slot');
    var mover;
    if (slot && window.innerWidth >= 380) {
      mover = document.createElement('div');
      mover.className = 'lx-steps-walk-mascot';
      mover.innerHTML = mascotSvg();
      slot.appendChild(mover);
      if (!reduced) mover.classList.add('lx-steps-walk-mascot--bob');
    }

    function placeMascot(el) {
      if (!mover || !el) return;
      var wr = wrap.getBoundingClientRect();
      var sr = el.getBoundingClientRect();
      var top = sr.top - wr.top + sr.height * 0.15;
      var left = Math.max(8, sr.left - wr.left - 52);
      mover.style.transform = 'translate(' + left + 'px,' + top + 'px)';
    }

    function setActive(el) {
      steps.forEach(function (s) {
        s.classList.toggle('lx-steps-walk-step--active', s === el);
      });
      placeMascot(el);
    }

    if ('IntersectionObserver' in window) {
      var active = steps[0];
      var obs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.45) {
              active = entry.target;
              setActive(active);
            }
          });
        },
        { threshold: [0.45, 0.65], rootMargin: '-12% 0px -20% 0px' }
      );
      steps.forEach(function (s) {
        obs.observe(s);
      });
      setActive(active);
    } else {
      steps.forEach(function (s) {
        s.classList.add('lx-steps-walk-step--active');
      });
    }
  });
})();
