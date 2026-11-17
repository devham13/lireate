/* eslint-disable */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.LireatePatentPayment = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var DATA = null;

  function setData(json) {
    DATA = json;
  }

  function profile(id) {
    if (!DATA || !DATA.profiles || !DATA.profiles[id]) {
      return null;
    }
    return DATA.profiles[id];
  }

  function formatRub(n) {
    var x = Math.round(Number(n) || 0);
    return String(x).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' \u20BD';
  }

  function sanitizePurpose(s) {
    return String(s || '')
      .replace(/\|/g, ' ')
      .replace(/\s+/g, ' ')
      .trim()
      .slice(0, 180);
  }

  /** Универсальный платёжный код (ST00012) — поля по разъяснениям Банка России. */
  function buildSt00012(profileId, opts) {
    var p = profile(profileId);
    opts = opts || {};
    if (!p) {
      return { ok: false, error: 'missing_profile' };
    }
    if (!p.confirmed || !p.fields) {
      return {
        ok: false,
        error: 'unconfirmed',
        sourceUrl: p.sourceUrl || (DATA && DATA.profiles.rospatent_patent_dues && DATA.profiles.rospatent_patent_dues.reqPageUrl),
      };
    }
    var sumRub = Number(opts.sumRub);
    if (!isFinite(sumRub) || sumRub <= 0) {
      return { ok: false, error: 'sum' };
    }
    var purpose = sanitizePurpose(opts.purpose);
    if (!purpose) {
      return { ok: false, error: 'purpose' };
    }
    var sumKop = Math.round(sumRub * 100);
    var f = p.fields;
    var parts = [
      'ST00012',
      'Name=' + f.Name,
      'PersonalAcc=' + f.PersonalAcc,
      'BankName=' + f.BankName,
      'BIC=' + f.BIC,
      'CorrespAcc=' + f.CorrespAcc,
      'PayeeINN=' + f.PayeeINN,
      'KPP=' + f.KPP,
      'CBC=' + f.CBC,
      'OKTMO=' + f.OKTMO,
      'Sum=' + String(sumKop),
      'Purpose=' + purpose,
    ];
    return {
      ok: true,
      payload: parts.join('|'),
      sumRub: sumRub,
      sumKop: sumKop,
      profile: p,
    };
  }

  function defaultPurposeFromLines(lines) {
    if (!lines || !lines.length) {
      return '';
    }
    return lines
      .map(function (row) {
        return row.id ? 'п.' + row.id : row.label;
      })
      .filter(Boolean)
      .join(', ')
      .slice(0, 120);
  }

  return {
    setData: setData,
    profile: profile,
    buildSt00012: buildSt00012,
    formatRub: formatRub,
    sanitizePurpose: sanitizePurpose,
    defaultPurposeFromLines: defaultPurposeFromLines,
    meta: function () {
      return DATA;
    },
  };
});