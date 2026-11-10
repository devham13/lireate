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
      document.querySelectorAll(sel).forEach(function (el) {
        bindGoal(el, 'telegram');
      });
    });

    ['#link-whatsapp', '.lx-mobile-bar-btn[data-lx-icon="whatsapp"]', '.lx-site-footer-social-btn--wa'].forEach(
      function (sel) {
        document.querySelectorAll(sel).forEach(function (el) {
          bindGoal(el, 'whatsapp');
        });
      }
    );

    ['#link-max', '#hero-max', '#dock-max', '#form-alt-max', '.lx-dock-max'].forEach(function (sel) {
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

    var form = document.getElementById('lireate-lead-form');
    if (form) {
      form.addEventListener('submit', function () {
        if (form.checkValidity()) {
          reach(GOALS.lead);
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
