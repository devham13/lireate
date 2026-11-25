(function () {
  'use strict';

  function counterId() {
    var root = document.documentElement;
    var fromAttr = parseInt(root.getAttribute('data-lireate-metrika-id') || '0', 10);
    if (fromAttr) {
      return fromAttr;
    }
    var m = document.documentElement.innerHTML.match(/\bym\s*\(\s*(\d{5,})\s*,/);
    return m ? parseInt(m[1], 10) : 0;
  }

  var COUNTER_ID = counterId();
  if (!COUNTER_ID) {
    return;
  }

  var GOALS = {
    telegram: 'perehod_v_telegramm',
    whatsapp: 'perehod_v_messendzher',
    max: 'perehod_v_messendzher',
    phone: 'klik_po_nomeru_telefona',
    messenger: 'perehod_v_messendzher',
    lead: 'zayavka',
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

  // audit07 A5: elements whose clicks are already sent by another script.
  // [data-lu-goal] -> inline handler on /uslugi/; [data-lx-ym-goal] ->
  // delegated handler in lireate-home-hero.js and lireate-docs.js (when loaded).
  var OTHER_GOAL_SCRIPT = !!document.querySelector(
    'script[src*="lireate-home-hero.js"], script[src*="lireate-docs.js"]'
  );

  function handledElsewhere(el) {
    if (!el || !el.closest) {
      return false;
    }
    if (el.closest('[data-lu-goal]')) {
      return true;
    }
    return OTHER_GOAL_SCRIPT && !!el.closest('[data-lx-ym-goal]');
  }

  function bindGoal(el, goalKey) {
    if (!el || el.getAttribute('data-lx-ym-bound') === '1' || handledElsewhere(el)) {
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
      { passive: true },
    );
  }

  function init() {
    [
      '#link-telegram',
      '#hero-telegram',
      '#dock-telegram',
      '#form-alt-telegram',
      '.lx-mobile-bar-btn[data-lx-icon="telegram"]',
      '.lx-site-footer-social-btn--tg',
    ].forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        bindGoal(el, 'telegram');
      });
    });

    [
      '#link-whatsapp',
      '#dock-whatsapp',
      '#form-alt-whatsapp',
      '.lx-mobile-bar-btn[data-lx-icon="whatsapp"]',
      '.lx-site-footer-social-btn--wa',
    ].forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        bindGoal(el, 'whatsapp');
      });
    });

    [
      '#link-max',
      '#hero-max',
      '#dock-max',
      '#form-alt-max',
      '.lx-dock-max',
      '.lx-mobile-bar-btn--max',
      '.lx-mobile-bar-btn[data-lx-icon="max-white"]',
    ].forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        bindGoal(el, 'max');
      });
    });

    document.querySelectorAll('a[href^="tel:"]').forEach(function (el) {
      bindGoal(el, 'phone');
    });

    document.querySelectorAll('[data-lx-ym-goal]').forEach(function (el) {
      bindGoal(el, el.getAttribute('data-lx-ym-goal'));
    });
  }

  // audit07 A5: delegated fallback by href for links that are not bound above
  // (body buttons, footer MAX, links added later). No preventDefault.
  function goalByHref(href) {
    var h = String(href || '').replace(/^\s+/, '').toLowerCase();
    if (h.indexOf('tel:') === 0) {
      return GOALS.phone;
    }
    if (!/^(https?:)?\/\//.test(h)) {
      return '';
    }
    h = h.replace(/^(https?:)?\/\//, '').replace(/^www\./, '');
    if (h.indexOf('t.me/') === 0 || h.indexOf('telegram.me/') === 0) {
      return GOALS.telegram;
    }
    if (h.indexOf('wa.me/') === 0 || h.indexOf('api.whatsapp.com/') === 0) {
      return GOALS.whatsapp;
    }
    if (h.indexOf('max.ru/') === 0) {
      return GOALS.max;
    }
    return '';
  }

  // Service landings (PR #91) load assets/lireate-shared/lireate-metrika-goals.js
  // with its own href delegate; then this one stays off to avoid double goals.
  // Shared contract for both delegates: skip anchors with data-lx-ym-bound="1"
  // and events already marked with ev.lxYmGoalSent.
  var SERVICE_DELEGATE = !!document.querySelector('script[src*="lireate-shared/lireate-metrika-goals.js"]');

  if (!SERVICE_DELEGATE) {
    document.addEventListener(
      'click',
      function (ev) {
        if (ev.lxYmGoalSent) {
          return;
        }
        var t = ev.target;
        if (!t || !t.closest) {
          return;
        }
        var a = t.closest('a[href]');
        if (!a || a.getAttribute('data-lx-ym-bound') === '1' || handledElsewhere(a)) {
          return;
        }
        var goalId = goalByHref(a.getAttribute('href'));
        if (goalId) {
          ev.lxYmGoalSent = true;
          reach(goalId);
        }
      },
      true
    );
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
