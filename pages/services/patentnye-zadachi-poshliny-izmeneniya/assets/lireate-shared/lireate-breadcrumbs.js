/* Legacy hosting HTML: replace «Услуги» dropdown with a plain link (kept for cached asset URLs). */
(function () {
  'use strict';

  function plainUslugiLabel(raw) {
    var t = (raw || 'Услуги').replace(/\u25be/g, '').replace(/\s+/g, ' ').trim();
    return t || 'Услуги';
  }

  function demoteServicesCrumb(wrap) {
    if (!wrap || !wrap.querySelector) {
      return;
    }
    var menu = wrap.querySelector('.lx-breadcrumb-services-menu');
    if (menu) {
      menu.remove();
    }
    var btn = wrap.querySelector('.lx-breadcrumb-services-trigger');
    if (!btn) {
      wrap.classList.remove('lx-breadcrumb-services', 'is-open');
      return;
    }
    var a = document.createElement('a');
    a.href = '/uslugi/';
    a.textContent = plainUslugiLabel(btn.textContent);
    wrap.replaceChild(a, btn);
    wrap.classList.remove('lx-breadcrumb-services', 'is-open');
    if (wrap.tagName === 'LI') {
      wrap.classList.remove('lx-breadcrumb-services', 'is-open');
    }
    var li = wrap.parentElement && wrap.parentElement.tagName === 'LI' ? wrap.parentElement : null;
    if (li && li.classList.contains('lx-breadcrumb-services')) {
      li.classList.remove('lx-breadcrumb-services', 'is-open');
    }
  }

  function run() {
    var seen = typeof WeakSet === 'function' ? new WeakSet() : null;
    var nodes = document.querySelectorAll('li.lx-breadcrumb-services, .lx-breadcrumb-services');
    for (var i = 0; i < nodes.length; i += 1) {
      if (seen && seen.has(nodes[i])) {
        continue;
      }
      if (seen) {
        seen.add(nodes[i]);
      }
      demoteServicesCrumb(nodes[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
  window.addEventListener('load', run);
}());