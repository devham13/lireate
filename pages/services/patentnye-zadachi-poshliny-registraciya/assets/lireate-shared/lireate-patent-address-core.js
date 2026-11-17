/* eslint-disable */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.LireatePatentAddress = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var GAR_DISCLAIMER = 'Адрес не проверен по ГАР. Сверьте формулировку вручную перед подачей.';

  var REPLACEMENTS = [
    [/\bгород\b/gi, 'г.'],
    [/\bг\.\s*город\b/gi, 'г.'],
    [/\bобласть\b/gi, 'обл.'],
    [/\bкрай\b/gi, 'край'],
    [/\bреспублика\b/gi, 'Респ.'],
    [/\bулица\b/gi, 'ул.'],
    [/\bул\s+/gi, 'ул. '],
    [/\bпроспект\b/gi, 'пр.'],
    [/\bпр-т\b/gi, 'пр.'],
    [/\bпр\.\s*т\b/gi, 'пр.'],
    [/\bпереулок\b/gi, 'пер.'],
    [/\bбульвар\b/gi, 'б-р'],
    [/\bшоссе\b/gi, 'ш.'],
    [/\bнабережная\b/gi, 'наб.'],
    [/\bпроезд\b/gi, 'пр-д'],
    [/\bплощадь\b/gi, 'пл.'],
    [/\bдом\b/gi, 'д.'],
    [/\bд\s+(\d)/gi, 'д. $1'],
    [/\bкорпус\b/gi, 'корп.'],
    [/\bкорп\.\s*/gi, 'корп. '],
    [/\bк\b\.?\s*(\d)/gi, 'к. $1'],
    [/\bстроение\b/gi, 'стр.'],
    [/\bлитера\b/gi, 'лит.'],
    [/\bпомещение\b/gi, 'пом.'],
    [/\bофис\b/gi, 'оф.'],
    [/\bквартира\b/gi, 'кв.'],
    [/\bкв\s+(\d)/gi, 'кв. $1'],
    [/\bроссийская федерация\b/gi, 'Российская Федерация'],
    [/\bрф\b/gi, 'РФ'],
  ];

  function collapseSpaces(s) {
    return String(s || '')
      .replace(/\s+/g, ' ')
      .replace(/\s*,\s*/g, ', ')
      .replace(/\s*;\s*/g, '; ')
      .trim();
  }

  function normalizeCase(s) {
    return s.replace(/(^|[,;]\s*)([а-яё])/gi, function (_m, p1, ch) {
      return p1 + ch.toUpperCase();
    });
  }

  function formatAddress(raw) {
    var src = String(raw || '').trim();
    if (!src) {
      return { ok: false, error: 'empty', source: src };
    }
    var work = collapseSpaces(src);
    REPLACEMENTS.forEach(function (pair) {
      work = work.replace(pair[0], pair[1]);
    });
    work = collapseSpaces(work);
    work = normalizeCase(work);
    return {
      ok: true,
      source: src,
      formatted: work,
      garVerified: false,
      disclaimer: GAR_DISCLAIMER,
    };
  }

  return {
    formatAddress: formatAddress,
    GAR_DISCLAIMER: GAR_DISCLAIMER,
  };
});