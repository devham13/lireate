/* Lightweight sticky section highlight. No tracking, no form replacements. */
(function () {
  'use strict';
  var root = document.getElementById('lireate-patent-v4');
  if (!root) return;
  var links = Array.prototype.slice.call(root.querySelectorAll('.lp4-nav-link'));
  var sections = links.map(function (a) { return { a: a, section: root.querySelector(a.getAttribute('href')) }; }).filter(function (e) { return !!e.section; });
  if (!sections.length) return;
  var scheduled = false;
  var sync = function () {
    scheduled = false;
    var chosen = sections[0], best = Infinity;
    sections.forEach(function (v) {
      var r = v.section.getBoundingClientRect();
      if (r.bottom < 130) return;
      var score = Math.abs(r.top - 180);
      if (score < best) { best = score; chosen = v; }
    });
    sections.forEach(function (v) {
      var active = v === chosen;
      v.a.classList.toggle('is-active', active);
      if (active) v.a.setAttribute('aria-current', 'location');
      else v.a.removeAttribute('aria-current');
    });
  };
  var request = function () { if (!scheduled) { scheduled = true; requestAnimationFrame(sync); } };
  window.addEventListener('scroll', request, { passive: true });
  window.addEventListener('resize', request, { passive: true });
  sync();
})();
