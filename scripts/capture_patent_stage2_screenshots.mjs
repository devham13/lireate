/**
 * Скриншоты этапа 2 «Патентные задачи» — baseline + результат инструментов.
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PATENT_PREVIEW_BASE || 'http://127.0.0.1:8891';
const OUT = '/opt/cursor/artifacts/screenshots';

const PAGES = [
  { path: '/patentnye-zadachi/adres-zayavitelya/', key: 'patent-adres' },
  { path: '/patentnye-zadachi/fail-slovesnogo-znaka/', key: 'patent-wordmark' },
  { path: '/patentnye-zadachi/poshliny-izmeneniya/', key: 'patent-changes' },
  { path: '/patentnye-zadachi/sokrashchenie-mktu/', key: 'patent-shrink' },
  { path: '/patentnye-zadachi/kontakty-fips/', key: 'patent-contacts' },
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 8000) throw new Error(`${path} too small (${buf.length} bytes)`);
}

async function revealAll(page) {
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => {
      el.classList.add('lx-reveal--visible', 'is-visible', 'lx-reveal-visible');
      el.style.opacity = '1';
      el.style.transform = 'none';
    });
  });
}

async function scrollTop(page) {
  await page.evaluate(() => {
    window.scrollTo(0, 0);
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
  });
}

async function shotFull(page, url, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await revealAll(page);
  await scrollTop(page);
  await new Promise((r) => setTimeout(r, 800));
  await page.screenshot({ path: file, fullPage: true });
  assertPng(file);
  console.log('OK', file);
}

async function shotResult(page, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await revealAll(page);
  await scrollTop(page);
  await new Promise((r) => setTimeout(r, 400));
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
  const url = BASE + p.path;
  await shotFull(page, url, `${OUT}/${p.key}-desktop-1440-full.png`, 1440, 900);
  await shotFull(page, url, `${OUT}/${p.key}-mobile-390-full.png`, 390, 844);
  await page.close();
}

const addr = await browser.newPage();
await addr.setViewport({ width: 1440, height: 900 });
await addr.goto(BASE + '/patentnye-zadachi/adres-zayavitelya/', { waitUntil: 'networkidle2' });
await addr.$eval('[data-lx-adres-applicant-in]', (el) => {
  el.value = '125993, г. Москва, Примерная улица, дом 1, офис 2';
});
await addr.click('[data-lx-adres-run]');
await new Promise((r) => setTimeout(r, 500));
await shotResult(addr, `${OUT}/patent-adres-result-desktop-1440-full.png`, 1440, 900);
await shotResult(addr, `${OUT}/patent-adres-result-mobile-390-full.png`, 390, 844);
await addr.close();

const wm = await browser.newPage();
await wm.setViewport({ width: 1440, height: 900 });
await wm.goto(BASE + '/patentnye-zadachi/fail-slovesnogo-znaka/', { waitUntil: 'networkidle2' });
await wm.$eval('[data-lx-wordmark-in]', (el) => {
  el.value = 'ЛИРЕЙТ';
});
await wm.click('[data-lx-wordmark-preview]');
await new Promise((r) => setTimeout(r, 600));
await shotResult(wm, `${OUT}/patent-wordmark-result-desktop-1440-full.png`, 1440, 900);
await shotResult(wm, `${OUT}/patent-wordmark-result-mobile-390-full.png`, 390, 844);
await wm.close();

const ch = await browser.newPage();
await ch.setViewport({ width: 1440, height: 900 });
await ch.goto(BASE + '/patentnye-zadachi/poshliny-izmeneniya/', { waitUntil: 'networkidle2' });
await ch.$eval('[data-lx-changes-216]', (el) => {
  el.value = '2';
});
await ch.$eval('[data-lx-changes-marks]', (el) => {
  el.value = '2';
});
await ch.click('[data-lx-patent-calc]');
await ch.waitForFunction(
  () => {
    const t = document.querySelector('[data-lx-patent-total]');
    return t && t.textContent && t.textContent.indexOf('12 000') >= 0;
  },
  { timeout: 10000 }
);
await shotResult(ch, `${OUT}/patent-changes-result-desktop-1440-full.png`, 1440, 900);
await shotResult(ch, `${OUT}/patent-changes-result-mobile-390-full.png`, 390, 844);
await ch.close();

const shrink = await browser.newPage();
await shrink.setViewport({ width: 1440, height: 900 });
await shrink.goto(BASE + '/patentnye-zadachi/sokrashchenie-mktu/', { waitUntil: 'networkidle2' });
const mktuSample =
  '25\n' +
  'одежда; обувь; головные уборы\n' +
  '35\n' +
  'реклама; управление бизнесом';
await shrink.$eval('[data-lx-shrink-source]', (el, val) => {
  el.value = val;
}, mktuSample);
await shrink.click('[data-lx-shrink-parse]');
await new Promise((r) => setTimeout(r, 400));
await shrink.evaluate(() => {
  const boxes = document.querySelectorAll('[data-lx-shrink-pos]');
  if (boxes.length > 2) {
    boxes[boxes.length - 1].checked = false;
    boxes[boxes.length - 2].checked = false;
  }
});
await shrink.click('[data-lx-shrink-calc]');
await shrink.waitForSelector('[data-lx-patent-result]:not([hidden])', { timeout: 10000 });
await shotResult(shrink, `${OUT}/patent-shrink-result-desktop-1440-full.png`, 1440, 900);
await shotResult(shrink, `${OUT}/patent-shrink-result-mobile-390-full.png`, 390, 844);
await shrink.close();

const contacts = await browser.newPage();
await contacts.setViewport({ width: 1440, height: 900 });
await contacts.goto(BASE + '/patentnye-zadachi/kontakty-fips/', { waitUntil: 'networkidle2' });
await contacts.waitForSelector('[data-lx-contacts-list] .lx-contact-card', { timeout: 10000 });
await contacts.$eval('[data-lx-contacts-search]', (el) => {
  el.value = 'фипс';
});
await contacts.evaluate(() => {
  const ev = new Event('input', { bubbles: true });
  document.querySelector('[data-lx-contacts-search]').dispatchEvent(ev);
});
await new Promise((r) => setTimeout(r, 400));
await shotResult(contacts, `${OUT}/patent-contacts-filter-desktop-1440-full.png`, 1440, 900);
await shotResult(contacts, `${OUT}/patent-contacts-filter-mobile-390-full.png`, 390, 844);
await contacts.close();

await browser.close();
console.log('Stage 2 patent screenshots done.');