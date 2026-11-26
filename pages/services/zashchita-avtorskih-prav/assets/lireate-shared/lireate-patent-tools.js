(function () {
  'use strict';

  var Fees = typeof LireatePatentFees !== 'undefined' ? LireatePatentFees : null;
  var Mktu = typeof LireatePatentMktu !== 'undefined' ? LireatePatentMktu : null;
  var STORAGE_KEY = 'lireate-patent-class-counts-v1';
  var PAY_STORAGE_KEY = 'lireate-patent-pay-v1';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }
  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
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

  function readStoredCounts() {
    try {
      var raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) {
        return null;
      }
      var data = JSON.parse(raw);
      sessionStorage.removeItem(STORAGE_KEY);
      return data;
    } catch (err) {
      return null;
    }
  }

  function storeCounts(counts) {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(counts));
    } catch (err) {
      /* ignore */
    }
  }

  function storePayDraft(draft) {
    try {
      sessionStorage.setItem(PAY_STORAGE_KEY, JSON.stringify(draft));
    } catch (err) {
      /* ignore */
    }
  }


  function buildPrintText(title, lines, total, meta) {
    var out = [title, ''];
    lines.forEach(function (row) {
      out.push(row.id + ' — ' + row.label + ': ' + Fees.formatRub(row.amount));
    });
    out.push('');
    out.push('Итого пошлины Роспатента: ' + Fees.formatRub(total));
    out.push('');
    out.push('Редакция: ' + meta.rulesetId + ', сверка ' + meta.rulesetChecked);
    out.push(meta.sourceUrl);
    return out.join('\n');
  }

  function wireCopyPrint(root, getText) {
    var copyBtn = qs('[data-lx-patent-copy]', root);
    var printBtn = qs('[data-lx-patent-print]', root);
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        var text = getText();
        if (!text) {
          return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text);
        }
      });
    }
    if (printBtn) {
      printBtn.addEventListener('click', function () {
        window.print();
      });
    }
  }

  function countsFromManualTable(root) {
    var counts = {};
    qsa('[data-lx-class-count]', root).forEach(function (row) {
      var cls = row.getAttribute('data-lx-class');
      var input = qs('input', row);
      if (!cls || !input) {
        return;
      }
      var v = parseInt(input.value, 10);
      if (!isNaN(v) && v > 0) {
        counts[cls] = v;
      }
    });
    return counts;
  }

  function initRegistration(root) {
    if (!Fees || !Mktu) {
      return;
    }
    var toolId = 'poshliny-registraciya';
    var mode = 'classes';
    var modeBtns = qsa('[data-lx-patent-mode]', root);
    var panelClasses = qs('[data-lx-panel="classes"]', root);
    var panelList = qs('[data-lx-panel="list"]', root);
    var classInput = qs('[data-lx-patent-class-num]', root);
    var listArea = qs('[data-lx-patent-mktu-in]', root);
    var lineMode = qs('[data-lx-patent-line-mode]', root);
    var paper = qs('[data-lx-patent-paper]', root);
    var resultPanel = qs('[data-lx-patent-result]', root);
    var resultBody = qs('[data-lx-patent-result-body]', root);
    var totalEl = qs('[data-lx-patent-total]', root);
    var errEl = qs('[data-lx-patent-error]', root);
    var classTable = qs('[data-lx-patent-class-table]', root);
    var calcBtn = qs('[data-lx-patent-calc]', root);
    var clearBtn = qs('[data-lx-patent-clear]', root);
    var stored = readStoredCounts();
    var lastMeta = null;
    var lastPayload = null;

    function setMode(next) {
      mode = next;
      modeBtns.forEach(function (btn) {
        btn.setAttribute('aria-pressed', btn.getAttribute('data-lx-patent-mode') === mode ? 'true' : 'false');
      });
      if (panelClasses) {
        panelClasses.hidden = mode !== 'classes';
      }
      if (panelList) {
        panelList.hidden = mode !== 'list';
      }
    }
    modeBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setMode(btn.getAttribute('data-lx-patent-mode'));
      });
    });
    setMode('classes');
    fireMetrika(toolId, 'open', 'ok');

    function renderClassTable(counts) {
      if (!classTable) {
        return;
      }
      classTable.innerHTML = '';
      Object.keys(counts)
        .sort(function (a, b) {
          return parseInt(a, 10) - parseInt(b, 10);
        })
        .forEach(function (cls) {
          var row = document.createElement('div');
          row.className = 'lx-class-row';
          row.setAttribute('data-lx-class-count', '1');
          row.setAttribute('data-lx-class', cls);
          row.innerHTML =
            '<span>' +
            cls +
            '</span><span class="lx-hint">Позиций</span>' +
            '<input type="number" min="1" step="1" value="' +
            counts[cls] +
            '" aria-label="Число позиций в классе ' +
            cls +
            '">';
          classTable.appendChild(row);
        });
    }

    if (stored && classTable) {
      setMode('list');
      renderClassTable(stored);
    }

    function getCounts() {
      if (mode === 'classes') {
        var c = classInput ? parseInt(classInput.value, 10) : NaN;
        if (isNaN(c) || c < 1 || c > 45) {
          return { error: 'Введите число классов от 1 до 45.' };
        }
        if (Math.floor(c) !== c) {
          return { error: 'Число классов должно быть целым.' };
        }
        var counts = {};
        for (var i = 1; i <= c; i++) {
          counts[String(i)] = 10;
        }
        return { counts: counts };
      }
      var manualFirst = countsFromManualTable(root);
      if (Object.keys(manualFirst).length) {
        return { counts: manualFirst };
      }
      if (!listArea || !listArea.value.trim()) {
        return { error: 'Вставьте перечень МКТУ или укажите классы.' };
      }
      var parsed = Mktu.parseMktuText(listArea.value, lineMode && lineMode.checked);
      if (!Object.keys(parsed.counts).length) {
        return { error: 'Не удалось разобрать классы. Проверьте формат.' };
      }
      var manual = countsFromManualTable(root);
      if (Object.keys(manual).length) {
        parsed.counts = manual;
      }
      return { counts: parsed.counts, parsed: parsed };
    }

    function showResult(payload) {
      lastPayload = payload;
      lastMeta = {
        rulesetId: payload.rulesetId,
        rulesetChecked: payload.rulesetChecked,
        sourceUrl: payload.sourceUrl,
      };
      if (errEl) {
        errEl.textContent = '';
        errEl.hidden = true;
      }
      if (!payload.ok) {
        if (resultPanel) {
          resultPanel.hidden = true;
        }
        if (errEl) {
          errEl.textContent = 'Нет данных для расчёта.';
          errEl.hidden = false;
        }
        return;
      }
      var html = '';
      payload.lines.forEach(function (row) {
        html +=
          '<div class="lx-line-row"><span>' +
          row.id +
          ' ' +
          row.label +
          '</span><span>' +
          Fees.formatRub(row.amount) +
          '</span></div>';
      });
      html +=
        '<div class="lx-line-row"><strong>Подача</strong><strong>' +
        Fees.formatRub(payload.filingSubtotal) +
        '</strong></div>';
      payload.afterLines.forEach(function (row) {
        html +=
          '<div class="lx-line-row"><span>' +
          row.id +
          ' ' +
          row.label +
          '</span><span>' +
          Fees.formatRub(row.amount) +
          '</span></div>';
      });
      payload.optionalLines.forEach(function (row) {
        html +=
          '<div class="lx-line-row"><span>' +
          row.id +
          ' ' +
          row.label +
          '</span><span>' +
          Fees.formatRub(row.amount) +
          '</span></div>';
      });
      if (resultBody) {
        resultBody.innerHTML = html;
        var allLines = (payload.lines || [])
          .concat(payload.afterLines || [])
          .concat(payload.optionalLines || []);
        var hand = document.createElement('p');
        hand.className = 'lx-hint';
        hand.innerHTML =
          '<a href="/patentnye-zadachi/oplata-patentnyh-poshlin/">Подготовить оплату →</a>';
        var link = hand.querySelector('a');
        if (link) {
          link.addEventListener('click', function (ev) {
            ev.preventDefault();
            storePayDraft({ source: toolId, total: payload.total, lines: allLines });
            window.location.href = '/patentnye-zadachi/oplata-patentnyh-poshlin/';
          });
        }
        resultBody.appendChild(hand);
      }
      if (totalEl) {
        totalEl.textContent = Fees.formatRub(payload.total);
      }
      if (resultPanel) {
        resultPanel.hidden = false;
      }
      fireMetrika(toolId, mode, 'calculated');
    }

    function calculate() {
      var data = getCounts();
      if (data.error) {
        if (errEl) {
          errEl.textContent = data.error;
          errEl.hidden = false;
        }
        if (resultPanel) {
          resultPanel.hidden = true;
        }
        return;
      }
      if (data.parsed && classTable) {
        renderClassTable(data.parsed.counts);
      }
      var usePaper = paper && paper.checked;
      var res = Fees.registrationLines(data.counts, usePaper);
      showResult(res);
    }

    if (calcBtn) {
      calcBtn.addEventListener('click', calculate);
    }
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (classInput) {
          classInput.value = '';
        }
        if (listArea) {
          listArea.value = '';
        }
        if (classTable) {
          classTable.innerHTML = '';
        }
        if (resultPanel) {
          resultPanel.hidden = true;
        }
        if (errEl) {
          errEl.hidden = true;
        }
      });
    }

    wireCopyPrint(root, function () {
      if (!lastPayload || !lastPayload.ok) {
        return '';
      }
      var lines = lastPayload.lines.concat(lastPayload.afterLines).concat(lastPayload.optionalLines);
      return buildPrintText('Пошлины регистрации ТЗ', lines, lastPayload.total, lastMeta);
    });
  }

  function initRenewal(root) {
    if (!Fees || !Mktu) {
      return;
    }
    var toolId = 'poshliny-prodlenie';
    var listArea = qs('[data-lx-patent-mktu-in]', root);
    var lineMode = qs('[data-lx-patent-line-mode]', root);
    var extra = qs('[data-lx-patent-extra-term]', root);
    var classTable = qs('[data-lx-patent-class-table]', root);
    var calcBtn = qs('[data-lx-patent-calc]', root);
    var resultPanel = qs('[data-lx-patent-result]', root);
    var resultBody = qs('[data-lx-patent-result-body]', root);
    var totalEl = qs('[data-lx-patent-total]', root);
    var errEl = qs('[data-lx-patent-error]', root);
    var lastPayload = null;
    var lastMeta = null;
    fireMetrika(toolId, 'open', 'ok');

    function calculate() {
      var counts = {};
      var manual = countsFromManualTable(root);
      if (Object.keys(manual).length) {
        counts = manual;
      } else if (listArea && listArea.value.trim()) {
        var parsed = Mktu.parseMktuText(listArea.value, lineMode && lineMode.checked);
        counts = parsed.counts;
        if (!Object.keys(counts).length) {
          if (errEl) {
            errEl.textContent = 'Не удалось разобрать перечень.';
            errEl.hidden = false;
          }
          if (resultPanel) {
            resultPanel.hidden = true;
          }
          return;
        }
        if (classTable) {
          classTable.innerHTML = '';
          Object.keys(counts)
            .sort(function (a, b) {
              return parseInt(a, 10) - parseInt(b, 10);
            })
            .forEach(function (cls) {
              var row = document.createElement('div');
              row.className = 'lx-class-row';
              row.setAttribute('data-lx-class-count', '1');
              row.setAttribute('data-lx-class', cls);
              row.innerHTML =
                '<span>' +
                cls +
                '</span><span class="lx-hint">Позиций</span>' +
                '<input type="number" min="1" step="1" value="' +
                counts[cls] +
                '" aria-label="Класс ' +
                cls +
                '">';
              classTable.appendChild(row);
            });
        }
      }
      if (!Object.keys(counts).length) {
        if (errEl) {
          errEl.textContent = 'Вставьте перечень или укажите классы и позиции.';
          errEl.hidden = false;
        }
        if (resultPanel) {
          resultPanel.hidden = true;
        }
        return;
      }
      var res = Fees.renewalLines(counts, extra && extra.checked);
      lastPayload = res;
      lastMeta = {
        rulesetId: res.rulesetId,
        rulesetChecked: res.rulesetChecked,
        sourceUrl: res.sourceUrl,
      };
      if (errEl) {
        errEl.hidden = true;
      }
      var html = '';
      res.lines.forEach(function (row) {
        html +=
          '<div class="lx-line-row"><span>' +
          row.id +
          ' ' +
          row.label +
          '</span><span>' +
          Fees.formatRub(row.amount) +
          '</span></div>';
      });
      html +=
        '<p class="lx-hint">Бумажная часть п. 2.22 не включена в сумму по умолчанию — сверьте процедуру на rospatent.gov.ru.</p>';
      if (resultBody) {
        resultBody.innerHTML = html;
      }
      if (totalEl) {
        totalEl.textContent = Fees.formatRub(res.total);
      }
      if (resultPanel) {
        resultPanel.hidden = false;
      }
      fireMetrika(toolId, 'list', 'calculated');
    }

    if (calcBtn) {
      calcBtn.addEventListener('click', calculate);
    }

    wireCopyPrint(root, function () {
      if (!lastPayload || !lastPayload.ok) {
        return '';
      }
      return buildPrintText('Пошлины продления ТЗ', lastPayload.lines, lastPayload.total, lastMeta);
    });
  }

  function initMktuEditor(root) {
    if (!Mktu) {
      return;
    }
    var toolId = 'redaktor-mktu';
    var source = qs('[data-lx-mktu-source]', root);
    var result = qs('[data-lx-mktu-result]', root);
    var warn = qs('[data-lx-mktu-warn]', root);
    var lineMode = qs('[data-lx-patent-line-mode]', root);
    var runBtn = qs('[data-lx-mktu-run]', root);
    var clearBtn = qs('[data-lx-mktu-clear]', root);
    var toCalc = qs('[data-lx-mktu-to-calc]', root);
    fireMetrika(toolId, 'open', 'ok');

    function run() {
      if (!source) {
        return;
      }
      var parsed = Mktu.parseMktuText(source.value, lineMode && lineMode.checked);
      if (result) {
        result.value = Mktu.formatExport(parsed.classes);
      }
      if (warn) {
        var msgs = parsed.warnings.concat(
          parsed.unrecognized.map(function (l) {
            return 'Не распознано: ' + l;
          }),
          parsed.duplicates.map(function (d) {
            return 'Дубликат в классе ' + d.class + ': ' + d.term;
          })
        );
        warn.innerHTML = msgs.map(function (m) {
          return '<p class="lx-warn">' + m + '</p>';
        }).join('');
      }
      fireMetrika(toolId, 'normalize', 'ok');
    }

    if (runBtn) {
      runBtn.addEventListener('click', run);
    }
    if (clearBtn && source && result) {
      clearBtn.addEventListener('click', function () {
        source.value = '';
        result.value = '';
        if (warn) {
          warn.innerHTML = '';
        }
      });
    }
    if (toCalc) {
      toCalc.addEventListener('click', function (ev) {
        if (ev && ev.preventDefault) {
          ev.preventDefault();
        }
        if (!source) {
          return;
        }
        var parsed = Mktu.parseMktuText(source.value, lineMode && lineMode.checked);
        if (!Object.keys(parsed.counts).length) {
          return;
        }
        storeCounts(parsed.counts);
        var href = toCalc.getAttribute('href') || '/patentnye-zadachi/poshliny-registraciya/';
        window.location.href = href;
      });
    }
  }

  function initAddress(root) {
    var Addr = typeof LireatePatentAddress !== 'undefined' ? LireatePatentAddress : null;
    if (!Addr) {
      return;
    }
    var toolId = 'adres-zayavitelya';
    var srcApplicant = qs('[data-lx-adres-applicant-in]', root);
    var srcCorr = qs('[data-lx-adres-corr-in]', root);
    var outApplicant = qs('[data-lx-adres-applicant-out]', root);
    var outCorr = qs('[data-lx-adres-corr-out]', root);
    var note = qs('[data-lx-adres-note]', root);
    var runBtn = qs('[data-lx-adres-run]', root);
    var clearBtn = qs('[data-lx-adres-clear]', root);
  fireMetrika(toolId, 'open', 'ok');

    function run() {
      if (srcApplicant) {
        var r = Addr.formatAddress(srcApplicant.value);
        if (outApplicant) {
          outApplicant.value = r.ok ? r.formatted : '';
        }
      }
      if (srcCorr) {
        var c = Addr.formatAddress(srcCorr.value);
        if (outCorr) {
          outCorr.value = c.ok ? c.formatted : '';
        }
      }
      if (note) {
        note.textContent = Addr.GAR_DISCLAIMER;
      }
      fireMetrika(toolId, 'format', 'ok');
    }
    if (runBtn) {
      runBtn.addEventListener('click', run);
    }
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (srcApplicant) {
          srcApplicant.value = '';
        }
        if (srcCorr) {
          srcCorr.value = '';
        }
        if (outApplicant) {
          outApplicant.value = '';
        }
        if (outCorr) {
          outCorr.value = '';
        }
        if (note) {
          note.textContent = '';
        }
      });
    }
    qsa('[data-lx-adres-copy]', root).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.getAttribute('data-lx-adres-copy');
        var el = target === 'corr' ? outCorr : outApplicant;
        if (el && el.value && navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(el.value);
        }
      });
    });
  }

  function initWordmark(root) {
    var Wm = typeof LireatePatentWordmark !== 'undefined' ? LireatePatentWordmark : null;
    if (!Wm) {
      return;
    }
    var toolId = 'fail-slovesnogo-znaka';
    var input = qs('[data-lx-wordmark-in]', root);
    var canvasHost = qs('[data-lx-wordmark-canvas]', root);
    var previewBtn = qs('[data-lx-wordmark-preview]', root);
    var dlBtn = qs('[data-lx-wordmark-download]', root);
    var clearBtn = qs('[data-lx-wordmark-clear]', root);
    var err = qs('[data-lx-wordmark-error]', root);
    var currentCanvas = null;
    fireMetrika(toolId, 'open', 'ok');

    function showPreview() {
      if (!input || !canvasHost) {
        return;
      }
      var r = Wm.render(input.value, { fontFamily: 'Inter, Arial, sans-serif' });
      if (!r.ok) {
        if (err) {
          err.textContent = 'Введите текст обозначения.';
          err.hidden = false;
        }
        canvasHost.innerHTML = '';
        return;
      }
      if (err) {
        err.hidden = true;
      }
      canvasHost.innerHTML = '';
      currentCanvas = r.canvas;
      currentCanvas.style.maxWidth = '100%';
      currentCanvas.style.height = 'auto';
      currentCanvas.style.border = '1px solid rgba(255,255,255,0.15)';
      canvasHost.appendChild(currentCanvas);
      fireMetrika(toolId, 'preview', 'ok');
    }

    if (previewBtn) {
      previewBtn.addEventListener('click', showPreview);
    }
    if (dlBtn) {
      dlBtn.addEventListener('click', function () {
        if (!input || !input.value.trim()) {
          if (err) {
            err.textContent = 'Введите текст перед скачиванием.';
            err.hidden = false;
          }
          return;
        }
        showPreview();
        var d = Wm.downloadTiff(input.value, 'oboznachenie-944x944.tif', {
          fontFamily: 'Inter, Arial, sans-serif',
        });
        if (d.ok) {
          fireMetrika(toolId, 'download', 'ok');
        }
      });
    }
    if (clearBtn && input) {
      clearBtn.addEventListener('click', function () {
        input.value = '';
        if (canvasHost) {
          canvasHost.innerHTML = '';
        }
        if (err) {
          err.hidden = true;
        }
      });
    }
  }

  function initChanges(root) {
    if (!Fees) {
      return;
    }
    var toolId = 'poshliny-izmeneniya';
    var marksEl = qs('[data-lx-changes-marks]', root);
    var n216 = qs('[data-lx-changes-216]', root);
    var n217 = qs('[data-lx-changes-217]', root);
    var paper217 = qs('[data-lx-changes-paper]', root);
    var certYes = qs('[data-lx-changes-cert-yes]', root);
    var certNo = qs('[data-lx-changes-cert-no]', root);
    var calcBtn = qs('[data-lx-patent-calc]', root);
    var resultPanel = qs('[data-lx-patent-result]', root);
    var resultBody = qs('[data-lx-patent-result-body]', root);
    var totalEl = qs('[data-lx-patent-total]', root);
    var errEl = qs('[data-lx-patent-error]', root);
    var lastPayload = null;
    fireMetrika(toolId, 'open', 'ok');

    function certInfo() {
      if (certNo && certNo.checked) {
        return false;
      }
      if (certYes && certYes.checked) {
        return true;
      }
      return null;
    }

    function calculate() {
      var marks = marksEl ? parseInt(marksEl.value, 10) : NaN;
      var c216 = n216 ? parseInt(n216.value, 10) || 0 : 0;
      var c217 = n217 ? parseInt(n217.value, 10) || 0 : 0;
      if (isNaN(marks) || marks < 1) {
        if (errEl) {
          errEl.textContent = 'Укажите число регистраций (знаков) — целое от 1.';
          errEl.hidden = false;
        }
        if (resultPanel) {
          resultPanel.hidden = true;
        }
        return;
      }
      if (c216 < 1 && c217 < 1) {
        if (errEl) {
          errEl.textContent = 'Укажите число изменений по п. 2.16 и/или 2.17.';
          errEl.hidden = false;
        }
        if (resultPanel) {
          resultPanel.hidden = true;
        }
        return;
      }
      var res = Fees.changesLines(
        marks,
        c216,
        c217,
        paper217 && paper217.checked,
        certInfo()
      );
      lastPayload = res;
      if (!res.ok) {
        if (errEl) {
          errEl.textContent = 'Нет данных для расчёта.';
          errEl.hidden = false;
        }
        return;
      }
      if (errEl) {
        errEl.hidden = true;
      }
      var html = '';
      res.lines.forEach(function (row) {
        if (row.needsClarification) {
          html +=
            '<div class="lx-line-row"><span>' +
            row.id +
            ' ' +
            row.label +
            '</span><span><a href="' +
            row.formsUrl +
            '" target="_blank" rel="noopener noreferrer">бланки</a></span></div>';
          return;
        }
        html +=
          '<div class="lx-line-row"><span>' +
          row.id +
          ' ' +
          row.label +
          '</span><span>' +
          Fees.formatRub(row.amount) +
          '</span></div>';
      });
      html +=
        '<p class="lx-hint">Источник: <a href="' +
        res.sourceUrl +
        '" target="_blank" rel="noopener noreferrer">таблица пошлин</a>, сверка ' +
        res.rulesetChecked +
        '</p>';
      if (resultBody) {
        resultBody.innerHTML = html;
      }
      if (totalEl) {
        totalEl.textContent = Fees.formatRub(res.total);
      }
      if (resultPanel) {
        resultPanel.hidden = false;
      }
      fireMetrika(toolId, 'calc', 'ok');
    }

    if (calcBtn) {
      calcBtn.addEventListener('click', calculate);
    }
    wireCopyPrint(root, function () {
      if (!lastPayload || !lastPayload.ok) {
        return '';
      }
      var lines = lastPayload.lines.filter(function (l) {
        return l.amount != null;
      });
      return buildPrintText('Пошлины за изменения', lines, lastPayload.total, {
        rulesetId: lastPayload.rulesetId,
        rulesetChecked: lastPayload.rulesetChecked,
        sourceUrl: lastPayload.sourceUrl,
      });
    });
  }

  function initShrink(root) {
    if (!Fees || !Mktu) {
      return;
    }
    var toolId = 'sokrashchenie-mktu';
    var source = qs('[data-lx-shrink-source]', root);
    var lineMode = qs('[data-lx-patent-line-mode]', root);
    var parseBtn = qs('[data-lx-shrink-parse]', root);
    var listHost = qs('[data-lx-shrink-list]', root);
    var warnEl = qs('[data-lx-shrink-warn]', root);
    var calcBtn = qs('[data-lx-shrink-calc]', root);
    var resultPanel = qs('[data-lx-patent-result]', root);
    var resultBody = qs('[data-lx-patent-result-body]', root);
    var totalEl = qs('[data-lx-patent-total]', root);
    var errEl = qs('[data-lx-patent-error]', root);
    var marksEl = qs('[data-lx-shrink-marks]', root);
    var parsedState = null;
    fireMetrika(toolId, 'open', 'ok');

    function positionKey(cls, idx) {
      return String(cls) + ':' + String(idx);
    }

    function renderList(parsed) {
      if (!listHost) {
        return;
      }
      listHost.innerHTML = '';
      var keys = Object.keys(parsed.classes)
        .map(function (k) {
          return parseInt(k, 10);
        })
        .sort(function (a, b) {
          return a - b;
        });
      keys.forEach(function (cls) {
        var block = document.createElement('div');
        block.className = 'lx-shrink-class';
        var title = document.createElement('p');
        title.className = 'lx-field-label';
        title.textContent = 'Класс ' + (cls < 10 ? '0' : '') + cls;
        block.appendChild(title);
        var toggleAll = document.createElement('button');
        toggleAll.type = 'button';
        toggleAll.className = 'lx-btn lx-btn-ghost';
        toggleAll.setAttribute('data-lx-shrink-class', String(cls));
        toggleAll.textContent = 'Снять весь класс';
        toggleAll.addEventListener('click', function () {
          if (!window.confirm('Исключить все позиции класса ' + cls + '?')) {
            return;
          }
          qsa('[data-lx-shrink-pos]', block).forEach(function (cb) {
            cb.checked = false;
          });
        });
        block.appendChild(toggleAll);
        parsed.classes[cls].forEach(function (term, idx) {
          var lbl = document.createElement('label');
          lbl.className = 'lx-shrink-pos';
          var cb = document.createElement('input');
          cb.type = 'checkbox';
          cb.checked = true;
          cb.setAttribute('data-lx-shrink-pos', positionKey(cls, idx));
          cb.setAttribute('data-lx-class', String(cls));
          lbl.appendChild(cb);
          lbl.appendChild(document.createTextNode(' ' + term));
          block.appendChild(lbl);
        });
        listHost.appendChild(block);
      });
    }

    if (parseBtn) {
      parseBtn.addEventListener('click', function () {
        if (!source) {
          return;
        }
        var parsed = Mktu.parseMktuText(source.value, lineMode && lineMode.checked);
        parsedState = parsed;
        if (!Object.keys(parsed.classes).length) {
          if (errEl) {
            errEl.textContent = 'Не удалось разобрать перечень.';
            errEl.hidden = false;
          }
          return;
        }
        if (errEl) {
          errEl.hidden = true;
        }
        renderList(parsed);
        if (warnEl) {
          var msgs = parsed.warnings.concat(
            parsed.unrecognized.map(function (l) {
              return 'Не распознано: ' + l;
            })
          );
          warnEl.innerHTML = msgs
            .map(function (m) {
              return '<p class="lx-warn">' + m + '</p>';
            })
            .join('');
        }
      });
    }

    function selectedCounts() {
      var counts = {};
      if (!parsedState) {
        return counts;
      }
      Object.keys(parsedState.classes).forEach(function (cls) {
        var kept = 0;
        parsedState.classes[cls].forEach(function (_t, idx) {
          var sel = qs('[data-lx-shrink-pos="' + positionKey(cls, idx) + '"]', root);
          if (sel && sel.checked) {
            kept += 1;
          }
        });
        if (kept > 0) {
          counts[cls] = kept;
        }
      });
      return counts;
    }

    function originalCounts() {
      return parsedState ? parsedState.counts : {};
    }

    if (calcBtn) {
      calcBtn.addEventListener('click', function () {
        if (!parsedState) {
          if (errEl) {
            errEl.textContent = 'Сначала разберите перечень.';
            errEl.hidden = false;
          }
          return;
        }
        var before = originalCounts();
        var after = selectedCounts();
        if (!Object.keys(after).length) {
          if (errEl) {
            errEl.textContent =
              'Все классы исключены — расчёт продления недоступен. Оставьте хотя бы одну позицию.';
            errEl.hidden = false;
          }
          if (resultPanel) {
            resultPanel.hidden = true;
          }
          return;
        }
        var marks = marksEl ? parseInt(marksEl.value, 10) : 1;
        if (isNaN(marks) || marks < 1) {
          marks = 1;
        }
        var renewBefore = Fees.renewalLines(before, false);
        var renewAfter = Fees.renewalLines(after, false);
        var changeFee = Fees.changesLines(marks, 1, 0, false, true);
        if (errEl) {
          errEl.hidden = true;
        }
        var html = '';
        html += '<p class="lx-field-label">Продление до сокращения</p>';
        renewBefore.lines.forEach(function (row) {
          html +=
            '<div class="lx-line-row"><span>' +
            row.id +
            ' ' +
            row.label +
            '</span><span>' +
            Fees.formatRub(row.amount) +
            '</span></div>';
        });
        html += '<p class="lx-field-label">Продление после</p>';
        renewAfter.lines.forEach(function (row) {
          html +=
            '<div class="lx-line-row"><span>' +
            row.id +
            ' ' +
            row.label +
            '</span><span>' +
            Fees.formatRub(row.amount) +
            '</span></div>';
        });
        html += '<p class="lx-field-label">Изменение перечня (п. 2.16)</p>';
        changeFee.lines.forEach(function (row) {
          html +=
            '<div class="lx-line-row"><span>' +
            row.id +
            ' ' +
            row.label +
            '</span><span>' +
            Fees.formatRub(row.amount) +
            '</span></div>';
        });
        var delta = renewAfter.total - renewBefore.total;
        var net = delta + (changeFee.ok ? changeFee.total : 0);
        html +=
          '<div class="lx-line-row"><strong>Разница продления</strong><strong>' +
          Fees.formatRub(delta) +
          '</strong></div>';
        html +=
          '<div class="lx-line-row"><strong>С учётом п. 2.16</strong><strong>' +
          Fees.formatRub(net) +
          '</strong></div>';
        if (resultBody) {
          resultBody.innerHTML = html;
        }
        if (totalEl) {
          totalEl.textContent = Fees.formatRub(net);
        }
        if (resultPanel) {
          resultPanel.hidden = false;
        }
        fireMetrika(toolId, 'calc', 'ok');
      });
    }
  }

  function initContacts(root) {
    var Contacts = typeof LireatePatentContacts !== 'undefined' ? LireatePatentContacts : null;
    if (!Contacts) {
      return;
    }
    var toolId = 'kontakty-fips';
    var search = qs('[data-lx-contacts-search]', root);
    var list = qs('[data-lx-contacts-list]', root);
    var metaEl = qs('[data-lx-contacts-meta]', root);
    var jsonUrl = root.getAttribute('data-lx-contacts-json');
    fireMetrika(toolId, 'open', 'ok');

    function renderRows(rows) {
      if (!list) {
        return;
      }
      if (!rows.length) {
        list.innerHTML =
          '<p class="lx-warn">Ничего не найдено. <a href="https://rospatent.gov.ru/ru/contacts" target="_blank" rel="noopener noreferrer">Официальный справочник</a></p>';
        return;
      }
      list.innerHTML = rows
        .map(function (row) {
          var val;
          if (row.type === 'fips_consult') {
            var phones = (row.phones || [])
              .map(function (ph) {
                return '<a href="' + ph.href + '">' + ph.value + '</a>';
              })
              .join('<span class="lx-contact-sep"> · </span>');
            var mail =
              row.email && row.email.href
                ? '<a href="' + row.email.href + '">' + row.email.value + '</a>'
                : '';
            val =
              '<p class="lx-contact-phones"><span class="lx-field-label">Телефон</span> ' +
              phones +
              '</p><p class="lx-contact-mail"><span class="lx-field-label">E-mail</span> ' +
              mail +
              '</p>';
          } else {
            val =
              row.type === 'email' || row.type === 'phone'
                ? '<a href="' + row.href + '">' + row.value + '</a>'
                : row.value;
            val = '<p>' + val + '</p>';
          }
          return (
            '<article class="lx-contact-card"><p class="lx-field-label">' +
            row.org +
            ' · ' +
            row.department +
            '</p><p>' +
            row.topic +
            '</p>' +
            val +
            '</article>'
          );
        })
        .join('');
    }

    function refresh() {
      var q = search ? search.value : '';
      renderRows(Contacts.filterEntries(q));
    }

    function boot(data) {
      Contacts.setData(data);
      var meta = Contacts.meta();
      if (metaEl && meta.sourceUrl) {
        metaEl.innerHTML =
          'Сверка: ' +
          meta.checked +
          '. Источник: <a href="' +
          meta.sourceUrl +
          '" target="_blank" rel="noopener noreferrer">rospatent.gov.ru</a>';
      }
      refresh();
    }

    if (jsonUrl) {
      fetch(jsonUrl, { credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(boot)
        .catch(function () {
          if (list) {
            list.innerHTML =
              '<p class="lx-warn">Не удалось загрузить справочник. <a href="https://rospatent.gov.ru/ru/contacts" target="_blank" rel="noopener noreferrer">Официальные контакты</a></p>';
          }
        });
    }
    if (search) {
      search.addEventListener('input', refresh);
    }
  }

  qsa('[data-lx-patent-reg]').forEach(initRegistration);
  qsa('[data-lx-patent-renew]').forEach(initRenewal);
  qsa('[data-lx-patent-mktu]').forEach(initMktuEditor);
  qsa('[data-lx-patent-adres]').forEach(initAddress);
  qsa('[data-lx-patent-wordmark]').forEach(initWordmark);
  qsa('[data-lx-patent-changes]').forEach(initChanges);
  qsa('[data-lx-patent-shrink]').forEach(initShrink);
  qsa('[data-lx-patent-contacts]').forEach(initContacts);
})();