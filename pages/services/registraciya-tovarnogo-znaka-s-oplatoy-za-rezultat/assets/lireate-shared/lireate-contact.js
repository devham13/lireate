(function () {
  'use strict';

  var MAX_BLOB =
    'M50.76 0.26c27.54 0 49.13 22.34 49.13 49.89S77.61 99.49 51.02 99.49c-9.44 0-14.01-1.33-21.37-6.54-.51-.36-1.2-.27-1.63.19-5.66 6.04-20.17 10.28-20.83 2.03 0-14.39-7.19-23.73-7.19-45.3C0 21.55 23.22 0.26 50.76 0.26zm.77 24.55c-13.06-.69-23.26 8.39-25.51 22.57-1.86 11.75 1.44 26.07 4.27 26.79 1.2.31 4.08-1.88 6.18-3.86.39-.37 1-.44 1.46-.15 3.27 2 6.97 3.5 11.05 3.71 13.41.7 25.3-9.8 26.01-23.21.7-13.41-10.02-25.14-23.43-25.84z';

  var ICONS = {
    telegram:
      '<svg class="lx-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
      '<path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>',
    whatsapp:
      '<svg class="lx-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
      '<path fill="currentColor" d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg>',
    lead:
      '<svg class="lx-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
      '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><path d="M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/><path d="M9 12h6"/><path d="M9 16h4"/></svg>',
    max: function (uid) {
      return (
        '<svg class="lx-svg-icon lx-svg-icon--max" viewBox="0 0 100 100" aria-hidden="true" focusable="false">' +
        '<defs><linearGradient id="' +
        uid +
        '" x1="8" y1="92" x2="92" y2="8" gradientUnits="userSpaceOnUse">' +
        '<stop stop-color="#4F46E5"/><stop offset=".45" stop-color="#A855F7"/><stop offset="1" stop-color="#EC4899"/></linearGradient></defs>' +
        '<path fill="url(#' +
        uid +
        ')" d="' +
        MAX_BLOB +
        '"/></svg>'
      );
    },
    'max-white':
      '<svg class="lx-svg-icon lx-svg-icon--max" viewBox="0 0 100 100" aria-hidden="true" focusable="false">' +
      '<path fill="currentColor" d="' +
      MAX_BLOB +
      '"/></svg>'
  };

  function iconHtml(name) {
    if (name === 'max') {
      return ICONS.max('lxMaxG' + Math.random().toString(36).slice(2, 9));
    }
    return ICONS[name] || '';
  }

  function applyIcon(el, name) {
    if (!el || !name || el.querySelector('.lx-svg-icon')) return;
    var html = iconHtml(name);
    if (!html) return;
    el.insertAdjacentHTML('afterbegin', html);
  }

  function setHref(id, url, fallback) {
    var el = document.getElementById(id);
    if (!el) return;
    el.href = url || fallback || el.getAttribute('href') || '/contacts/';
  }

  function initMessengerLinks(cfg) {
    var tg = cfg.telegram || 'https://t.me/lireate_patent';
    var wa = cfg.whatsapp || 'https://wa.me/79600484957';
    var max = cfg.max || 'https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI'; // LIREATE_MAX_CHAT_URL
    setHref('link-telegram', tg);
    setHref('link-whatsapp', wa);
    setHref('link-max', max);
    setHref('hero-telegram', tg);
    setHref('hero-max', max);
    setHref('dock-telegram', tg);
    setHref('dock-max', max);
    setHref('form-alt-telegram', tg);
    setHref('form-alt-max', max);
  }

  function initIcons() {
    document.querySelectorAll('[data-lx-icon]').forEach(function (el) {
      applyIcon(el, el.getAttribute('data-lx-icon'));
    });
    var dockLead = document.querySelector('.lx-dock-lead, .lx-dock a[href="#contact"]');
    applyIcon(dockLead, 'lead');
    applyIcon(document.getElementById('dock-telegram'), 'telegram');
    var dockMax = document.getElementById('dock-max');
    if (dockMax) {
      applyIcon(dockMax, 'max-white');
    }
  }

  function initLeadForm() {
    var form = document.getElementById('lireate-lead-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      var nameEl = document.getElementById('lf-name');
      var phoneEl = document.getElementById('lf-phone');
      var name = nameEl ? nameEl.value.trim() : '';
      var phone = phoneEl ? phoneEl.value.trim() : '';
      var topic = form.getAttribute('data-lead-topic') || 'Заявка LIREATE';
      var body =
        topic +
        '%0AИмя: ' +
        encodeURIComponent(name) +
        '%0AТел: ' +
        encodeURIComponent(phone);
      var tgEl = document.getElementById('link-telegram') || document.getElementById('form-alt-telegram');
      var tg = tgEl ? tgEl.href : '';
      if (tg && tg.indexOf('t.me') !== -1) {
        var sep = tg.indexOf('?') === -1 ? '?' : '&';
        window.open(tg + sep + 'text=' + body, '_blank', 'noopener,noreferrer');
      }
      var ok = document.getElementById('lf-success');
      if (ok) ok.classList.add('is-visible');
    });
  }

  var cfg = {
    telegram: document.documentElement.getAttribute('data-lireate-telegram') || '',
    whatsapp: document.documentElement.getAttribute('data-lireate-whatsapp') || '',
    max: document.documentElement.getAttribute('data-lireate-max') || ''
  };

  initMessengerLinks(cfg);
  initIcons();
  initLeadForm();
})();
