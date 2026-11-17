(function () {
  'use strict';
  document.querySelectorAll('[data-lx-carousel]').forEach(function (root) {
    var track = root.querySelector('.lx-carousel-track');
    var slides = track ? track.querySelectorAll('.lx-carousel-slide') : [];
    if (!track || slides.length < 2) return;

    var viewport = root.querySelector('.lx-carousel-viewport') || root;
    var prev = root.querySelector('.lx-carousel-prev');
    var next = root.querySelector('.lx-carousel-next');
    var dotsHost = root.querySelector('.lx-carousel-dots');
    var index = 0;
    var reduced = document.documentElement.classList.contains('lx-no-motion');

    root.setAttribute('role', 'region');
    if (!root.getAttribute('aria-label')) {
      root.setAttribute('aria-label', 'Карусель');
    }
    track.setAttribute('role', 'list');
    slides.forEach(function (slide, i) {
      slide.setAttribute('role', 'listitem');
      slide.setAttribute('aria-hidden', i === 0 ? 'false' : 'true');
      slide.id = slide.id || 'lx-slide-' + Math.random().toString(36).slice(2, 9) + '-' + i;
    });

    function dots() {
      if (!dotsHost) return;
      dotsHost.innerHTML = '';
      slides.forEach(function (_, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'lx-carousel-dot' + (i === index ? ' is-active' : '');
        b.setAttribute('aria-label', 'Слайд ' + (i + 1));
        b.setAttribute('aria-controls', slides[i].id);
        b.addEventListener('click', function () {
          go(i);
        });
        dotsHost.appendChild(b);
      });
    }

    function go(i) {
      index = (i + slides.length) % slides.length;
      var offset = slides[index].offsetLeft;
      track.style.transform = 'translate3d(' + -offset + 'px,0,0)';
      slides.forEach(function (slide, n) {
        slide.setAttribute('aria-hidden', n === index ? 'false' : 'true');
      });
      if (dotsHost) {
        dotsHost.querySelectorAll('.lx-carousel-dot').forEach(function (d, n) {
          d.classList.toggle('is-active', n === index);
        });
      }
    }

    if (prev) {
      prev.type = 'button';
      prev.addEventListener('click', function () {
        go(index - 1);
      });
    }
    if (next) {
      next.type = 'button';
      next.addEventListener('click', function () {
        go(index + 1);
      });
    }

    var startX = 0;
    viewport.addEventListener(
      'touchstart',
      function (e) {
        startX = e.changedTouches[0].screenX;
      },
      { passive: true }
    );
    viewport.addEventListener(
      'touchend',
      function (e) {
        var dx = e.changedTouches[0].screenX - startX;
        if (Math.abs(dx) < 40) return;
        go(dx > 0 ? index - 1 : index + 1);
      },
      { passive: true }
    );

    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') go(index - 1);
      if (e.key === 'ArrowRight') go(index + 1);
    });

    dots();
    go(0);
    window.addEventListener(
      'resize',
      function () {
        go(index);
      },
      { passive: true }
    );

    if (!reduced) {
      root.classList.add('lx-carousel-ready');
    }
  });
})();
