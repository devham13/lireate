/**
 * Скрины ленты отзывов с кастомным scrollbar.
 * node scripts/reviews_rail_scrollbar_screenshots.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/scrollbar';
const PREVIEW = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888/index.php';

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage']
});

async function layoutMetrics(page, tag) {
  const m = await page.evaluate(() => {
    const main = document.querySelector('.registraciya-tovarnogo-znaka-page.lireate-svc-page');
    const r = main ? main.getBoundingClientRect() : null;
    const de = document.documentElement;
    return {
      mainWidth: r ? Math.round(r.width) : 0,
      scrollWidth: de.scrollWidth,
      clientWidth: de.clientWidth
    };
  });
  console.log('layout', tag, JSON.stringify(m));
  return m;
}

async function shotReviews(page, name, fullPage) {
  const box = await page.evaluate(() => {
    const vp = document.querySelector('#reviews .lx-reviews-rail-viewport');
    if (!vp) return null;
    vp.scrollLeft = 40;
    const r = vp.getBoundingClientRect();
    return { x: r.x, y: r.y + r.height - 72, width: r.width, height: 72 };
  });
  if (box) {
    await page.screenshot({ path: path.join(OUT, name), clip: box });
  }
}

for (const vp of [
  { w: 1440, h: 900, tag: '1440' },
  { w: 390, h: 844, tag: '390' }
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: vp.w, height: vp.h });
  await page.goto(PREVIEW, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.getElementById('reviews')?.scrollIntoView({ block: 'center' });
  });
  await new Promise((r) => setTimeout(r, 400));
  await layoutMetrics(page, vp.tag);
  if (vp.w === 1440) {
    await page.screenshot({ path: path.join(OUT, `page-desktop-${vp.tag}.png`), fullPage: false });
    await shotReviews(page, `rail-scrollbar-desktop-${vp.tag}.png`);
  } else {
    await shotReviews(page, `rail-scrollbar-mobile-${vp.tag}.png`);
  }
  await page.close();
}

// mainWidth @ 1920
{
  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 900 });
  await page.goto(PREVIEW, { waitUntil: 'networkidle0', timeout: 120000 });
  const m = await page.evaluate(() => {
    const main = document.querySelector('.registraciya-tovarnogo-znaka-page.lireate-svc-page');
    const r = main.getBoundingClientRect();
    return { mainWidth: Math.round(r.width), mainLeft: Math.round(r.left) };
  });
  console.log('layout 1920', JSON.stringify(m));
  await page.close();
}

await browser.close();
console.log('artifacts', OUT);
