/* eslint-disable */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.LireatePatentContacts = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var DATA = null;
  var LOADED = false;

  function normalize(s) {
    return String(s || '')
      .toLowerCase()
      .replace(/ё/g, 'е');
  }

  function setData(json) {
    DATA = json;
    LOADED = true;
  }

  function filterEntries(query) {
    if (!DATA || !DATA.entries) {
      return [];
    }
    var q = normalize(query).trim();
    if (!q) {
      return DATA.entries.slice();
    }
    return DATA.entries.filter(function (row) {
      var parts = [row.org, row.department, row.topic, row.type, row.value];
      if (row.phones && row.phones.length) {
        row.phones.forEach(function (ph) {
          parts.push(ph.value);
        });
      }
      if (row.email && row.email.value) {
        parts.push(row.email.value);
      }
      var hay = normalize(parts.join(' '));
      return hay.indexOf(q) >= 0;
    });
  }

  function meta() {
    return {
      checked: DATA && DATA.checked,
      sourceUrl: DATA && DATA.sourceUrl,
    };
  }

  return {
    setData: setData,
    filterEntries: filterEntries,
    meta: meta,
    isLoaded: function () {
      return LOADED;
    },
  };
});