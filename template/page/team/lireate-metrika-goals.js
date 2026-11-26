(function () {
  'use strict';

  var root = document.documentElement;
  var COUNTER_ID = parseInt(root.getAttribute('data-lireate-metrika-id') || '0', 10);
  if (!COUNTER_ID) {
    return;
  }

  /** Идентификаторы целей в Метрике (JS-события). */
  var GOALS = {
    telegram: 'perehod_v_telegramm',
    whatsapp: 'perehod_v_messendzher',
    max: 'perehod_v_messendzher',
    phone: 'klik_po_nomeru_telefona',
    messenger: 'perehod_v_messendzher',
    lead: 'zayavka'
  };

  function reach(goalId) {
    if (!goalId || typeof window.ym !== 'function') {
      return;
    }
    try {
      window.ym(COUNTER_ID, 'reachGoal', goalId);
    } catch (e) {
      /* ignore */
    }
  }

  function bindGoal(el, goalKey) {
    if (!el || el.getAttribute('data-lx-ym-bound') === '1') {
      return;
    }
    var custom = el.getAttribute('data-lx-ym-goal');
    var goalId = custom || GOALS[goalKey] || goalKey;
    el.setAttribute('data-lx-ym-bound', '1');
    el.addEventListener(
      'click',
      function () {
        reach(goalId);
      },
      { passive: true }
    );
  }

  function goalFromHref(href) {
    if (!href) {
      return '';
    }
    var h = String(href).toLowerCase();
    if (h.indexOf('tel:') === 0) {
      return GOALS.phone;
    }
    if (h.indexOf('t.me/') !== -1) {
      return GOALS.telegram;
    }
    if (h.indexOf('wa.me/') !== -1 || h.indexOf('api.whatsapp.com/') !== -1) {
      return GOALS.messenger;
    }
    if (h.indexOf('max.ru/') !== -1) {
      return GOALS.messenger;
    }
    return '';
  }

  function isYmHandledAnchor(el) {
    if (!el || el.nodeName !== 'A') {
      return false;
    }
    if (el.getAttribute('data-lx-ym-bound') === '1') {
      return true;
    }
    if (el.hasAttribute('data-lx-ym-goal')) {
      return true;
    }
    return false;
  }

  function initDelegatedHrefGoals() {
    document.addEventListener('click', function (ev) {
      var node = ev.target;
      var anchor = null;
      while (node && node !== document) {
        if (node.nodeName === 'A') {
          anchor = node;
          break;
        }
        node = node.parentNode;
      }
      if (!anchor || isYmHandledAnchor(anchor)) {
        return;
      }
      var href = anchor.getAttribute('href');
      if (!href) {
        href = anchor.href;
      }
      var goal = goalFromHref(href);
      if (goal) {
        reach(goal);
      }
    }, false);
  }

  function init() {
    var tgSelectors = [
      '#link-telegram',
      '#hero-telegram',
      '#dock-telegram',
      '#form-alt-telegram',
      '.lx-mobile-bar-btn[data-lx-icon="telegram"]',
      '.lx-site-footer-social-btn--tg'
    ];
    tgSelectors.forEach(function (sel) {
      var list = document.querySelectorAll(sel);
      var i;
      for (i = 0; i < list.length; i += 1) {
        bindGoal(list[i], 'telegram');
      }
    });

    ['#link-whatsapp', '#form-alt-whatsapp', '.lx-mobile-bar-btn[data-lx-icon="whatsapp"]', '.lx-site-footer-social-btn--wa'].forEach(function (sel) {
      var list = document.querySelectorAll(sel);
      var j;
      for (j = 0; j < list.length; j += 1) {
        bindGoal(list[j], 'whatsapp');
      }
    });

    ['#link-max', '#hero-max', '#dock-max', '#form-alt-max', '.lx-dock-max', '.lx-mobile-bar-btn--max', '.lx-mobile-bar-btn[data-lx-icon="max-white"]'].forEach(function (sel) {
      var list = document.querySelectorAll(sel);
      var k;
      for (k = 0; k < list.length; k += 1) {
        bindGoal(list[k], 'max');
      }
    });

    var telLinks = document.querySelectorAll('a[href^="tel:"]');
    var t;
    for (t = 0; t < telLinks.length; t += 1) {
      bindGoal(telLinks[t], 'phone');
    }

    var customGoals = document.querySelectorAll('[data-lx-ym-goal]');
    var c;
    for (c = 0; c < customGoals.length; c += 1) {
      bindGoal(customGoals[c], customGoals[c].getAttribute('data-lx-ym-goal'));
    }

    var form = document.getElementById('lireate-lead-form');
    if (form) {
      form.addEventListener('submit', function () {
        if (form.checkValidity()) {
          reach(GOALS.lead);
        }
      });
    }

    initDelegatedHrefGoals();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();