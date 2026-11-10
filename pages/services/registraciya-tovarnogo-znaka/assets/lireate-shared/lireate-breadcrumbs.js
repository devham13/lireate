(function () {
  'use strict';

  function initBreadcrumbServices() {
    var wrap = document.querySelector('.lx-breadcrumb-services');
    if (!wrap) return;

    var btn = wrap.querySelector('.lx-breadcrumb-services-trigger');
    var menu = wrap.querySelector('.lx-breadcrumb-services-menu');
    if (!btn || !menu) return;

    var hoverOpenTimer = null;
    var hoverCloseTimer = null;

    function setOpen(open) {
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        menu.removeAttribute('hidden');
        wrap.classList.add('is-open');
      } else {
        menu.setAttribute('hidden', '');
        wrap.classList.remove('is-open');
      }
    }

    function isOpen() {
      return btn.getAttribute('aria-expanded') === 'true';
    }

    function focusFirstItem() {
      var first = menu.querySelector('[role="menuitem"]');
      if (first) first.focus();
    }

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      setOpen(!isOpen());
      if (isOpen()) focusFirstItem();
    });

    btn.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        setOpen(true);
        focusFirstItem();
      }
    });

    menu.addEventListener('keydown', function (e) {
      var items = Array.prototype.slice.call(menu.querySelectorAll('[role="menuitem"]'));
      if (!items.length) return;
      var idx = items.indexOf(document.activeElement);
      if (e.key === 'Escape') {
        e.preventDefault();
        setOpen(false);
        btn.focus();
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        var next = idx < 0 ? 0 : (idx + 1) % items.length;
        items[next].focus();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        var prev = idx <= 0 ? items.length - 1 : idx - 1;
        items[prev].focus();
      } else if (e.key === 'Tab') {
        setOpen(false);
      }
    });

    wrap.addEventListener('mouseenter', function () {
      if (window.matchMedia('(hover: hover)').matches) {
        clearTimeout(hoverCloseTimer);
        hoverOpenTimer = setTimeout(function () {
          setOpen(true);
        }, 120);
      }
    });
    wrap.addEventListener('mouseleave', function () {
      if (window.matchMedia('(hover: hover)').matches) {
        clearTimeout(hoverOpenTimer);
        hoverCloseTimer = setTimeout(function () {
          setOpen(false);
        }, 220);
      }
    });

    document.addEventListener('click', function (e) {
      if (!wrap.contains(e.target)) setOpen(false);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBreadcrumbServices);
  } else {
    initBreadcrumbServices();
  }
})();
