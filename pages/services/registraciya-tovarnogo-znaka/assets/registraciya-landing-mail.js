(function (root) {
  'use strict';

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
    var data;
    try {
      data = JSON.parse(text || 'null');
    } catch (e) {
      return { ok: false, message: 'Сервер вернул неожиданный ответ. Попробуйте ещё раз.' };
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
    return { ok: false, message: 'Не удалось отправить заявку. Попробуйте позже.' };
  }

  /**
   * Собирает тело POST для /mail.php (как legacy ajax_form + consent-gate + lireate-utm.js).
   */
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
    fd.set('tel', phoneDigits);
    if (nameOptional) {
      fd.set('name', nameOptional);
    }
    fd.set('consent_request', '1');
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

  function submitMailLead(form, options) {
    var opts = options || {};
    var action = form.getAttribute('data-lx-mail-action') || '/mail.php';
    var mock = form.getAttribute('data-lx-mail-mock') === '1' || opts.mock === true;
    if (mock) {
      return Promise.resolve(parseMailPhpResponse(opts.mockResponse || '["success"]'));
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
        parsed.httpStatus = res.status;
        return parsed;
      });
    });
  }

  root.lireateLandingMail = {
    normalizePhoneRu: normalizePhoneRu,
    parseMailPhpResponse: parseMailPhpResponse,
    buildMailLeadBody: buildMailLeadBody,
    submitMailLead: submitMailLead
  };
})(typeof window !== 'undefined' ? window : this);
