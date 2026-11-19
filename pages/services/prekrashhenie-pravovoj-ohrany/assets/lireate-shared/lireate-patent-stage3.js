(function () {
  'use strict';

  var Pay = typeof LireatePatentPayment !== 'undefined' ? LireatePatentPayment : null;
  var PAY_STORAGE_KEY = 'lireate-patent-pay-v1';

  var PURPOSE_HINT =
    'В назначении указывается пункт Приложения к Положению, регистрационный номер заявки, патента, свидетельства, наименование действия. ' +
    'Платёж должен относиться к одной заявке, патенту или свидетельству. Поле 22 «Код» — идентификатор начисления; для физлиц поле 108 — «0», поля 106, 107, 109, 110 — «0», если иное не требуется формой.';

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

  var QR_MARGIN_MOD = 4;
  var QR_PX_PER_MOD_DESKTOP = 8;
  var QR_PX_PER_MOD_DESKTOP_MIN = 4;
  var QR_PX_PER_MOD_MOBILE_MIN = 3;
  var QR_HI_PX_PER_MOD = 10;

  /** ST00012 с кириллицей: только BYTE-сегмент (UTF-8), иначе смешанная сегментация ломает ряд сканеров/OpenCV. */
  function qrPayloadSegments(payload) {
    if (typeof TextEncoder !== 'undefined') {
      return [{ data: new TextEncoder().encode(payload), mode: 'byte' }];
    }
    return payload;
  }

  function qrRenderOpts() {
    return { errorCorrectionLevel: 'M' };
  }

  function qrModuleCount(payload) {
    if (typeof LireateQR !== 'undefined' && LireateQR.create) {
      try {
        return LireateQR.create(qrPayloadSegments(payload), qrRenderOpts()).modules.size;
      } catch (e) {
        /* ignore */
      }
    }
    return 41;
  }

  function qrHostInnerWidth(host) {
    var narrow = window.matchMedia('(max-width: 519px)').matches;
    if (narrow) {
      return Math.floor(window.innerWidth);
    }
    if (!host) {
      return 0;
    }
    var w = host.getBoundingClientRect().width;
    if (w < 20 && host.parentElement) {
      w = host.parentElement.getBoundingClientRect().width;
    }
    return Math.floor(w);
  }

  function qrPxPerMod(host, moduleCount) {
    var modulesTotal = moduleCount + QR_MARGIN_MOD * 2;
    var narrow = window.matchMedia('(max-width: 519px)').matches;
    var minPx = narrow ? QR_PX_PER_MOD_MOBILE_MIN : QR_PX_PER_MOD_DESKTOP_MIN;
    var targetPx = narrow ? QR_PX_PER_MOD_MOBILE_MIN : QR_PX_PER_MOD_DESKTOP;
    var inner = qrHostInnerWidth(host);
    var pad = narrow ? 32 : 20;
    var pxPerMod = targetPx;
    if (inner > modulesTotal + pad) {
      var fit = Math.floor((inner - pad) / modulesTotal);
      pxPerMod = Math.min(targetPx, Math.max(minPx, fit));
    }
    return pxPerMod;
  }

  function qrCssWidth(host, moduleCount) {
    var modulesTotal = moduleCount + QR_MARGIN_MOD * 2;
    return modulesTotal * qrPxPerMod(host, moduleCount);
  }

  function ensureQrDownloadBtn(host) {
    var panel = host.closest('.lx-result-panel') || host.parentElement;
    var btn = panel ? qs('[data-lx-qr-download]', panel) : null;
    if (!btn && panel) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'lx-btn lx-btn-ghost';
      btn.setAttribute('data-lx-qr-download', '');
      btn.textContent = 'Скачать QR (PNG)';
      host.parentNode.insertBefore(btn, host.nextSibling);
    }
    return btn;
  }

  function placeQrImg(host, url, cssPx, hiUrl, cb) {
    var wrap = document.createElement('div');
    wrap.className = 'lx-qr-img-wrap';
    var img = document.createElement('img');
    img.src = url;
    img.className = 'lx-qr-img';
    img.alt = 'QR-код для оплаты';
    img.width = cssPx;
    img.height = cssPx;
    img.style.width = cssPx + 'px';
    img.style.height = 'auto';
    img.style.aspectRatio = '1';
    wrap.appendChild(img);
    host.appendChild(wrap);
    if (hiUrl) {
      host.setAttribute('data-lx-qr-hi', hiUrl);
      var btn = ensureQrDownloadBtn(host);
      if (btn) {
        btn.hidden = false;
        btn.onclick = function () {
          var a = document.createElement('a');
          a.href = hiUrl;
          a.download = 'lireate-qr-st00012.png';
          a.rel = 'noopener';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        };
      }
    }
    if (cb) {
      cb(true);
    }
  }

  function qrBitmapDataUrl(payload, pxPerMod) {
    if (typeof LireateQR === 'undefined' || !LireateQR.create) {
      return null;
    }
    var qr;
    try {
      qr = LireateQR.create(qrPayloadSegments(payload), qrRenderOpts());
    } catch (e) {
      return null;
    }
    var n = qr.modules.size;
    var quiet = QR_MARGIN_MOD;
    var grid = n + quiet * 2;
    var px = grid * pxPerMod;
    var canvas = document.createElement('canvas');
    canvas.width = px;
    canvas.height = px;
    var ctx = canvas.getContext('2d');
    if (!ctx) {
      return null;
    }
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, px, px);
    ctx.fillStyle = '#000000';
    for (var row = 0; row < n; row++) {
      for (var col = 0; col < n; col++) {
        if (qr.modules.get(row, col)) {
          ctx.fillRect((col + quiet) * pxPerMod, (row + quiet) * pxPerMod, pxPerMod, pxPerMod);
        }
      }
    }
    return { url: canvas.toDataURL('image/png'), cssPx: px, pxPerMod: pxPerMod };
  }

  function renderQr(host, payload, cb) {
    host.innerHTML = '';
    host.setAttribute('data-lx-qr-payload', payload);
    host.removeAttribute('data-lx-qr-hi');
    var modCount = qrModuleCount(payload);

    function paint() {
      if (qrHostInnerWidth(host) < 48) {
        requestAnimationFrame(paint);
        return;
      }
      modCount = qrModuleCount(payload);
      var pxPerMod = qrPxPerMod(host, modCount);
      var pack = qrBitmapDataUrl(payload, pxPerMod);
      if (pack && pack.url) {
        var hi = qrBitmapDataUrl(payload, QR_HI_PX_PER_MOD);
        placeQrImg(host, pack.url, pack.cssPx, hi ? hi.url : null, cb);
        return;
      }
      if (typeof LireateQR !== 'undefined' && LireateQR.toDataURL) {
        var cssW = qrCssWidth(host, modCount);
        LireateQR.toDataURL(
          qrPayloadSegments(payload),
          {
            errorCorrectionLevel: 'M',
            type: 'image/png',
            margin: QR_MARGIN_MOD,
            scale: qrPxPerMod(host, modCount),
          },
          function (err, url) {
            if (!err && url) {
              placeQrImg(host, url, qrCssWidth(host, modCount), null, cb);
              return;
            }
            renderQrLegacy(host, payload, modCount, cb);
          }
        );
        return;
      }
      renderQrLegacy(host, payload, modCount, cb);
    }

    function schedulePaint() {
      if (host.getBoundingClientRect().width < 20 && !window.matchMedia('(max-width: 519px)').matches) {
        requestAnimationFrame(schedulePaint);
        return;
      }
      paint();
    }
    schedulePaint();
    var resizeTimer;
    window.addEventListener('resize', function onResize() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        if (host.isConnected && host.querySelector('img.lx-qr-img')) {
          paint();
        }
      }, 120);
    });
  }

  /** Fallback: qrcodejs (davidshimjs). */
  function renderQrLegacy(host, payload, moduleCount, cb) {
    if (typeof QRCode === 'undefined') {
      host.textContent = 'QR недоступен — обновите страницу.';
      if (cb) {
        cb(false);
      }
      return;
    }
    var cssW = qrCssWidth(host, moduleCount || qrModuleCount(payload));
    var scratch = document.createElement('div');
    scratch.style.cssText = 'position:absolute;left:-9999px;top:0;';
    document.body.appendChild(scratch);
    /* global QRCode */
    new QRCode(scratch, {
      text: payload,
      width: cssW,
      height: cssW,
      correctLevel: QRCode.CorrectLevel.M,
    });
    setTimeout(function () {
      var canvas = scratch.querySelector('canvas');
      document.body.removeChild(scratch);
      if (canvas && canvas.width > 0) {
        try {
          placeQrImg(host, canvas.toDataURL('image/png'), cssW, null, cb);
          return;
        } catch (e) {
          /* ignore */
        }
      }
      host.textContent = 'Не удалось сформировать QR.';
      if (cb) {
        cb(false);
      }
    }, 120);
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

  function copyText(value) {
    if (!value) {
      return;
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(value).catch(function () {
        /* ignore */
      });
      return;
    }
    var ta = document.createElement('textarea');
    ta.value = value;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
    } catch (e) {
      /* ignore */
    }
    document.body.removeChild(ta);
  }

  function renderReqLines(container, lines) {
    container.innerHTML = lines
      .map(function (row) {
        var label = row[0];
        var val = row[1];
        var copy = row[2];
        var copyBtn = copy
          ? '<button type="button" class="lx-btn-copy" data-lx-copy="' +
            String(copy).replace(/"/g, '&quot;') +
            '">Копировать</button>'
          : '';
        return (
          '<div class="lx-line-row">' +
          '<span class="lx-line-lbl">' +
          label +
          '</span>' +
          '<span class="lx-line-val lx-line-amt">' +
          val +
          '</span>' +
          copyBtn +
          '</div>'
        );
      })
      .join('');
    container.querySelectorAll('[data-lx-copy]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        copyText(btn.getAttribute('data-lx-copy'));
        btn.textContent = 'Скопировано';
        setTimeout(function () {
          btn.textContent = 'Копировать';
        }, 1600);
      });
    });
  }

  function profileReqLines(p, built) {
    return [
      ['Получатель', p.fields.Name, null],
      ['ИНН / КПП', p.fields.PayeeINN + ' / ' + p.fields.KPP, p.fields.PayeeINN],
      ['БИК', p.fields.BIC, p.fields.BIC],
      ['Банк', p.fields.BankName, null],
      ['Казначейский / расчётный счёт', p.fields.PersonalAcc, p.fields.PersonalAcc],
      ['ЕКС', p.fields.CorrespAcc, p.fields.CorrespAcc],
      ['КБК', p.fields.CBC, p.fields.CBC],
      ['ОКТМО', p.fields.OKTMO, p.fields.OKTMO],
      ['Сумма', Pay.formatRub(built.sumRub), null],
    ];
  }

  function initGenericPay(root, cfg) {
    var toolId = cfg.toolId;
    var profileId = cfg.profileId;
    var jsonUrl = root.getAttribute('data-lx-pay-profiles-json');
    fireMetrika(toolId, 'open', 'ok');

    var banner =
      cfg.dangerBanner
        ? '<p class="lx-alert lx-alert-danger">' + cfg.dangerBanner + '</p>'
        : '';
    var purposeBlock = cfg.showPurposeHint
      ? '<p class="lx-purpose-hint">' + PURPOSE_HINT + '</p>'
      : '';
    var purposePlaceholder = cfg.purposePlaceholder || 'Пункт таблицы, номер заявки…';

    mountTool(
      root,
      banner +
        '<div class="lx-tool-grid"><div>' +
        '<p class="lx-hint lx-pay-draft-note" data-lx-pay-draft-note hidden></p>' +
        '<div class="lx-field"><label for="lx-pay-sum-' +
        toolId +
        '">Сумма к оплате, ₽</label>' +
        '<input id="lx-pay-sum-' +
        toolId +
        '" type="number" min="1" step="1" inputmode="numeric" data-lx-pay-sum></div>' +
        '<div class="lx-field"><label for="lx-pay-purpose-' +
        toolId +
        '">Назначение платежа</label>' +
        '<textarea id="lx-pay-purpose-' +
        toolId +
        '" data-lx-pay-purpose rows="4" placeholder="' +
        purposePlaceholder +
        '"></textarea></div>' +
        purposeBlock +
        '<p class="lx-hint">Сумма и назначение остаются в браузере. Образец — <strong>черновик</strong>: сверьте с ' +
        (cfg.officialLinkHtml || '') +
        '.</p>' +
        '<div class="lx-actions">' +
        '<button type="button" class="lx-btn lx-btn-primary" data-lx-pay-build>Сформировать QR</button>' +
        '<button type="button" class="lx-btn lx-btn-ghost" data-lx-pay-print>Печать</button>' +
        '<button type="button" class="lx-btn lx-btn-ghost" data-lx-pay-clear>Очистить</button>' +
        '</div>' +
        '<p class="lx-warn" data-lx-pay-error hidden></p>' +
        '</div><aside class="lx-result-panel" data-lx-pay-result hidden>' +
        '<p class="lx-field-label">Реквизиты получателя</p>' +
        '<div data-lx-pay-req-lines></div>' +
        '<p class="lx-field-label">Универсальный платёжный код (UTF-8)</p>' +
        '<pre class="lx-pay-payload" data-lx-pay-payload></pre>' +
        '<div class="lx-qr-box" data-lx-pay-qr></div>' +
        '<button type="button" class="lx-btn lx-btn-ghost" data-lx-qr-download hidden>Скачать QR (PNG)</button>' +
        '<p class="lx-hint" data-lx-pay-meta></p>' +
        '</aside></div>'
    );

    var sumEl = qs('[data-lx-pay-sum]', root);
    var purposeEl = qs('[data-lx-pay-purpose]', root);
    var errEl = qs('[data-lx-pay-error]', root);
    var result = qs('[data-lx-pay-result]', root);
    var draftNote = qs('[data-lx-pay-draft-note]', root);

    if (cfg.toolId === 'oplata-patentnyh-poshlin') {
      var draft = readPayDraft();
      if (draft && draft.total) {
        sumEl.value = String(draft.total);
        if (Pay && draft.lines) {
          purposeEl.value = Pay.defaultPurposeFromLines(draft.lines);
        }
        draftNote.hidden = false;
        draftNote.textContent = 'Из калькулятора: ' + (draft.source || 'пошлины') + ', сумма ' + (draft.total || '') + ' ₽.';
        clearPayDraft();
      }
    }

    fetchProfiles(jsonUrl, function (data) {
      if (Pay && data) {
        Pay.setData(data);
      }
      qs('[data-lx-pay-build]', root).addEventListener('click', function () {
        if (!Pay) {
          return;
        }
        var built = Pay.buildSt00012(profileId, {
          sumRub: Number(sumEl.value),
          purpose: purposeEl.value,
        });
        if (!built.ok) {
          errEl.hidden = false;
          errEl.textContent =
            built.error === 'unconfirmed'
              ? 'Реквизиты не опубликованы в инструменте — откройте официальную страницу источника.'
              : 'Укажите сумму больше нуля и назначение платежа.';
          result.hidden = true;
          return;
        }
        errEl.hidden = true;
        result.hidden = false;
        var p = built.profile;
        renderReqLines(qs('[data-lx-pay-req-lines]', root), profileReqLines(p, built));
        qs('[data-lx-pay-payload]', root).textContent = built.payload;
        renderQr(qs('[data-lx-pay-qr]', root), built.payload, function (ok) {
          if (!ok) {
            errEl.hidden = false;
            errEl.textContent = 'Не удалось построить QR — попробуйте ещё раз или используйте текст кода выше.';
          }
        });
        var eff = p.effectiveFrom ? ' Действуют с ' + p.effectiveFrom + '.' : '';
        qs('[data-lx-pay-meta]', root).innerHTML =
          'Источник: <a href="' +
          p.sourceUrl +
          '" target="_blank" rel="noopener noreferrer">' +
          (cfg.sourceLabel || 'официальная публикация') +
          '</a>.' +
          eff +
          ' Формат ST00012 в QR — UTF-8. <a href="https://www.cbr.ru/explan/Psystem/upc/" target="_blank" rel="noopener noreferrer">Разъяснения Банка России</a>.';
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

  function initPatentPay(root) {
    initGenericPay(root, {
      toolId: 'oplata-patentnyh-poshlin',
      profileId: 'rospatent_patent_dues',
      showPurposeHint: true,
      officialLinkHtml:
        '<a href="https://rospatent.gov.ru/ru/activities/dues/req" target="_blank" rel="noopener noreferrer">rospatent.gov.ru</a>',
      sourceLabel: 'реквизиты Роспатента (прил. №1)',
    });
  }

  function initFipsPay(root) {
    initGenericPay(root, {
      toolId: 'oplata-uslug-fips',
      profileId: 'fips_paid_services',
      showPurposeHint: false,
      purposePlaceholder: 'Раздел и пункт услуги по перечню ФИПС',
      dangerBanner:
        'Государственные, патентные и иные пошлины на этот счёт платить нельзя! Текст реквизитов должен совпадать с источником — любые сокращения и замены приведут к незачислению.',
      officialLinkHtml:
        '<a href="https://fips.ru/vse-uslugi/rekvizity-fips-dlya-oplaty-uslug-fips-okazyvaemykh-na-platnoy-osnove.php" target="_blank" rel="noopener noreferrer">fips.ru</a>',
      sourceLabel: 'реквизиты ФИПС для платных услуг',
    });
  }

  function initEvmDuty(root) {
    var jsonUrl = root.getAttribute('data-lx-pay-profiles-json');
    fireMetrika('poisk-platezha-evm', 'open', 'ok');
    mountTool(
      root,
      '<div class="lx-tool-grid"><div class="lx-evm-guide">' +
        '<p class="lx-field-label">Как подтвердить оплату госпошлины за ПрЭВМ</p>' +
        '<ol class="lx-ol">' +
        '<li>Оплатите по реквизитам <a href="https://rospatent.gov.ru/content/uploadfiles/rekviziti-pril2.pdf" target="_blank" rel="noopener noreferrer">приложения №2</a> (КБК <strong>16810809000011000110</strong>) или сформируйте QR справа.</li>' +
        '<li>Сверьте платёж в <a href="https://new.fips.ru/office/" target="_blank" rel="noopener noreferrer">личном кабинете ФИПС</a> — отдельного публичного поиска по реквизитам документа на сайте нет.</li>' +
        '<li>При расхождении сверьте назначение и КБК с <a href="https://rospatent.gov.ru/ru/activities/dues/stateduty/gos_poshl" target="_blank" rel="noopener noreferrer">правилами оплаты</a>.</li>' +
        '</ol>' +
        '<p class="lx-hint">Номера платёжек и ИНН на этом сайте не собираются и никуда не отправляются.</p>' +
        '</div><aside class="lx-evm-aside">' +
        '<p class="lx-field-label">Черновик QR госпошлины ПрЭВМ</p>' +
        '<div class="lx-field"><label for="lx-evm-sum">Сумма, ₽</label><input id="lx-evm-sum" type="number" min="1" data-lx-evm-sum placeholder="По тарифу"></div>' +
        '<div class="lx-field"><label for="lx-evm-purpose">Назначение</label><textarea id="lx-evm-purpose" data-lx-evm-purpose rows="3" placeholder="Госпошлина за регистрацию ПрЭВМ, номер заявки…"></textarea></div>' +
        '<div class="lx-actions"><button type="button" class="lx-btn lx-btn-primary" data-lx-evm-build>QR для оплаты</button></div>' +
        '<div class="lx-result-panel" data-lx-evm-result hidden><div data-lx-evm-req></div><pre class="lx-pay-payload" data-lx-evm-payload></pre><div class="lx-qr-box" data-lx-evm-qr></div><button type="button" class="lx-btn lx-btn-ghost" data-lx-qr-download hidden>Скачать QR (PNG)</button></div>' +
        '</aside></div>'
    );

    fetchProfiles(jsonUrl, function (data) {
      if (Pay && data) {
        Pay.setData(data);
      }
      qs('[data-lx-evm-build]', root).addEventListener('click', function () {
        if (!Pay) {
          return;
        }
        var built = Pay.buildSt00012('rospatent_evm_state_duty', {
          sumRub: Number(qs('[data-lx-evm-sum]', root).value),
          purpose: qs('[data-lx-evm-purpose]', root).value,
        });
        var panel = qs('[data-lx-evm-result]', root);
        if (!built.ok) {
          panel.hidden = false;
          qs('[data-lx-evm-payload]', root).textContent = 'Укажите сумму и назначение.';
          return;
        }
        panel.hidden = false;
        renderReqLines(qs('[data-lx-evm-req]', root), profileReqLines(built.profile, built));
        qs('[data-lx-evm-payload]', root).textContent = built.payload;
        renderQr(qs('[data-lx-evm-qr]', root), built.payload);
        fireMetrika('poisk-platezha-evm', 'qr', 'built');
      });
    });
  }

  function initOldApps(root) {
    var toolId = 'starye-zayavki';
    fireMetrika(toolId, 'open', 'ok');
    mountTool(
      root,
      '<p class="lx-hint">Чтобы открыть свою заявку, войдите в сервисы ФИПС под своей учётной записью.</p>' +
        '<div class="lx-field"><label for="lx-old-num">Номер заявки (запишите для себя)</label>' +
        '<input id="lx-old-num" type="text" data-lx-old-num inputmode="numeric" placeholder="Например, 2025123456"></div>' +
        '<div class="lx-actions">' +
        '<a class="lx-btn lx-btn-primary" href="https://new.fips.ru/office/" target="_blank" rel="noopener noreferrer">Личный кабинет ФИПС</a>' +
        '<a class="lx-btn lx-btn-ghost" href="https://kpsrtz.fips.ru/" target="_blank" rel="noopener noreferrer">АРМ «Регистратор»</a>' +
        '</div>' +
        '<p class="lx-hint">Пароль и логин ФИПС вводятся только на сайте ФИПС. LIREATE их не видит.</p>' +
        '<p class="lx-hint"><a href="https://www.fips.ru/podacha-zayavki/podacha-zayavki-na-tovarnyy-znak/user-guide-api-arm2024.pdf" target="_blank" rel="noopener noreferrer">Инструкция по АРМ</a></p>'
    );
    qs('[data-lx-old-num]', root).addEventListener('change', function () {
      fireMetrika(toolId, 'number', 'local');
    });
  }

  function boot() {
    document.querySelectorAll('[data-lx-patent-pay="patent"]').forEach(initPatentPay);
    document.querySelectorAll('[data-lx-patent-pay="fips"]').forEach(initFipsPay);
    document.querySelectorAll('[data-lx-patent-evm-search], [data-lx-patent-evm-duty]').forEach(initEvmDuty);
    document.querySelectorAll('[data-lx-patent-old-apps]').forEach(initOldApps);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();