import puppeteer from 'puppeteer-core';
import { writeFileSync } from 'fs';

const CHROME = '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/patent-wordmark-sample.tiff';

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});
const page = await browser.newPage();
await page.goto('http://127.0.0.1:8891/patentnye-zadachi/fail-slovesnogo-znaka/', {
  waitUntil: 'networkidle2',
});
const b64 = await page.evaluate(async () => {
  await new Promise((r) => setTimeout(r, 300));
  var input = document.querySelector('[data-lx-wordmark-in]');
  input.value = 'ЛИРЕЙТ';
  document.querySelector('[data-lx-wordmark-preview]').click();
  await new Promise((r) => setTimeout(r, 400));
  var W = window.LireatePatentWordmark;
  var r = W.render('ЛИРЕЙТ');
  var rgb = new Uint8Array(944 * 944 * 3);
  var ctx = r.canvas.getContext('2d');
  var img = ctx.getImageData(0, 0, 944, 944);
  var j = 0;
  for (var i = 0; i < img.data.length; i += 4) {
    rgb[j++] = img.data[i];
    rgb[j++] = img.data[i + 1];
    rgb[j++] = img.data[i + 2];
  }
  var buf = W.encodeTiffRgb(rgb, 944, 944, 300);
  var u8 = new Uint8Array(buf);
  var bin = '';
  for (var k = 0; k < u8.length; k++) {
    bin += String.fromCharCode(u8[k]);
  }
  return btoa(bin);
});
writeFileSync(OUT, Buffer.from(b64, 'base64'));
await browser.close();
console.log('Wrote', OUT);