/**
 * Скриншоты этапа 3 — платежи и АРМ.
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PATENT_PREVIEW_BASE || 'http://127.0.0.1:8891';
const OUT = '/opt/cursor/artifacts/screenshots';

const PAGES = [
  { path: '/patentnye-zadachi/oplata-patentnyh-poshlin/', key: 'patent-pay-dues' },
  { path: '/patentnye-zadachi/oplata-uslug-fips/', key: 'patent-pay-fips' },
  { path: '/patentnye-zadachi/poisk-platezha-evm/', key: 'patent-evm-search' },
  { path: '/patentnye-zadachi/starye-zayavki/', key: 'patent-old-apps' },
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 8000) throw new Error(`${path} too small (${buf.length} bytes)`);
}

async function scrollTop(page) {
  await page.evaluate(() => {
    window.scrollTo(0, 0);
  });
}

async function shot(page, url, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await scrollTop(page);
  await new Promise((r) => setTimeout(r, 900));
  await page.screenshot({ path: file, fullPage: true });
  assertPng(file);
  console.log('OK', file);
}

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

for (const p of PAGES) {
  const page = await browser.newPage();
  await shot(page, BASE + p.path, `${OUT}/${p.key}-desktop-1440-full.png`, 1440, 900);
  await shot(page, BASE + p.path, `${OUT}/${p.key}-mobile-390-full.png`, 390, 844);
  await page.close();
}

const pay = await browser.newPage();
await pay.setViewport({ width: 1440, height: 900 });
await pay.goto(BASE + '/patentnye-zadachi/oplata-patentnyh-poshlin/', { waitUntil: 'networkidle2' });
await pay.waitForSelector('[data-lx-pay-sum]', { timeout: 15000 });
await pay.$eval('[data-lx-pay-sum]', (el) => {
  el.value = '12000';
});
await pay.$eval('[data-lx-pay-purpose]', (el) => {
  el.value = 'п.2.16 изменения по заявке (черновик)';
});
await pay.click('[data-lx-pay-build]');
await pay.waitForSelector('[data-lx-pay-qr] img', { timeout: 10000 });
await scrollTop(pay);
await pay.screenshot({ path: `${OUT}/patent-pay-dues-result-desktop-1440-full.png`, fullPage: true });
await pay.setViewport({ width: 390, height: 844 });
await scrollTop(pay);
await pay.screenshot({ path: `${OUT}/patent-pay-dues-result-mobile-390-full.png`, fullPage: true });
assertPng(`${OUT}/patent-pay-dues-result-desktop-1440-full.png`);
console.log('OK result desktop/mobile patent pay');

const evm = await browser.newPage();
await evm.setViewport({ width: 1440, height: 900 });
await evm.goto(BASE + '/patentnye-zadachi/poisk-platezha-evm/', { waitUntil: 'networkidle2' });
await evm.click('[data-lx-evm-check]');
await evm.waitForSelector('[data-lx-evm-result]:not([hidden])', { timeout: 10000 });
await scrollTop(evm);
await evm.screenshot({ path: `${OUT}/patent-evm-search-result-desktop-1440-full.png`, fullPage: true });
await evm.setViewport({ width: 390, height: 844 });
await scrollTop(evm);
await evm.screenshot({ path: `${OUT}/patent-evm-search-result-mobile-390-full.png`, fullPage: true });
console.log('OK result evm search');

const fips = await browser.newPage();
await fips.setViewport({ width: 1440, height: 900 });
await fips.goto(BASE + '/patentnye-zadachi/oplata-uslug-fips/', { waitUntil: 'networkidle2' });
await fips.$eval('[data-lx-fips-sum]', (el) => {
  el.value = '5000';
});
await scrollTop(fips);
await fips.screenshot({ path: `${OUT}/patent-pay-fips-result-desktop-1440-full.png`, fullPage: true });
await fips.setViewport({ width: 390, height: 844 });
await scrollTop(fips);
await fips.screenshot({ path: `${OUT}/patent-pay-fips-result-mobile-390-full.png`, fullPage: true });
console.log('OK result fips');

const old = await browser.newPage();
await old.setViewport({ width: 1440, height: 900 });
await old.goto(BASE + '/patentnye-zadachi/starye-zayavki/', { waitUntil: 'networkidle2' });
await old.$eval('[data-lx-old-num]', (el) => {
  el.value = '2025712345';
});
await scrollTop(old);
await old.screenshot({ path: `${OUT}/patent-old-apps-result-desktop-1440-full.png`, fullPage: true });
await old.setViewport({ width: 390, height: 844 });
await scrollTop(old);
await old.screenshot({ path: `${OUT}/patent-old-apps-result-mobile-390-full.png`, fullPage: true });
console.log('OK result old apps');

await browser.close();
console.log('Stage 3 screenshots done.');