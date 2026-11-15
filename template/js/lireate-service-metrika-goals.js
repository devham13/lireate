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

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
