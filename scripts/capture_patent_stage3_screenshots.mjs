/**
 * Скриншоты этапа 3 + mobile overflow + QR result.
 */
import puppeteer from 'puppeteer-core';
import { readFileSync, writeFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PATENT_PREVIEW_BASE || 'http://127.0.0.1:8891';
const OUT = '/opt/cursor/artifacts/screenshots';

const STAGE3 = [
  { path: '/patentnye-zadachi/oplata-patentnyh-poshlin/', key: 'patent-pay-dues' },
  { path: '/patentnye-zadachi/oplata-uslug-fips/', key: 'patent-pay-fips' },
  { path: '/patentnye-zadachi/poisk-platezha-evm/', key: 'patent-evm-search' },
  { path: '/patentnye-zadachi/starye-zayavki/', key: 'patent-old-apps' },
];

const ALL_PATENT = [
  { path: '/patentnye-zadachi/', key: 'patent-catalog' },
  { path: '/patentnye-zadachi/poshliny-registraciya/', key: 'patent-registraciya' },
  { path: '/patentnye-zadachi/poshliny-prodlenie/', key: 'patent-prodlenie' },
  { path: '/patentnye-zadachi/redaktor-mktu/', key: 'patent-mktu' },
  { path: '/patentnye-zadachi/adres-zayavitelya/', key: 'patent-adres' },
  { path: '/patentnye-zadachi/fail-slovesnogo-znaka/', key: 'patent-wordmark' },
  { path: '/patentnye-zadachi/poshliny-izmeneniya/', key: 'patent-changes' },
  { path: '/patentnye-zadachi/sokrashchenie-mktu/', key: 'patent-shrink' },
  { path: '/patentnye-zadachi/kontakty-fips/', key: 'patent-contacts' },
  ...STAGE3,
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 8000) throw new Error(`${path} too small (${buf.length} bytes)`);
}

async function scrollTop(page) {
  await page.evaluate(() => window.scrollTo(0, 0));
}

async function assertNoOverflow(page, label) {
  const w = await page.evaluate(() => {
    return Math.max(document.documentElement.scrollWidth, document.body.scrollWidth);
  });
  const vw = await page.evaluate(() => window.innerWidth);
  if (w > vw + 2) {
    throw new Error(`${label}: scrollWidth ${w} > viewport ${vw}`);
  }
  console.log('scrollWidth OK', label, w);
}

async function shot(page, url, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await scrollTop(page);
  await new Promise((r) => setTimeout(r, 700));
  if (w <= 420) await assertNoOverflow(page, url);
  await page.screenshot({ path: file, fullPage: true });
  assertPng(file);
  console.log('OK', file);
}

async function waitQr(page) {
  await page.waitForFunction(
    () => {
      const box = document.querySelector('[data-lx-pay-qr], [data-lx-evm-qr]');
      if (!box) return false;
      const img = box.querySelector('img.lx-qr-img, img');
      if (img && img.naturalWidth > 0) return true;
      const c = box.querySelector('canvas');
      if (c && c.width > 0) {
        const ctx = c.getContext('2d');
        const d = ctx.getImageData(0, 0, c.width, c.height).data;
        let dark = 0;
        for (let i = 0; i < d.length; i += 16) {
          if (d[i] < 200) dark++;
        }
        return dark > 50;
      }
      const t = box.querySelector('table');
      return !!t;
    },
    { timeout: 15000 }
  );
}

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const overflowLog = [];

for (const p of STAGE3) {
  const page = await browser.newPage();
  await shot(page, BASE + p.path, `${OUT}/${p.key}-desktop-1440-full.png`, 1440, 900);
  await shot(page, BASE + p.path, `${OUT}/${p.key}-mobile-390-full.png`, 390, 844);
  await page.close();
}

for (const p of ALL_PATENT) {
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844 });
  await page.goto(BASE + p.path, { waitUntil: 'networkidle2', timeout: 60000 });
  try {
    await assertNoOverflow(page, p.path);
    overflowLog.push({ path: p.path, ok: true });
  } catch (e) {
    overflowLog.push({ path: p.path, ok: false, error: String(e) });
    console.error(e.message);
  }
  await page.close();
}

writeFileSync('/opt/cursor/artifacts/patent-mobile-overflow.json', JSON.stringify(overflowLog, null, 2));

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
await waitQr(pay);
const payPayload = await pay.$eval('[data-lx-pay-payload]', (el) => el.textContent);
writeFileSync('/opt/cursor/artifacts/patent-pay-dues-payload.txt', payPayload);
await scrollTop(pay);
await pay.screenshot({ path: `${OUT}/patent-pay-dues-result-desktop-1440-full.png`, fullPage: true });
await pay.setViewport({ width: 390, height: 844 });
await assertNoOverflow(pay, 'patent-pay-dues-result-mobile');
await scrollTop(pay);
await pay.screenshot({ path: `${OUT}/patent-pay-dues-result-mobile-390-full.png`, fullPage: true });

const fips = await browser.newPage();
await fips.setViewport({ width: 1440, height: 900 });
await fips.goto(BASE + '/patentnye-zadachi/oplata-uslug-fips/', { waitUntil: 'networkidle2' });
await fips.waitForSelector('[data-lx-pay-sum]', { timeout: 15000 });
await fips.$eval('[data-lx-pay-sum]', (el) => {
  el.value = '5000';
});
await fips.$eval('[data-lx-pay-purpose]', (el) => {
  el.value = 'Раздел 1 пункт 2 перечня платных услуг';
});
await fips.click('[data-lx-pay-build]');
await waitQr(fips);
const fipsPayload = await fips.$eval('[data-lx-pay-payload]', (el) => el.textContent);
writeFileSync('/opt/cursor/artifacts/patent-pay-fips-payload.txt', fipsPayload);
await scrollTop(fips);
await fips.screenshot({ path: `${OUT}/patent-pay-fips-result-desktop-1440-full.png`, fullPage: true });
await fips.setViewport({ width: 390, height: 844 });
await assertNoOverflow(fips, 'patent-pay-fips-result-mobile');
await fips.screenshot({ path: `${OUT}/patent-pay-fips-result-mobile-390-full.png`, fullPage: true });

const evm = await browser.newPage();
await evm.setViewport({ width: 1440, height: 900 });
await evm.goto(BASE + '/patentnye-zadachi/poisk-platezha-evm/', { waitUntil: 'networkidle2' });
await evm.$eval('[data-lx-evm-sum]', (el) => {
  el.value = '4500';
});
await evm.$eval('[data-lx-evm-purpose]', (el) => {
  el.value = 'Госпошлина за регистрацию ПрЭВМ';
});
await evm.click('[data-lx-evm-build]');
await waitQr(evm);
await scrollTop(evm);
await evm.screenshot({ path: `${OUT}/patent-evm-search-result-desktop-1440-full.png`, fullPage: true });
await evm.setViewport({ width: 390, height: 844 });
await assertNoOverflow(evm, 'patent-evm-result-mobile');
await evm.screenshot({ path: `${OUT}/patent-evm-search-result-mobile-390-full.png`, fullPage: true });

const old = await browser.newPage();
await old.setViewport({ width: 1440, height: 900 });
await old.goto(BASE + '/patentnye-zadachi/starye-zayavki/', { waitUntil: 'networkidle2' });
await old.$eval('[data-lx-old-num]', (el) => {
  el.value = '2025712345';
});
await scrollTop(old);
await old.screenshot({ path: `${OUT}/patent-old-apps-result-desktop-1440-full.png`, fullPage: true });
await old.setViewport({ width: 390, height: 844 });
await old.screenshot({ path: `${OUT}/patent-old-apps-result-mobile-390-full.png`, fullPage: true });

await browser.close();
console.log('Stage 3 screenshots done.');