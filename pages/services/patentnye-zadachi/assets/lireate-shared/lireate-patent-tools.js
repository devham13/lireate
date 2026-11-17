(function () {
  'use strict';

  var Fees = typeof LireatePatentFees !== 'undefined' ? LireatePatentFees : null;
  var Mktu = typeof LireatePatentMktu !== 'undefined' ? LireatePatentMktu : null;
  var STORAGE_KEY = 'lireate-patent-class-counts-v1';

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

  qsa('[data-lx-patent-reg]').forEach(initRegistration);
  qsa('[data-lx-patent-renew]').forEach(initRenewal);
  qsa('[data-lx-patent-mktu]').forEach(initMktuEditor);
})();