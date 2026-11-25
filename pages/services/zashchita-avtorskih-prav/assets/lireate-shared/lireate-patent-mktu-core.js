/* eslint-disable */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.LireatePatentMktu = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var CLASS_LINE_RE = /^\s*(?:класс\s*)?(\d{1,2})\s*[.:\-–—]?\s*(.*)$/i;

  function normalizeClassNum(raw) {
    raw = String(raw || '').trim();
    if (!/^\d+$/.test(raw)) {
      return null;
    }
    var n = parseInt(raw, 10);
    if (n < 1 || n > 45) {
      return null;
    }
    return n;
  }

  function splitItems(chunk, lineMode) {
    chunk = (chunk || '').trim();
    if (!chunk) {
      return [];
    }
    if (lineMode) {
      return [chunk];
    }
    var parts = [];
    var buf = [];
    var paren = 0;
    for (var i = 0; i < chunk.length; i++) {
      var ch = chunk.charAt(i);
      if (ch === '(') {
        paren += 1;
      } else if (ch === ')' && paren > 0) {
        paren -= 1;
      }
      if (ch === ';' && paren === 0) {
        parts.push(buf.join('').trim());
        buf = [];
      } else {
        buf.push(ch);
      }
    }
    var tail = buf.join('').trim();
    if (tail) {
      parts.push(tail);
    }
    return parts.filter(function (p) {
      return p;
    });
  }

  function parseMktuText(text, lineMode) {
    var warnings = [];
    var unrecognized = [];
    var duplicates = [];
    var classes = {};
    var current = null;
    var lines = String(text || '')
      .replace(/\r\n/g, '\n')
      .replace(/\r/g, '\n')
      .split('\n');

    function addPosition(cls, term) {
      term = (term || '').trim();
      if (!term) {
        return;
      }
      if (!classes[cls]) {
        classes[cls] = [];
      }
      var lower = term.toLowerCase();
      for (var j = 0; j < classes[cls].length; j++) {
        if (classes[cls][j].toLowerCase() === lower) {
          duplicates.push({ class: cls, term: term });
          return;
        }
      }
      classes[cls].push(term);
    }

    lines.forEach(function (rawLine) {
      var line = rawLine.trim();
      if (!line) {
        return;
      }
      var m = CLASS_LINE_RE.exec(line);
      if (m) {
        var cls = normalizeClassNum(m[1]);
        if (cls === null) {
          unrecognized.push(line);
          return;
        }
        if (classes[cls] && current !== null && cls !== current) {
          warnings.push('Повтор класса ' + cls + ': уточните перечень.');
        }
        current = cls;
        if (!classes[cls]) {
          classes[cls] = [];
        }
        var rest = (m[2] || '').trim();
        if (rest) {
          splitItems(rest, lineMode).forEach(function (item) {
            addPosition(cls, item);
          });
        }
        return;
      }
      if (current === null) {
        unrecognized.push(line);
        return;
      }
      if (lineMode) {
        addPosition(current, line);
      } else {
        splitItems(line, lineMode).forEach(function (item) {
          addPosition(current, item);
        });
      }
    });

    var counts = {};
    Object.keys(classes).forEach(function (k) {
      counts[k] = classes[k].length;
    });

    return {
      ok: true,
      classes: classes,
      counts: counts,
      warnings: warnings,
      unrecognized: unrecognized,
      duplicates: duplicates,
    };
  }

  function formatExport(classes) {
    var keys = Object.keys(classes)
      .map(function (k) {
        return parseInt(k, 10);
      })
      .sort(function (a, b) {
        return a - b;
      });
    var out = [];
    keys.forEach(function (cls) {
      out.push((cls < 10 ? '0' : '') + cls);
      classes[cls].forEach(function (term) {
        out.push('  ' + term);
      });
      out.push('');
    });
    return out.join('\n').trim() + '\n';
  }

  return {
    parseMktuText: parseMktuText,
    formatExport: formatExport,
    normalizeClassNum: normalizeClassNum,
  };
});