/**
 * Сравнение ширины контента: prod (после отката) vs превью fix-ветки.
 * node scripts/registraciya_layout_fix_screenshots.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/reviews-map/fix';
const siteBase = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!siteBase) {
  throw new Error('Set LIREATE_PUBLIC_SITE_URL for prod comparison screenshots');
}
const PROD = `${siteBase}/registraciya_tovarnogo_znaka/`;
const PREVIEW = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888/index.php';

fs.mkdirSync(OUT, { recursive: true });

const widths = [
  { w: 390, name: '390' },
  { w: 1440, name: '1440' },
  { w: 1920, name: '1920' }
];

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage']
});

async function metrics(page, label) {
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
  });
  const m = await page.evaluate(() => {
    const main = document.querySelector('.registraciya-tovarnogo-znaka-page.lireate-svc-page');
    const de = document.documentElement;
    const r = main ? main.getBoundingClientRect() : null;
    const cs = main ? window.getComputedStyle(main) : null;
    return {
      mainWidth: r ? Math.round(r.width) : 0,
      mainLeft: r ? Math.round(r.left) : 0,
      maxWidth: cs ? cs.maxWidth : '',
      scrollWidth: de.scrollWidth,
      clientWidth: de.clientWidth
    };
  });
  console.log(`${label}`, JSON.stringify(m));
  return m;
}

for (const src of [
  { key: 'prod-rollback', url: PROD },
  { key: 'preview-fix', url: PREVIEW }
]) {
  for (const vp of widths) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: vp.w < 500 ? 844 : 900 });
    await page.goto(src.url, { waitUntil: 'networkidle0', timeout: 120000 });
    await metrics(page, `${src.key}-${vp.name}`);
    await page.screenshot({
      path: path.join(OUT, `${src.key}-${vp.name}.png`),
      fullPage: false
    });
    await page.close();
  }
}

await browser.close();
console.log('artifacts', OUT);
