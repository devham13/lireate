(function () {
  'use strict';
  var block = document.getElementById('floatingBlock');
  if (!block) return;

  var manager = document.getElementById('manager_icon');
  var message = document.getElementById('managerMessage');
  if (manager) {
    manager.setAttribute('aria-hidden', 'false');
  }
  if (message) {
    message.setAttribute('aria-hidden', 'true');
    block.classList.add('lx-widget-icons-only');
  }

  function rectsOverlap(a, b) {
    return (
      a.left < b.right &&
      a.right > b.left &&
      a.top < b.bottom &&
      a.bottom > b.top
    );
  }

  function targetsOverlapForm() {
    var forms = document.querySelectorAll('form');
    if (!forms.length) return false;
    var rect = block.getBoundingClientRect();
    for (var i = 0; i < forms.length; i++) {
      var fr = forms[i].getBoundingClientRect();
      if (fr.bottom <= 0 || fr.top >= window.innerHeight) continue;
      if (rectsOverlap(rect, fr)) return true;
    }
    return false;
  }

  function targetsOverlapContent() {
    var rect = block.getBoundingClientRect();
    var nodes = document.querySelectorAll(
      '#modals .advant, #modals .content-button, .main-title, .lu-page, #lireate-avtor, .lu-wrap, .txt-content',
    );
    for (var i = 0; i < nodes.length; i++) {
      var r = nodes[i].getBoundingClientRect();
      if (r.bottom <= 0 || r.top >= window.innerHeight) continue;
      if (rectsOverlap(rect, r)) return true;
    }
    return false;
  }

  function updateWidget() {
    var hide = targetsOverlapForm() || targetsOverlapContent();
    if (hide) {
      block.classList.add('lx-widget-hidden');
    } else {
      block.classList.remove('lx-widget-hidden');
    }
  }

  if ('IntersectionObserver' in window) {
    var watch = document.querySelectorAll(
      'form, .g-recaptcha, #recaptcha, .recaptcha, #modals .advant, .main-title, .lu-page, #lireate-avtor',
    );
    if (watch.length) {
      var obs = new IntersectionObserver(
        function () {
          updateWidget();
        },
        { root: null, threshold: [0, 0.08, 0.25], rootMargin: '-8px 0px -72px 0px' },
      );
      for (var j = 0; j < watch.length; j++) {
        obs.observe(watch[j]);
      }
    }
  }
  window.addEventListener('scroll', updateWidget, { passive: true });
  window.addEventListener('resize', updateWidget);
  window.addEventListener('orientationchange', updateWidget);
  updateWidget();
})();
