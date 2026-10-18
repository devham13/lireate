(function () {
  'use strict';
  var reduced = document.documentElement.classList.contains('lx-no-motion');
  var slot = document.querySelector('[data-lx-mascot]');
  if (!slot) return;

  var variant = slot.getAttribute('data-lx-mascot') || 'default';
  var w = window.innerWidth;
  if (w < 380) {
    slot.remove();
    return;
  }

  var mascot = document.createElement('div');
  mascot.className = 'lx-mascot-host lx-mascot-host--' + variant;
  mascot.setAttribute('aria-hidden', 'true');
  mascot.innerHTML =
    '<svg class="lx-mascot" viewBox="0 0 72 108" focusable="false">' +
    '<g class="lx-mascot-part lx-mascot-foot lx-mascot-foot--l">' +
    '<rect x="14" y="94" width="14" height="8" rx="4" fill="#f8fafc"/><rect x="15" y="95" width="12" height="2" fill="#e2e8f0"/>' +
    '</g><g class="lx-mascot-part lx-mascot-foot lx-mascot-foot--r">' +
    '<rect x="44" y="94" width="14" height="8" rx="4" fill="#f8fafc"/><rect x="45" y="95" width="12" height="2" fill="#e2e8f0"/>' +
    '</g><g class="lx-mascot-part lx-mascot-hand lx-mascot-hand--l">' +
    '<rect x="4" y="52" width="10" height="10" rx="2" fill="#f8fafc"/><rect x="2" y="48" width="6" height="6" rx="1" fill="#f8fafc"/>' +
    '<rect x="10" y="46" width="5" height="5" rx="1" fill="#f8fafc"/><rect x="6" y="44" width="4" height="4" rx="1" fill="#f8fafc"/>' +
    '</g><g class="lx-mascot-part lx-mascot-hand lx-mascot-hand--r">' +
    '<rect x="58" y="52" width="10" height="10" rx="2" fill="#f8fafc"/><rect x="64" y="48" width="6" height="6" rx="1" fill="#f8fafc"/>' +
    '<rect x="57" y="46" width="5" height="5" rx="1" fill="#f8fafc"/><rect x="62" y="44" width="4" height="4" rx="1" fill="#f8fafc"/>' +
    '</g><g class="lx-mascot-part lx-mascot-head">' +
    '<circle cx="36" cy="28" r="18" fill="#f8fafc"/><rect x="34" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/>' +
    '<rect x="41" y="26" width="3" height="8" rx="1.5" fill="#0f172a"/><rect x="22" y="38" width="28" height="4" rx="2" fill="#e2e8f0" opacity="0.35"/>' +
    '</g></svg>';
  slot.appendChild(mascot);

  if (reduced) {
    mascot.classList.add('lx-mascot--static');
    return;
  }

  mascot.classList.add('lx-mascot--enter');
  setTimeout(function () {
    mascot.classList.add('lx-mascot--wave');
  }, 700);
  setTimeout(function () {
    mascot.classList.remove('lx-mascot--wave');
    mascot.classList.add('lx-mascot--point');
  }, 2800);
  setTimeout(function () {
    mascot.classList.remove('lx-mascot--point');
    mascot.classList.add('lx-mascot--idle');
  }, 5200);

  var program = document.getElementById('program');
  if (program && 'IntersectionObserver' in window) {
    var walkObs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          mascot.classList.toggle('lx-mascot--walk', entry.isIntersecting);
        });
      },
      { threshold: 0.15, rootMargin: '-10% 0px' }
    );
    walkObs.observe(program);
  }

  var form = document.getElementById('lireate-lead-form');
  if (form) {
    var idleTimer;
    var idleMs = 10000;
    function resetIdle() {
      clearTimeout(idleTimer);
      mascot.classList.remove('lx-mascot--form-wave');
      idleTimer = setTimeout(function () {
        var contact = document.getElementById('contact');
        if (!contact) return;
        var r = contact.getBoundingClientRect();
        if (r.top < window.innerHeight && r.bottom > 0) {
          mascot.classList.add('lx-mascot--form-wave');
        }
      }, idleMs);
    }
    ['input', 'focus', 'click', 'keydown'].forEach(function (ev) {
      form.addEventListener(ev, resetIdle, { passive: true });
    });
    resetIdle();
  }
})();
