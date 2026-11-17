/* eslint-disable */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.LireatePatentWordmark = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var SIZE = 944;
  var DPI = 300;
  var REQ_URL =
    'https://rospatent.gov.ru/index.php/ru/faq/27-kakie-trebovaniya-predyavlyayutsya-k-fajlu-izobrazheniya-zayavlyaemogo-oboznacheniya';

  function wrapLines(ctx, text, maxWidth) {
    var words = String(text || '').split(/\s+/).filter(Boolean);
    var lines = [];
    var line = '';
    words.forEach(function (w) {
      var test = line ? line + ' ' + w : w;
      if (ctx.measureText(test).width > maxWidth && line) {
        lines.push(line);
        line = w;
      } else {
        line = test;
      }
    });
    if (line) {
      lines.push(line);
    }
    if (!lines.length && text) {
      lines.push(String(text));
    }
    return lines;
  }

  function drawToCanvas(text, fontFamily, fontSize) {
    var canvas = document.createElement('canvas');
    canvas.width = SIZE;
    canvas.height = SIZE;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, SIZE, SIZE);
    ctx.fillStyle = '#000000';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    var size = fontSize || 72;
    var family = fontFamily || 'Arial, sans-serif';
    ctx.font = 'bold ' + size + 'px ' + family;
    var pad = 48;
    var maxW = SIZE - pad * 2;
    var lines = wrapLines(ctx, text, maxW);
    while (lines.length > 8 && size > 24) {
      size -= 4;
      ctx.font = 'bold ' + size + 'px ' + family;
      lines = wrapLines(ctx, text, maxW);
    }
    var lineHeight = size * 1.15;
    var blockH = lines.length * lineHeight;
    var startY = (SIZE - blockH) / 2 + lineHeight / 2;
    lines.forEach(function (ln, i) {
      ctx.fillText(ln, SIZE / 2, startY + i * lineHeight);
    });
    return canvas;
  }

  function canvasToRgbBytes(canvas) {
    var ctx = canvas.getContext('2d');
    var img = ctx.getImageData(0, 0, SIZE, SIZE);
    var out = new Uint8Array(SIZE * SIZE * 3);
    var j = 0;
    for (var i = 0; i < img.data.length; i += 4) {
      out[j++] = img.data[i];
      out[j++] = img.data[i + 1];
      out[j++] = img.data[i + 2];
    }
    return out;
  }

  function encodeTiffRgb(rgb, width, height, dpi) {
    var numPixels = width * height;
    var imageBytes = numPixels * 3;
    var headerSize = 8;
    var ifdCount = 12;
    var ifdBytes = 2 + ifdCount * 12 + 4;
    var bitsOffset = headerSize + ifdBytes;
    var bitsLen = 6;
    var resOffset = bitsOffset + bitsLen;
    var resLen = 16;
    var imageOffset = resOffset + resLen;
    var total = imageOffset + imageBytes;
    var buf = new ArrayBuffer(total);
    var view = new DataView(buf);
    var u8 = new Uint8Array(buf);
    var o = 0;
    u8[o++] = 0x49;
    u8[o++] = 0x49;
    view.setUint16(o, 42, true);
    o += 2;
    view.setUint32(o, headerSize, true);
    o = headerSize;
    view.setUint16(o, ifdCount, true);
    o += 2;

    function writeEntry(tag, type, count, valueOrOffset) {
      view.setUint16(o, tag, true);
      o += 2;
      view.setUint16(o, type, true);
      o += 2;
      view.setUint32(o, count, true);
      o += 4;
      view.setUint32(o, valueOrOffset, true);
      o += 4;
    }

    writeEntry(256, 4, 1, width);
    writeEntry(257, 4, 1, height);
    writeEntry(258, 3, 3, bitsOffset);
    writeEntry(259, 3, 1, 1);
    writeEntry(262, 3, 1, 2);
    writeEntry(273, 4, 1, imageOffset);
    writeEntry(277, 3, 1, 3);
    writeEntry(278, 4, 1, height);
    writeEntry(279, 4, 1, imageBytes);
    writeEntry(282, 5, 1, resOffset);
    writeEntry(283, 5, 1, resOffset + 8);
    writeEntry(296, 3, 1, 2);
    view.setUint32(o, 0, true);
    o += 4;

    u8[bitsOffset] = 8;
    u8[bitsOffset + 1] = 8;
    u8[bitsOffset + 2] = 8;

    view.setUint32(resOffset, dpi, true);
    view.setUint32(resOffset + 4, 1, true);
    view.setUint32(resOffset + 8, dpi, true);
    view.setUint32(resOffset + 12, 1, true);

    u8.set(rgb, imageOffset);
    return buf;
  }

  function render(text, options) {
    options = options || {};
    if (!String(text || '').trim()) {
      return { ok: false, error: 'empty' };
    }
    var canvas = drawToCanvas(text, options.fontFamily, options.fontSize);
    return {
      ok: true,
      canvas: canvas,
      width: SIZE,
      height: SIZE,
      dpi: DPI,
      requirementsUrl: REQ_URL,
    };
  }

  function downloadTiff(text, filename, options) {
    var r = render(text, options);
    if (!r.ok) {
      return r;
    }
    var rgb = canvasToRgbBytes(r.canvas);
    var tiff = encodeTiffRgb(rgb, SIZE, SIZE, DPI);
    var blob = new Blob([tiff], { type: 'image/tiff' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename || 'slovesnyy-znak.tif';
    document.body.appendChild(a);
    a.click();
    setTimeout(function () {
      URL.revokeObjectURL(a.href);
      document.body.removeChild(a);
    }, 100);
    return { ok: true, bytes: tiff.byteLength, uncompressed: true };
  }

  return {
    SIZE: SIZE,
    DPI: DPI,
    REQ_URL: REQ_URL,
    render: render,
    downloadTiff: downloadTiff,
    encodeTiffRgb: encodeTiffRgb,
  };
});