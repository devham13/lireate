/**
 * PR A (отзывы + футер + tz_form_error) — скрины и overflow.
 * node scripts/reviews_map_pr_a_screenshots.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/reviews-map';

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage']
});

const url = `${BASE}/index.php`;

async function prepPage(page) {
  await page.setRequestInterception(true);
  page.on('request', (req) => {
    const u = req.url();
    if (u.includes('/mail.php') && req.method() === 'POST') {
      req.respond({ status: 200, contentType: 'application/json', body: '["success"]' });
      return;
    }
    req.continue();
  });
}

async function activateReveals(page) {
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
  });
}

async function checkOverflow(page, label) {
  const r = await page.evaluate(() => {
    const de = document.documentElement;
    return {
      clientWidth: de.clientWidth,
      scrollWidth: de.scrollWidth,
      bodyScroll: document.body ? document.body.scrollWidth : 0
    };
  });
  const ok = r.scrollWidth <= r.clientWidth && r.bodyScroll <= r.clientWidth;
  console.log(
    `overflow ${label}: client=${r.clientWidth} scroll=${r.scrollWidth} body=${r.bodyScroll} => ${ok ? 'OK' : 'FAIL'}`
  );
  if (!ok) process.exitCode = 1;
}

const widths = [
  { w: 1440, h: 900, tag: '1440' },
  { w: 390, h: 844, tag: '390' },
  { w: 375, h: 812, tag: '375' },
  { w: 320, h: 700, tag: '320' },
  { w: 768, h: 1024, tag: '768' }
];

for (const vp of widths) {
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: vp.w, height: vp.h });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await activateReveals(page);
  await checkOverflow(page, vp.tag);
  if (vp.w === 1440) {
    await page.evaluate(() => {
      document.getElementById('reviews')?.scrollIntoView({ block: 'start' });
    });
    await new Promise((r) => setTimeout(r, 500));
    await page.screenshot({ path: path.join(OUT, `reviews-desktop-${vp.tag}.png`), fullPage: false });
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await new Promise((r) => setTimeout(r, 400));
    await page.screenshot({ path: path.join(OUT, `footer-desktop-${vp.tag}.png`), fullPage: false });
  }
  if (vp.w === 390) {
    await page.evaluate(() => {
      document.getElementById('reviews')?.scrollIntoView({ block: 'start' });
    });
    await new Promise((r) => setTimeout(r, 500));
    await page.screenshot({ path: path.join(OUT, `reviews-mobile-${vp.tag}.png`), fullPage: false });
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await new Promise((r) => setTimeout(r, 400));
    await page.screenshot({ path: path.join(OUT, `footer-mobile-${vp.tag}.png`), fullPage: false });
  }
  await page.close();
}

const page = await browser.newPage();
await prepPage(page);
await page.setViewport({ width: 1440, height: 900 });
await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
await activateReveals(page);
const counts = await page.evaluate(() => ({
  reviewsSections: document.querySelectorAll('#reviews').length,
  reviewCards: document.querySelectorAll('#reviews .lx-review-frame').length
}));
console.log('reviews sections:', counts.reviewsSections, 'cards:', counts.reviewCards);
if (counts.reviewsSections !== 1 || counts.reviewCards !== 25) process.exitCode = 1;

await browser.close();
console.log('artifacts:', OUT);
