(function () {
  'use strict';

  var prose = document.getElementById('lireate-blog-prose');
  if (!prose) return;

  var links = document.querySelectorAll('[data-lx-toc-href], .lireate-blog-toc-mobile .lireate-blog-toc-list a');
  if (!links.length) return;

  var headings = [];
  links.forEach(function (a) {
    var id = (a.getAttribute('href') || '').replace(/^#/, '');
    if (!id) return;
    var el = document.getElementById(id);
    if (el) headings.push({ id: id, el: el, links: [] });
  });

  links.forEach(function (a) {
    var id = (a.getAttribute('href') || '').replace(/^#/, '');
    headings.forEach(function (h) {
      if (h.id === id) h.links.push(a);
    });
  });

  if (!headings.length) return;

  function setActive(id) {
    links.forEach(function (a) {
      a.classList.toggle('is-active', (a.getAttribute('href') || '') === '#' + id);
    });
  }

  var observer = new IntersectionObserver(
    function (entries) {
      var visible = entries
        .filter(function (e) {
          return e.isIntersecting;
        })
        .sort(function (a, b) {
          return a.boundingClientRect.top - b.boundingClientRect.top;
        });
      if (visible.length) {
        setActive(visible[0].target.id);
      }
    },
    { root: null, rootMargin: '-20% 0px -55% 0px', threshold: 0 }
  );

  headings.forEach(function (h) {
    observer.observe(h.el);
  });

  setActive(headings[0].id);
})();
