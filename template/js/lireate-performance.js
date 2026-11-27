/* Accessible names for legacy CMS controls; no changes to their handlers. */
(function () {
  'use strict';
  function name(selector, text) {
    document.querySelectorAll(selector).forEach(function (node) {
      if (!node.getAttribute('aria-label') && !node.textContent.trim()) {
        node.setAttribute('aria-label', text);
      }
    });
  }
  name('#whatsapp_icon', 'Написать в WhatsApp');
  name('#telegram_icon', 'Написать в Telegram');
  name('a.topBarLogo', 'Лирейт — главная страница');
  var menuButton = document.querySelector('.topBarButton');
  var menu = document.getElementById('leftSideBar');
  if (menuButton && menu && menuButton.getAttribute('data-lx-menu') !== '1') {
    menuButton.setAttribute('data-lx-menu', '1');
    menuButton.setAttribute('role', 'button');
    menuButton.tabIndex = 0;
    if (!menuButton.getAttribute('aria-label')) {
      menuButton.setAttribute('aria-label', 'Меню');
    }
    menuButton.setAttribute('aria-controls', 'leftSideBar');
    var syncMenu = function () {
      var hidden = getComputedStyle(menu).left.charAt(0) === '-';
      menuButton.setAttribute('aria-expanded', hidden ? 'false' : 'true');
    };
    menuButton.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter' && event.key !== ' ') {
        return;
      }
      event.preventDefault();
      menuButton.click();
    });
    menuButton.addEventListener('click', function () {
      window.setTimeout(syncMenu, 0);
    });
    syncMenu();
  }
  if (!document.querySelector('main, [role="main"]')) {
    var content = document.querySelector('.lireate-blog-page, #lireate-home, #articleBox, #textBox, body > .container.content');
    if (content) content.setAttribute('role', 'main');
  }
  var max = document.querySelector('#max_icon img');
  if (max) {
    max.setAttribute('width', '63');
    max.setAttribute('height', '63');
  }
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || !form.classList || !form.classList.contains('ajax_form')) {
      return;
    }
    var name = form.querySelector('input[name="name"]');
    var phone = form.querySelector('input[name="tel"], input[type="tel"]');
    var message = '';
    var focus = null;
    var consent = form.querySelector('input[name="consent_request"]');
    if (
      consent &&
      consent.type === 'checkbox' &&
      (consent.required || consent.hasAttribute('required')) &&
      !consent.checked
    ) {
      message = 'Нужно согласие на обработку персональных данных.';
      focus = consent;
    } else if (name && name.required && !String(name.value || '').trim()) {
      message = 'Укажите, как к вам обращаться.';
      focus = name;
    } else if (phone) {
      var digits = String(phone.value || '').replace(/\D/g, '');
      var valid = (digits.length === 11 && (digits.charAt(0) === '7' || digits.charAt(0) === '8')) || digits.length === 10;
      if (!valid) {
        message = 'Укажите корректный номер телефона России (+7 …).';
        focus = phone;
      }
    }
    if (!message) {
      return;
    }
    event.preventDefault();
    event.stopImmediatePropagation();
    var error = form.querySelector('.lx-form-error, .lireate-consent-error');
    if (error) {
      error.textContent = message;
      error.hidden = false;
      error.style.display = 'block';
    }
    if (focus) {
      focus.focus();
    }
  }, true);
  /* mail.php has no error callback, so a failed request left the button disabled. */
  if (window.jQuery) {
    window.jQuery(document).ajaxError(function (_event, _xhr, settings) {
      var url = settings && settings.url ? String(settings.url) : '';
      if (url.indexOf('mail.php') === -1) {
        return;
      }
      document.querySelectorAll('form.ajax_form, form.ajax_form_main, form.simple_form, form.ajax_form_find').forEach(function (form) {
        form.querySelectorAll('[type="submit"]').forEach(function (button) {
          button.disabled = false;
        });
      });
    });
  }
}());
