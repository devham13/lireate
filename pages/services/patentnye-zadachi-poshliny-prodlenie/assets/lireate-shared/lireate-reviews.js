(function () {
  'use strict';

  function prefersReducedMotion() {
    return document.documentElement.classList.contains('lx-no-motion');
  }

  document.querySelectorAll('[data-lx-reviews-rail]').forEach(function (rail) {
    var viewport = rail.querySelector('.lx-reviews-rail-viewport');
    if (!viewport) return;
    var prev = rail.querySelector('.lx-reviews-rail-prev');
    var next = rail.querySelector('.lx-reviews-rail-next');

    function scrollStep() {
      var card = viewport.querySelector('.lx-review-frame');
      if (!card) return 300;
      var gap = 14;
      return card.getBoundingClientRect().width + gap;
    }

    function scrollBy(dx) {
      viewport.scrollBy({ left: dx, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
    }

    if (prev) prev.addEventListener('click', function () { scrollBy(-scrollStep()); });
    if (next) next.addEventListener('click', function () { scrollBy(scrollStep()); });
    rail.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        scrollBy(-scrollStep());
      }
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        scrollBy(scrollStep());
      }
    });
  });

  var lightbox = document.querySelector('[data-lx-review-lightbox]');
  if (!lightbox) return;

  var bodySlot = lightbox.querySelector('[data-lx-review-lightbox-body]');
  var titleEl = lightbox.querySelector('.lx-review-lightbox-title');
  var panel = lightbox.querySelector('.lx-review-lightbox-panel');
  var lastFocus = null;

  function closeLightbox() {
    lightbox.hidden = true;
    document.documentElement.classList.remove('lx-review-lightbox-open');
    if (bodySlot) bodySlot.innerHTML = '';
    if (titleEl) titleEl.textContent = '';
    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
  }

  function openLightbox(trigger) {
    var frame = trigger.closest('.lx-review-frame');
    if (!frame || !bodySlot) return;
    var full = frame.querySelector('.lx-review-full');
    if (!full) return;
    lastFocus = trigger;
    var authorEl = frame.querySelector('.lx-review-author');
    var title = authorEl ? authorEl.textContent.trim() : 'Отзыв';
    if (frame.classList.contains('lx-review-frame--demo')) {
      title = 'Пример оформления';
    }
    if (titleEl) titleEl.textContent = title;
    bodySlot.innerHTML = full.innerHTML;
    var videoHost = bodySlot.querySelector('[data-lx-lightbox-video]');
    if (videoHost) {
      var embed = videoHost.getAttribute('data-embed') || trigger.getAttribute('data-embed');
      if (embed && !videoHost.querySelector('iframe')) {
        var iframe = document.createElement('iframe');
        iframe.src = embed.indexOf('?') === -1 ? embed + '?autoplay=1' : embed + '&autoplay=1';
        iframe.title = 'Видеоотзыв';
        iframe.loading = 'lazy';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        videoHost.appendChild(iframe);
      }
    }
    lightbox.hidden = false;
    document.documentElement.classList.add('lx-review-lightbox-open');
    var closeBtn = lightbox.querySelector('.lx-review-lightbox-close');
    if (closeBtn) closeBtn.focus();
  }

  document.querySelectorAll('[data-lx-review-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openLightbox(btn);
    });
  });

  lightbox.querySelectorAll('[data-lx-review-close]').forEach(function (el) {
    el.addEventListener('click', closeLightbox);
  });

  document.addEventListener('keydown', function (e) {
    if (lightbox.hidden) return;
    if (e.key === 'Escape') {
      e.preventDefault();
      closeLightbox();
    }
  });
})();
