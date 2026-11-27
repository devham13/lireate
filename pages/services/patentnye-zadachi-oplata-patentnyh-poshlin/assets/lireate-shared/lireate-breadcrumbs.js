/* Legacy hosting HTML: replace «Услуги» dropdown with a plain link (kept for cached asset URLs). */
(function () {
  'use strict';
  var wrap = document.querySelector('.lx-breadcrumb-services');
  if (!wrap) {
    return;
  }
  var btn = wrap.querySelector('.lx-breadcrumb-services-trigger');
  var menu = wrap.querySelector('.lx-breadcrumb-services-menu');
  if (menu) {
    menu.remove();
  }
  if (!btn) {
    return;
  }
  var a = document.createElement('a');
  a.href = '/uslugi/';
  a.textContent = (btn.textContent || 'Услуги').replace(/\s+/g, ' ').trim() || 'Услуги';
  wrap.replaceChild(a, btn);
  wrap.classList.remove('lx-breadcrumb-services', 'is-open');
  var li = wrap.parentElement && wrap.parentElement.tagName === 'LI' ? wrap.parentElement : null;
  if (li && li.classList.contains('lx-breadcrumb-services')) {
    li.classList.remove('lx-breadcrumb-services', 'is-open');
  }
}());