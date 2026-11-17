(function () {
  'use strict';

  var Pay = typeof LireatePatentPayment !== 'undefined' ? LireatePatentPayment : null;
  var PAY_STORAGE_KEY = 'lireate-patent-pay-v1';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function fireMetrika(toolId, mode, status) {
    if (typeof ym !== 'function') {
      return;
    }
    var id = document.documentElement.getAttribute('data-lireate-metrika-id');
    if (!id) {
      return;
    }
    try {
      ym(parseInt(id, 10), 'reachGoal', 'lireate_patent_tool', {
        tool: toolId,
        mode: mode || '',
        status: status || '',
      });
    } catch (e) {
      /* ignore */
    }
  }

  function readPayDraft() {
    try {
      var raw = sessionStorage.getItem(PAY_STORAGE_KEY);
      if (!raw) {
        return null;
      }
      return JSON.parse(raw);
    } catch (err) {
      return null;
    }
  }

  function clearPayDraft() {
    try {
      sessionStorage.removeItem(PAY_STORAGE_KEY);
    } catch (err) {
      /* ignore */
    }
  }

  function mountTool(root, html) {
    var host = qs('[data-lx-pay-tool]', root) || root;
    host.innerHTML = html;
    return host;
  }

  function renderQr(host, payload) {
    if (typeof QRCode === 'undefined') {
      host.textContent = 'QR: библиотека недоступна';
      return;
    }
    host.innerHTML = '';
    /* global QRCode */
    new QRCode(host, { text: payload, width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
  }

  function fetchProfiles(jsonUrl, cb) {
    if (!jsonUrl) {
      cb(null);
      return;
    }
    fetch(jsonUrl, { credentials: 'same-origin' })
      .then(function (r) {
        return r.json();
      })
      .then(cb)
      .catch(function () {
        cb(null);
      });
  }

  function initPatentPay(root) {
    var toolId = 'oplata-patentnyh-poshlin';
    var jsonUrl = root.getAttribute('data-lx-pay-profiles-json');
    fireMetrika(toolId, 'open', 'ok');
    mountTool(
      root,
      '<div class="lx-tool-grid"><div>' +
        '<p class="lx-hint lx-pay-draft-note" data-lx-pay-draft-note hidden></p>' +
        '<div class="lx-field"><label for="lx-pay-sum">Сумма к оплате, ₽</label>' +
        '<input id="lx-pay-sum" type="number" min="1" step="1" data-lx-pay-sum></div>' +
        '<div class="lx-field"><label for="lx-pay-purpose">Назначение платежа</label>' +
        '<textarea id="lx-pay-purpose" data-lx-pay-purpose rows="4" placeholder="Пункт таблицы пошлин, номер заявки…"></textarea></div>' +
        '<p class="lx-hint">Данные остаются в браузере. Образец — <strong>черновик</strong>: сверьте реквизиты на <a href="https://rospatent.gov.ru/ru/activities/dues/req" target="_blank" rel="noopener noreferrer">rospatent.gov.ru</a>.</p>' +
        '<div class="lx-actions">' +
        '<button type="button" class="lx-btn lx-btn-primary" data-lx-pay-build>Сформировать QR</button>' +
        '<button type="button" class="lx-btn lx-btn-ghost" data-lx-pay-print>Печать</button>' +
        '<button type="button" class="lx-btn lx-btn-ghost" data-lx-pay-clear>Очистить</button>' +
        '</div>' +
        '<p class="lx-warn" data-lx-pay-error hidden></p>' +
        '</div><aside class="lx-result-panel" data-lx-pay-result hidden>' +
        '<p class="lx-field-label">Реквизиты получателя</p>' +
        '<div data-lx-pay-req-lines></div>' +
        '<p class="lx-field-label">Универсальный платёжный код</p>' +
        '<pre class="lx-pay-payload" data-lx-pay-payload></pre>' +
        '<div class="lx-qr-box" data-lx-pay-qr></div>' +
        '<p class="lx-hint" data-lx-pay-meta></p>' +
        '</aside></div>'
    );

    var sumEl = qs('[data-lx-pay-sum]', root);
    var purposeEl = qs('[data-lx-pay-purpose]', root);
    var errEl = qs('[data-lx-pay-error]', root);
    var result = qs('[data-lx-pay-result]', root);
    var draftNote = qs('[data-lx-pay-draft-note]', root);

    var draft = readPayDraft();
    if (draft && draft.total) {
      sumEl.value = String(draft.total);
      if (Pay && draft.lines) {
        purposeEl.value = Pay.defaultPurposeFromLines(draft.lines);
      }
      draftNote.hidden = false;
      draftNote.textContent =
        'Из калькулятора: ' + (draft.source || 'пошлины') + ', сумма ' + (draft.total || '') + ' ₽.';
      clearPayDraft();
    }

    var profilesData = null;
    fetchProfiles(jsonUrl, function (data) {
      profilesData = data;
      if (Pay && data) {
        Pay.setData(data);
      }
      qs('[data-lx-pay-build]', root).addEventListener('click', function () {
        if (!Pay) {
          return;
        }
        var built = Pay.buildSt00012('rospatent_patent_dues', {
          sumRub: Number(sumEl.value),
          purpose: purposeEl.value,
        });
        if (!built.ok) {
          errEl.hidden = false;
          errEl.textContent =
            built.error === 'unconfirmed'
              ? 'Профиль не подтверждён — используйте официальную страницу реквизитов.'
              : 'Проверьте сумму и назначение платежа.';
          result.hidden = true;
          return;
        }
        errEl.hidden = true;
        result.hidden = false;
        var p = built.profile;
        var lines = [
          ['Получатель', p.fields.Name],
          ['ИНН / КПП', p.fields.PayeeINN + ' / ' + p.fields.KPP],
          ['БИК', p.fields.BIC],
          ['Банк', p.fields.BankName],
          ['Казначейский счёт', p.fields.PersonalAcc],
          ['ЕКС', p.fields.CorrespAcc],
          ['КБК', p.fields.CBC],
          ['ОКТМО', p.fields.OKTMO],
          ['Сумма', Pay.formatRub(built.sumRub)],
        ];
        qs('[data-lx-pay-req-lines]', root).innerHTML = lines
          .map(function (row) {
            return (
              '<div class="lx-line-row"><span>' +
              row[0] +
              '</span><span class="lx-line-amt">' +
              row[1] +
              '</span></div>'
            );
          })
          .join('');
        qs('[data-lx-pay-payload]', root).textContent = built.payload;
        renderQr(qs('[data-lx-pay-qr]', root), built.payload);
        qs('[data-lx-pay-meta]', root).innerHTML =
          'Сверка: ' +
          (profilesData && profilesData.checked ? profilesData.checked : '—') +
          '. Источник: <a href="' +
          p.sourceUrl +
          '" target="_blank" rel="noopener noreferrer">PDF Роспатента</a>. Формат ST00012 — <a href="https://www.cbr.ru/explan/Psystem/upc/" target="_blank" rel="noopener noreferrer">Банк России</a>.';
        fireMetrika(toolId, 'qr', 'built');
      });
    });

    qs('[data-lx-pay-clear]', root).addEventListener('click', function () {
      sumEl.value = '';
      purposeEl.value = '';
      result.hidden = true;
      errEl.hidden = true;
    });
    qs('[data-lx-pay-print]', root).addEventListener('click', function () {
      window.print();
    });
  }

  function initFipsPay(root) {
    var toolId = 'oplata-uslug-fips';
    var jsonUrl = root.getAttribute('data-lx-pay-profiles-json');
    fireMetrika(toolId, 'open', 'ok');
    mountTool(
      root,
      '<div class="lx-result-panel">' +
        '<p class="lx-warn">Реквизиты платных услуг ФИПС в этой сборке не подтверждены автоматически (официальный сайт fips.ru недоступен из среды сверки). QR не формируется.</p>' +
        '<p class="lx-hint">Сверьте данные на официальных страницах и в платёжном документе банка:</p>' +
        '<ul><li><a href="https://fips.ru/vse-uslugi/rekvizity-fips-dlya-oplaty-uslug-fips-okazyvaemykh-na-platnoy-osnove.php" target="_blank" rel="noopener noreferrer">Реквизиты ФИПС для платных услуг</a></li>' +
        '<li><a href="https://fips.ru/vse-uslugi/uslugi-predostavlyaemye-fips-na-platnoy-osnove-.php" target="_blank" rel="noopener noreferrer">Перечень платных услуг</a></li>' +
        '<li><a href="https://rospatent.gov.ru/ru/activities/dues/req" target="_blank" rel="noopener noreferrer">Реквизиты Роспатента (пошлины)</a></li></ul>' +
        '<div class="lx-field"><label for="lx-fips-sum">Сумма по счёту или тарифу, ₽ (справочно)</label>' +
        '<input id="lx-fips-sum" type="number" min="0" step="1" data-lx-fips-sum></div>' +
        '<p class="lx-hint" data-lx-fips-meta></p></div>'
    );
    fetchProfiles(jsonUrl, function (data) {
      var meta = qs('[data-lx-fips-meta]', root);
      if (!data || !meta) {
        return;
      }
      var prof = data.profiles && data.profiles.fips_paid_services;
      if (prof) {
        meta.textContent = 'Сверка: ' + data.checked + '. Профиль «' + prof.title + '»: confirmed=false.';
      }
    });
  }

  function initEvmSearch(root) {
    var toolId = 'poisk-platezha-evm';
    fireMetrika(toolId, 'open', 'ok');
    mountTool(
      root,
      '<div class="lx-seg" role="tablist"><button type="button" data-lx-evm-mode="org" aria-pressed="true">Организация</button>' +
        '<button type="button" data-lx-evm-mode="person" aria-pressed="false">Физическое лицо</button></div>' +
        '<div data-lx-evm-panel="org">' +
        '<div class="lx-field"><label>Номер платёжного документа</label><input type="text" data-lx-evm-doc></div>' +
        '<div class="lx-field"><label>Дата</label><input type="date" data-lx-evm-date></div>' +
        '<div class="lx-field"><label>ИНН</label><input type="text" data-lx-evm-inn></div>' +
        '<div class="lx-field"><label>КПП</label><input type="text" data-lx-evm-kpp></div></div>' +
        '<div data-lx-evm-panel="person" hidden>' +
        '<div class="lx-field"><label>Номер платёжного документа</label><input type="text" data-lx-evm-doc-p></div>' +
        '<div class="lx-field"><label>Дата</label><input type="date" data-lx-evm-date-p></div>' +
        '<div class="lx-field"><label>ФИО</label><input type="text" data-lx-evm-fio></div></div>' +
        '<div class="lx-actions"><button type="button" class="lx-btn lx-btn-primary" data-lx-evm-check>Сверить в официальном контуре</button></div>' +
        '<p class="lx-warn">Поиск не выполняется на сервере LIREATE. История не сохраняется.</p>' +
        '<div class="lx-result-panel" data-lx-evm-result hidden><p data-lx-evm-result-text></p></div>' +
        '<p class="lx-hint">Госпошлина ПрЭВМ: <a href="https://rospatent.gov.ru/content/uploadfiles/rekviziti-pril2.pdf" target="_blank" rel="noopener noreferrer">реквизиты (прил. №2)</a> · <a href="https://rospatent.gov.ru/ru/activities/dues/stateduty/gos_poshl" target="_blank" rel="noopener noreferrer">правила оплаты</a></p>'
    );

    var orgBtn = qs('[data-lx-evm-mode="org"]', root);
    var personBtn = qs('[data-lx-evm-mode="person"]', root);
    var orgPanel = qs('[data-lx-evm-panel="org"]', root);
    var personPanel = qs('[data-lx-evm-panel="person"]', root);
    function setMode(mode) {
      var org = mode === 'org';
      orgBtn.setAttribute('aria-pressed', org ? 'true' : 'false');
      personBtn.setAttribute('aria-pressed', org ? 'false' : 'true');
      orgPanel.hidden = !org;
      personPanel.hidden = org;
    }
    orgBtn.addEventListener('click', function () {
      setMode('org');
    });
    personBtn.addEventListener('click', function () {
      setMode('person');
    });
    qs('[data-lx-evm-check]', root).addEventListener('click', function () {
      var out = qs('[data-lx-evm-result]', root);
      var text = qs('[data-lx-evm-result-text]', root);
      out.hidden = false;
      text.innerHTML =
        'Подтверждённый API поиска платежа не подключён. Используйте <a href="https://new.fips.ru/office/" target="_blank" rel="noopener noreferrer">личный кабинет ФИПС</a> или каналы, указанные на <a href="https://rospatent.gov.ru/ru/activities/dues/stateduty/gos_poshl" target="_blank" rel="noopener noreferrer">rospatent.gov.ru</a>. Введённые поля никуда не передавались.';
      fireMetrika(toolId, 'search', 'reference');
    });
  }

  function initOldApps(root) {
    var toolId = 'starye-zayavki';
    fireMetrika(toolId, 'open', 'ok');
    mountTool(
      root,
      '<div class="lx-field"><label for="lx-old-num">Номер заявки (для справки)</label>' +
        '<input id="lx-old-num" type="text" data-lx-old-num placeholder="Например, 2025123456"></div>' +
        '<div class="lx-actions">' +
        '<a class="lx-btn lx-btn-primary" href="https://new.fips.ru/office/" target="_blank" rel="noopener noreferrer">Открыть личный кабинет ФИПС</a>' +
        '<a class="lx-btn lx-btn-ghost" href="https://kpsrtz.fips.ru/" target="_blank" rel="noopener noreferrer">АРМ «Регистратор»</a>' +
        '</div>' +
        '<p class="lx-hint">LIREATE не передаёт номер заявки и не принимает логин/пароль ФИПС. Авторизация — только на стороне ФИПС.</p>' +
        '<p class="lx-hint"><a href="https://www.fips.ru/podacha-zayavki/podacha-zayavki-na-tovarnyy-znak/user-guide-api-arm2024.pdf" target="_blank" rel="noopener noreferrer">Руководство пользователя АРМ</a></p>'
    );
    qs('[data-lx-old-num]', root).addEventListener('change', function () {
      fireMetrika(toolId, 'number', 'local');
    });
  }

  function boot() {
    document.querySelectorAll('[data-lx-patent-pay="patent"]').forEach(initPatentPay);
    document.querySelectorAll('[data-lx-patent-pay="fips"]').forEach(initFipsPay);
    document.querySelectorAll('[data-lx-patent-evm-search]').forEach(initEvmSearch);
    document.querySelectorAll('[data-lx-patent-old-apps]').forEach(initOldApps);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();