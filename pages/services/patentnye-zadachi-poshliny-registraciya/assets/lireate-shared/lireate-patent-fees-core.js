/* eslint-disable */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.LireatePatentFees = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var RULESET_ID = 'rospatent-pp941-table-2025-10-03';
  var RULESET_CHECKED = '2026-11-17';
  var SOURCE_URL = 'https://rospatent.gov.ru/ru/activities/dues/table';

  function eTotal(classCounts) {
    var total = 0;
    Object.keys(classCounts).forEach(function (k) {
      var n = classCounts[k];
      if (n != null && n > 10) {
        total += n - 10;
      }
    });
    return total;
  }

  function registrationLines(classCounts, paper) {
    var keys = Object.keys(classCounts);
    if (!keys.length) {
      return { ok: false, error: 'empty' };
    }
    var c = keys.length;
    var e = eTotal(classCounts);
    var p21 = 4000 + 1000 * Math.max(0, c - 1);
    var p24 = 13000 + 2500 * Math.max(0, c - 1) + 500 * e;
    var p211 = 18000 + 2000 * Math.max(0, c - 5);
    var p214 = paper ? 3000 : 0;
    var filing = p21 + p24;
    var after = p211 + p214;
    return {
      ok: true,
      c: c,
      e: e,
      lines: [
        { id: '2.1', label: 'Регистрация заявки и формальная экспертиза', amount: p21 },
        { id: '2.4', label: 'Экспертиза обозначения', amount: p24 },
      ],
      afterLines: [
        { id: '2.11', label: 'Регистрация и электронное свидетельство', amount: p211 },
      ],
      optionalLines: paper
        ? [{ id: '2.14', label: 'Бумажное свидетельство (по ходатайству)', amount: p214 }]
        : [],
      filingSubtotal: filing,
      afterSubtotal: after,
      total: filing + after,
      rulesetId: RULESET_ID,
      rulesetChecked: RULESET_CHECKED,
      sourceUrl: SOURCE_URL,
    };
  }

  function renewalLines(classCounts, extraTerm) {
    var keys = Object.keys(classCounts);
    if (!keys.length) {
      return { ok: false, error: 'empty' };
    }
    var c = keys.length;
    var e = eTotal(classCounts);
    var p222 = 22000 + 2000 * Math.max(0, c - 5) + 500 * e;
    var p225 = extraTerm ? 3000 : 0;
    var lines = [{ id: '2.22', label: 'Продление', amount: p222 }];
    if (extraTerm) {
      lines.push({
        id: '2.25',
        label: 'Ходатайство о дополнительном шестимесячном сроке',
        amount: p225,
      });
    }
    return {
      ok: true,
      c: c,
      e: e,
      lines: lines,
      total: p222 + p225,
      rulesetId: RULESET_ID,
      rulesetChecked: RULESET_CHECKED,
      sourceUrl: SOURCE_URL,
    };
  }

  function formatRub(n) {
    if (n == null || isNaN(n)) {
      return '—';
    }
    return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
  }

  return {
    registrationLines: registrationLines,
    renewalLines: renewalLines,
    formatRub: formatRub,
    RULESET_ID: RULESET_ID,
    RULESET_CHECKED: RULESET_CHECKED,
    SOURCE_URL: SOURCE_URL,
  };
});