/**
 * Скрины scrollbar ленты отзывов v2 — headed + классические scrollbars.
 * xvfb-run -a node scripts/reviews_rail_scrollbar_screenshots_v2.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/scrollbar/v2';
const PREVIEW = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888/index.php';
const siteBase = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
const PROD = siteBase ? `${siteBase}/registraciya_tovarnogo_znaka/` : '';

fs.mkdirSync(OUT, { recursive: true });

const launchArgs = [
  '--no-sandbox',
  '--disable-dev-shm-usage',
  '--disable-blink-features=OverlayScrollbars',
  '--disable-features=OverlayScrollbar',
  '--force-device-scale-factor=1'
];

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: false,
  defaultViewport: null,
  args: launchArgs
});

async function prepareReviews(page) {
  const ok = await page.evaluate(() => Boolean(document.querySelector('#reviews .lx-reviews-rail-viewport')));
  if (!ok) throw new Error('reviews rail not found');
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
  });
  await page.evaluate(() => {
    const vp = document.querySelector('#reviews .lx-reviews-rail-viewport');
    vp.scrollIntoView({ block: 'center', inline: 'nearest' });
    vp.scrollLeft = Math.min(200, Math.max(0, vp.scrollWidth - vp.clientWidth));
  });
  await new Promise((r) => setTimeout(r, 600));
}

async function captureRail(page, outName, bottomOnly) {
  const vp = await page.$('#reviews .lx-reviews-rail-viewport');
  if (!vp) {
    console.warn('no viewport handle', outName);
    return;
  }
  const box = await vp.boundingBox();
  if (!box) {
    console.warn('no bounding box', outName);
    await vp.dispose();
    return;
  }
  const meta = await page.evaluate(() => {
    const el = document.querySelector('#reviews .lx-reviews-rail-viewport');
    return { scrollW: el.scrollWidth, clientW: el.clientWidth };
  });
  const extra = 20;
  const clip = bottomOnly
    ? {
        x: box.x,
        y: Math.max(0, box.y + box.height - 8),
        width: box.width,
        height: 36
      }
    : {
        x: box.x,
        y: box.y,
        width: box.width,
        height: box.height + extra
      };
  await page.screenshot({ path: path.join(OUT, outName), clip });
  const stats = fs.statSync(path.join(OUT, outName));
  console.log('saved', outName, JSON.stringify({ ...meta, clip }), 'bytes', stats.size);
  await vp.dispose();
}

async function runSet(url, prefix, widths) {
  for (const vp of widths) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: vp.h, deviceScaleFactor: 1 });
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
    await prepareReviews(page);
    if (vp.w === 1440) {
      await page.screenshot({
        path: path.join(OUT, `${prefix}-page-${vp.tag}.png`),
        fullPage: false
      });
    }
    await captureRail(page, `${prefix}-rail-${vp.tag}.png`, false);
    await captureRail(page, `${prefix}-rail-bottom-${vp.tag}.png`, true);
    await page.close();
  }
}

await runSet(PREVIEW, 'preview-fix', [
  { w: 1440, h: 900, tag: '1440' },
  { w: 390, h: 844, tag: '390' }
]);

if (PROD) {
  await runSet(PROD, 'prod-before', [
    { w: 1440, h: 900, tag: '1440' },
    { w: 390, h: 844, tag: '390' }
  ]);
} else {
  console.warn('LIREATE_PUBLIC_SITE_URL unset — prod сравнение пропущено');
}

await browser.close();
console.log('artifacts', OUT);
