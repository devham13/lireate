(function () {
  'use strict';

  var bar = document.querySelector('.lx-mobile-bar');
  if (!bar) {
    return;
  }

  var MAX_BLOB =
    'M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z';

  var ICONS = {
    telegram:
      '<svg class="lx-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
      '<path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>',
    'max-white':
      '<svg class="lx-svg-icon lx-svg-icon--max" viewBox="0 0 100 100" aria-hidden="true" focusable="false">' +
      '<path fill="currentColor" d="' +
      MAX_BLOB +
      '"/></svg>',
  };

  document.querySelectorAll('.lx-mobile-bar [data-lx-icon]').forEach(function (el) {
    var name = el.getAttribute('data-lx-icon');
    if (!name || el.querySelector('.lx-svg-icon') || !ICONS[name]) {
      return;
    }
    el.insertAdjacentHTML('afterbegin', ICONS[name]);
  });

  function contactTarget() {
    return (
      document.getElementById('contact') ||
      document.querySelector('section#contact, .lx-contact[id], [data-lx-contact]')
    );
  }

  if ('IntersectionObserver' in window) {
    var target = contactTarget() || document.querySelector('form.ajax_form, form.order_form_list');
    if (target) {
      var obs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            bar.classList.toggle('lx-mobile-bar--hide', entry.isIntersecting);
          });
        },
        { threshold: 0.12 },
      );
      obs.observe(target);
    }
  }

  bar.addEventListener('click', function (e) {
    var lead = e.target && e.target.closest ? e.target.closest('[data-lx-svc-lead="1"],[href="#contact"]') : null;
    if (!lead || lead.getAttribute('href') !== '#contact') {
      return;
    }
    var contact = contactTarget();
    if (contact) {
      return;
    }
    e.preventDefault();
    var modal = document.querySelector('.js-modal-form');
    if (modal) {
      modal.click();
      return;
    }
    var form = document.querySelector('form.ajax_form, .order_form_list');
    if (form) {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
})();
