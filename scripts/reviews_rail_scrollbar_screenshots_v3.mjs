/**
 * Скрины scrollbar v3 — preview fix (Chromium ::-webkit-scrollbar без Firefox props).
 * xvfb-run -a node scripts/reviews_rail_scrollbar_screenshots_v3.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/scrollbar/v3';
const PREVIEW = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888/index.php';

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: false,
  defaultViewport: null,
  args: [
    '--no-sandbox',
    '--disable-dev-shm-usage',
    '--disable-blink-features=OverlayScrollbars',
    '--disable-features=OverlayScrollbar',
    '--force-device-scale-factor=1'
  ]
});

async function prepareReviews(page) {
  const ready = await page.evaluate(() => {
    const vp = document.querySelector('#reviews .lx-reviews-rail-viewport');
    const frames = vp ? vp.querySelectorAll('.lx-review-frame').length : 0;
    return { hasReviews: Boolean(document.getElementById('reviews')), frames };
  });
  if (!ready.hasReviews || ready.frames < 5) {
    throw new Error(`reviews rail not ready: ${JSON.stringify(ready)}`);
  }
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    const vp = document.querySelector('#reviews .lx-reviews-rail-viewport');
    vp.scrollIntoView({ block: 'center', inline: 'nearest' });
    vp.scrollLeft = Math.min(240, Math.max(0, vp.scrollWidth - vp.clientWidth));
  });
  await new Promise((r) => setTimeout(r, 700));
  const check = await page.evaluate(() => {
    const vp = document.querySelector('#reviews .lx-reviews-rail-viewport');
    const r = vp.getBoundingClientRect();
    const hero = document.querySelector('.lx-hero--landing');
    const heroR = hero ? hero.getBoundingClientRect() : null;
    return {
      vpY: Math.round(r.y),
      vpH: Math.round(r.height),
      frames: vp.querySelectorAll('.lx-review-frame').length,
      heroBottom: heroR ? Math.round(heroR.bottom) : null,
      heroVisible: heroR ? heroR.bottom > 0 && heroR.top < window.innerHeight : false
    };
  });
  if (check.heroVisible && check.vpY < check.heroBottom) {
    throw new Error(`viewport overlaps hero — wrong crop: ${JSON.stringify(check)}`);
  }
  if (check.frames < 5) throw new Error(`too few frames in viewport: ${JSON.stringify(check)}`);
  console.log('prepare', JSON.stringify(check));
}

async function captureRail(page, outName) {
  const vp = await page.$('#reviews .lx-reviews-rail-viewport');
  if (!vp) throw new Error('missing viewport');
  const box = await vp.boundingBox();
  if (!box) throw new Error('missing bounding box');
  const extra = 20;
  await page.screenshot({
    path: path.join(OUT, outName),
    clip: { x: box.x, y: box.y, width: box.width, height: box.height + extra }
  });
  console.log('saved', outName, JSON.stringify({ ...box, height: box.height + extra }));
  await vp.dispose();
}

for (const vp of [
  { w: 1440, h: 900, tag: '1440' },
  { w: 390, h: 844, tag: '390' }
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: vp.w, height: vp.h, deviceScaleFactor: 1 });
  await page.goto(PREVIEW, { waitUntil: 'networkidle0', timeout: 120000 });
  await prepareReviews(page);
  await captureRail(page, `preview-fix-rail-${vp.tag}.png`);
  await page.close();
}

await browser.close();
console.log('artifacts', OUT);
