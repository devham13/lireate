(function () {
  'use strict';
  var bar = document.querySelector('.lx-mobile-bar');
  if (!bar) return;
  var contact = document.getElementById('contact');
  if (!contact || !('IntersectionObserver' in window)) return;
  var obs = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        bar.classList.toggle('lx-mobile-bar--hide', entry.isIntersecting);
      });
    },
    { threshold: 0.15 }
  );
  obs.observe(contact);
})();
