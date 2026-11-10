(function (root) {
  'use strict';

  var REQUIRED_HIDDEN = ['mail_token', 'mail', 'form-type', 'tomail', 'consent_form'];

  function currentSpamGuard(form) {
    if (root.lireateMailSpamGuard) {
      return String(root.lireateMailSpamGuard);
    }
    var spamHidden = form && form.querySelector('input[name="fuck_spamers"]');
    return spamHidden ? String(spamHidden.value || '') : '';
  }

  function normalizePhoneRu(raw) {
    var digits = (raw || '').replace(/\D/g, '');
    if (digits.length === 11 && (digits.charAt(0) === '7' || digits.charAt(0) === '8')) {
      return digits.charAt(0) === '8' ? '7' + digits.slice(1) : digits;
    }
    if (digits.length === 10) {
      return '7' + digits;
    }
    return '';
  }

  function parseMailPhpResponse(text) {
    var trimmed = String(text || '').trim();
    if (trimmed === '') {
      return {
        ok: false,
        emptyBody: true,
        showFallback: true,
        message:
          'Сервер принял запрос, но не вернул ответ. Заявка могла не дойти — позвоните или напишите в мессенджер ниже.'
      };
    }
    if (/^fuck spamers!/i.test(trimmed)) {
      return {
        ok: false,
        spamGuard: true,
        showFallback: true,
        message: 'Не удалось отправить с этого окна (устарела защита формы). Обновите страницу или напишите нам в Telegram.'
      };
    }
    var data;
    try {
      data = JSON.parse(trimmed);
    } catch (e) {
      return {
        ok: false,
        showFallback: true,
        message: 'Сервер вернул неожиданный ответ. Попробуйте ещё раз или свяжитесь с нами по телефону / в мессенджере.'
      };
    }
    if (data === 'success' || (Array.isArray(data) && data[0] === 'success')) {
      return { ok: true };
    }
    if (Array.isArray(data) && data[0] === 'error' && data[1] && typeof data[1] === 'object') {
      var parts = [];
      Object.keys(data[1]).forEach(function (key) {
        if (data[1][key]) {
          parts.push(String(data[1][key]));
        }
      });
      return { ok: false, fields: data[1], message: parts.join(' ') || 'Проверьте поля формы.' };
    }
    if (data && typeof data === 'object' && data.consent_request) {
      return { ok: false, message: String(data.consent_request) };
    }
    return {
      ok: false,
      showFallback: true,
      message: 'Не удалось отправить заявку. Попробуйте позже или свяжитесь с нами напрямую.'
    };
  }

  function validateMailLeadForm(form) {
    if (!form) {
      return { ok: false, message: 'Форма заявки не найдена.' };
    }
    var token = form.querySelector('input[name="mail_token"]');
    if (!token || !String(token.value || '').trim()) {
      return {
        ok: false,
        missingMailToken: true,
        showFallback: true,
        message: 'Не удалось подготовить заявку (mail_token). Обновите страницу или напишите в мессенджер.'
      };
    }
    for (var i = 0; i < REQUIRED_HIDDEN.length; i++) {
      var name = REQUIRED_HIDDEN[i];
      if (name === 'mail_token') {
        continue;
      }
      var el = form.querySelector('input[type="hidden"][name="' + name + '"]');
      if (!el || String(el.value || '').trim() === '') {
        return {
          ok: false,
          showFallback: true,
          message: 'Форма настроена неполностью (' + name + '). Обновите страницу или свяжитесь с нами напрямую.'
        };
      }
    }
    return { ok: true };
  }

  function buildMailLeadBody(form, phoneDigits, nameOptional) {
    var fd = new FormData();
    var nodes = form.querySelectorAll('input[type="hidden"]');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (!el.name) {
        continue;
      }
      fd.append(el.name, el.value);
    }
    var checks = form.querySelectorAll('input[type="checkbox"][name]:checked') || [];
    for (var c = 0; c < checks.length; c++) {
      var box = checks[c];
      if (box.name) {
        fd.set(box.name, box.value || '1');
      }
    }
    fd.set('tel', phoneDigits);
    if (nameOptional) {
      fd.set('name', nameOptional);
    }
    fd.set('consent_request', '1');
    fd.set('fuck_spamers', currentSpamGuard(form));
    var tokenNode = form.querySelector('input[name="mail_token"]');
    if (tokenNode && tokenNode.value) {
      fd.set('mail_token', tokenNode.value);
    }
    fd.set('mail', 'mailorder');
    if (!fd.get('form-type')) {
      fd.set('form-type', 'order');
    }
    var spamHidden = form.querySelector('input[name="fuck_spamers"]');
    if (spamHidden) {
      spamHidden.value = fd.get('fuck_spamers');
    }
    if (typeof root.lireateUtmFill === 'function') {
      root.lireateUtmFill(form);
      var utmNames = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid', 'ym_uid', 'lr_referrer', 'lr_landing'];
      for (var u = 0; u < utmNames.length; u++) {
        var key = utmNames[u];
        var inp = form.querySelector('input[name="' + key + '"]');
        if (inp && inp.value) {
          fd.set(key, inp.value);
        }
      }
    }
    return fd;
  }

  function leadFallbackHint() {
    return 'Напишите в Telegram или позвоните — менеджер свяжется с вами.';
  }

  function submitMailLead(form, options) {
    var opts = options || {};
    var action = form.getAttribute('data-lx-mail-action') || '/mail.php';
    var mock = form.getAttribute('data-lx-mail-mock') === '1' || opts.mock === true;
    if (mock) {
      return Promise.resolve(parseMailPhpResponse(opts.mockResponse || '["success"]'));
    }
    var validation = validateMailLeadForm(form);
    if (!validation.ok) {
      return Promise.resolve(validation);
    }
    var phoneDigits = opts.phoneDigits || '';
    var nameOptional = opts.nameOptional || '';
    var fd = buildMailLeadBody(form, phoneDigits, nameOptional);
    return fetch(action, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { Accept: 'application/json, text/plain, */*' }
    }).then(function (res) {
      return res.text().then(function (text) {
        var parsed = parseMailPhpResponse(text);
        if (!parsed.ok && res.status === 422 && !parsed.message) {
          parsed.message = 'Подтвердите согласие на обработку персональных данных.';
        }
        if (!parsed.ok && res.status === 200 && parsed.emptyBody) {
          parsed.showFallback = true;
        }
        parsed.httpStatus = res.status;
        return parsed;
      });
    });
  }

  root.lireateLeadMail = {
    normalizePhoneRu: normalizePhoneRu,
    parseMailPhpResponse: parseMailPhpResponse,
    validateMailLeadForm: validateMailLeadForm,
    buildMailLeadBody: buildMailLeadBody,
    submitMailLead: submitMailLead,
    currentSpamGuard: currentSpamGuard,
    leadFallbackHint: leadFallbackHint
  };
})(typeof window !== 'undefined' ? window : this);
