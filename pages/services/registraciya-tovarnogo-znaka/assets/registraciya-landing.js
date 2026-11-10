(function () {
  'use strict';

  var root = document.documentElement;
  var COUNTER_ID = parseInt(root.getAttribute('data-lireate-metrika-id') || '0', 10);

  function reach(goalId, params) {
    if (!goalId || typeof window.ym !== 'function' || !COUNTER_ID) {
      return;
    }
    try {
      if (params && typeof params === 'object') {
        window.ym(COUNTER_ID, 'reachGoal', goalId, params);
      } else {
        window.ym(COUNTER_ID, 'reachGoal', goalId);
      }
    } catch (e) {
      /* ignore */
    }
  }

  function scrollToContact(focusForm) {
    var contact = document.getElementById('contact');
    if (!contact) {
      return;
    }
    contact.scrollIntoView({ behavior: 'smooth', block: 'start' });
    if (focusForm) {
      var title = document.getElementById('lf-title');
      if (title && typeof title.focus === 'function') {
        try {
          title.focus({ preventScroll: true });
        } catch (err) {
          title.focus();
        }
      }
    }
  }

  function mapTzFormErrorReason(context, result) {
    if (context === 'network') {
      return 'network';
    }
    if (context === 'validation_phone') {
      return 'validation_phone';
    }
    if (context === 'validation_consent') {
      return 'validation_consent';
    }
    if (context === 'client_unavailable') {
      return 'client_unavailable';
    }
    if (context === 'server' || (result && !result.ok && !result.assumedSuccess)) {
      return 'server_rejected';
    }
    if (result && result.assumedSuccess) {
      return 'assumed_success';
    }
    return 'unknown';
  }

  function initReviewMetrics() {
    document.querySelectorAll('[data-lx-review-open]').forEach(function (btn) {
      btn.addEventListener(
        'click',
        function () {
          var card = btn.closest('.lx-review-frame');
          var placement = card ? 'reviews_rail' : 'page';
          var kind = btn.getAttribute('data-lx-review-video') === '1' ? 'video' : 'dialog';
          reach('tz_review_open', { placement: placement, kind: kind });
        },
        { passive: true }
      );
    });
  }

  function bindFormOpen() {
    document.querySelectorAll('[data-lx-tz-form-open]').forEach(function (el) {
      el.addEventListener('click', function (ev) {
        var href = el.getAttribute('href') || '';
        if (href.indexOf('#contact') === 0) {
          ev.preventDefault();
          scrollToContact(true);
        }
        reach('tz_form_open', { placement: el.getAttribute('data-lx-tz-form-open') || 'cta' });
      });
    });
  }

  function initHeroChannelMenu() {
    var toggle = document.getElementById('hero-messenger-toggle');
    var menu = document.getElementById('hero-messenger-menu');
    if (!toggle || !menu) {
      return;
    }
    var items = menu.querySelectorAll('[role="menuitem"]');

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        menu.removeAttribute('hidden');
      } else {
        menu.setAttribute('hidden', '');
      }
    }

    function closeMenu() {
      setOpen(false);
    }

    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') === 'true';
      setOpen(!open);
      if (!open) {
        var first = items[0];
        if (first && typeof first.focus === 'function') {
          first.focus();
        }
      }
    });

    toggle.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        setOpen(true);
        if (items[0]) {
          items[0].focus();
        }
      }
    });

    menu.addEventListener('keydown', function (e) {
      var idx = -1;
      items.forEach(function (el, i) {
        if (el === document.activeElement) {
          idx = i;
        }
      });
      if (e.key === 'Escape') {
        e.preventDefault();
        closeMenu();
        toggle.focus();
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        var next = items[idx + 1] || items[0];
        if (next) {
          next.focus();
        }
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        var prev = items[idx - 1] || items[items.length - 1];
        if (prev) {
          prev.focus();
        }
      } else if (e.key === 'Tab' && !e.shiftKey && idx === items.length - 1) {
        closeMenu();
      }
    });

    document.addEventListener('click', function (e) {
      if (!menu.hasAttribute('hidden') && !menu.contains(e.target) && e.target !== toggle) {
        closeMenu();
      }
    });

    items.forEach(function (el) {
      el.addEventListener('click', function () {
        closeMenu();
      });
    });
  }

  function bindMessengers() {
    document.querySelectorAll('[data-lx-tz-messenger]').forEach(function (el) {
      el.addEventListener(
        'click',
        function () {
          reach('tz_messenger_click', {
            channel: el.getAttribute('data-lx-tz-messenger') || 'unknown',
            placement: el.getAttribute('data-lx-tz-placement') || 'page'
          });
        },
        { passive: true }
      );
    });
  }

  function initLandingLeadForm() {
    var form = document.getElementById('lireate-lead-form');
    if (!form || form.getAttribute('data-lx-lead-handler') !== 'landing') {
      return;
    }
    var phoneEl = document.getElementById('lf-phone');
    var nameEl = document.getElementById('lf-name');
    var consent = document.getElementById('lf-consent');
    var okEl = document.getElementById('lf-success');
    var errEl = document.getElementById('lf-error');
    var submitBtn = form.querySelector('.lx-form-submit');
    var successText =
      form.getAttribute('data-lx-success-text') ||
      'Заявка принята. Специалист свяжется с вами, чтобы уточнить детали для расчёта';
    var sent = false;
    var mailApi = window.lireateLeadMail;

    function showError(msg) {
      if (!errEl) {
        return;
      }
      errEl.textContent = msg;
      errEl.hidden = false;
    }

    function clearError() {
      if (errEl) {
        errEl.hidden = true;
        errEl.textContent = '';
      }
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearError();
      if (form.classList.contains('is-busy')) {
        return;
      }
      var phoneNorm = mailApi.normalizePhoneRu(phoneEl ? phoneEl.value : '');
      if (!phoneNorm) {
        reach('tz_form_error', { reason: mapTzFormErrorReason('validation_phone') });
        showError('Укажите корректный номер телефона России (+7 …).');
        if (phoneEl) {
          phoneEl.focus();
        }
        return;
      }
      if (consent && !consent.checked) {
        reach('tz_form_error', { reason: mapTzFormErrorReason('validation_consent') });
        showError('Нужно согласие на обработку персональных данных.');
        if (consent) {
          consent.focus();
        }
        return;
      }
      if (!mailApi || typeof mailApi.submitMailLead !== 'function') {
        reach('tz_form_error', { reason: mapTzFormErrorReason('client_unavailable') });
        showError('Отправка временно недоступна. Обновите страницу или напишите в мессенджер.');
        return;
      }
      form.classList.add('is-busy');
      if (submitBtn) {
        submitBtn.disabled = true;
      }
      var name = nameEl && nameEl.value ? nameEl.value.trim() : '';
      mailApi
        .submitMailLead(form, { phoneDigits: phoneNorm, nameOptional: name })
        .then(function (result) {
          if (result && result.ok) {
            if (okEl) {
              okEl.textContent = successText;
              okEl.classList.add('is-visible');
            }
            if (!sent) {
              sent = true;
              reach('tz_form_success');
              reach('zayavka');
            }
            clearError();
          } else {
            reach('tz_form_error', { reason: mapTzFormErrorReason('server', result) });
            showError((result && result.message) || 'Не удалось отправить заявку. Попробуйте ещё раз.');
          }
        })
        .catch(function () {
          reach('tz_form_error', { reason: 'network' });
          showError('Сеть недоступна. Проверьте подключение и отправьте заявку снова.');
        })
        .then(function () {
          form.classList.remove('is-busy');
          if (submitBtn) {
            submitBtn.disabled = false;
          }
        });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      bindFormOpen();
      initHeroChannelMenu();
      bindMessengers();
      initLandingLeadForm();
      initReviewMetrics();
    });
  } else {
    bindFormOpen();
    initHeroChannelMenu();
    bindMessengers();
    initLandingLeadForm();
    initReviewMetrics();
  }
})();
