/**
 * Скриншоты услуг через PHP preview (scripts/lireate_service_preview_server.sh).
 * Usage: node scripts/capture_service_screenshots.mjs
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const OUT = '/opt/cursor/artifacts/screenshots';
const GIF = '/opt/cursor/artifacts/gif-frames';

const routes = [
  { path: '/zashchita_avtorskih_prav/', key: 'zashchita-avtorskih-prav' },
  { path: '/prodlenie_tovarnogo_znaka/', key: 'prodlenie-tovarnogo-znaka' },
  { path: '/registraciya_tovarnogo_znaka_v_kitae/', key: 'registraciya-tovarnogo-znaka-v-kitae' },
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 25000) throw new Error(`${path} too small (${buf.length} bytes)`);
  const text = buf.toString('latin1', 0, 200);
  if (text.includes('Directory listing')) throw new Error(`${path} is directory listing`);
}

await mkdir(OUT, { recursive: true });
await mkdir(GIF, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

async function shot(page, url, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await new Promise((r) => setTimeout(r, 2800));
  await page.screenshot({ path: file, fullPage: false });
  assertPng(file);
  console.log('OK', file);
}

for (const r of routes) {
  const page = await browser.newPage();
  const base = BASE + r.path;
  await shot(page, base, `${OUT}/${r.key}-desktop-hero.png`, 1440, 900);
  await shot(page, base + '#program', `${OUT}/${r.key}-desktop-steps.png`, 1440, 900);
  const revUrl = base + '#reviews';
  await page.goto(revUrl, { waitUntil: 'networkidle2', timeout: 60000 });
  await new Promise((r) => setTimeout(r, 1500));
  const hasReviews = await page.$('#reviews .lx-review-frame');
  if (hasReviews) {
    await page.screenshot({ path: `${OUT}/${r.key}-desktop-reviews.png` });
    assertPng(`${OUT}/${r.key}-desktop-reviews.png`);
    console.log('OK', `${OUT}/${r.key}-desktop-reviews.png`);
  } else {
    console.log('SKIP reviews', r.key);
  }
  await shot(page, base + '#contact', `${OUT}/${r.key}-desktop-footer.png`, 1440, 900);
  await shot(page, base, `${OUT}/${r.key}-mobile-hero.png`, 390, 844);
  await page.close();
}

const mascotPage = await browser.newPage();
await mascotPage.setViewport({ width: 1440, height: 900 });
await mascotPage.goto(BASE + '/prodlenie_tovarnogo_znaka/', { waitUntil: 'networkidle2', timeout: 60000 });
for (let i = 0; i < 6; i++) {
  await new Promise((r) => setTimeout(r, 900));
  const f = `${GIF}/mascot-frame-${i}.png`;
  await mascotPage.screenshot({ path: f });
  assertPng(f);
}
await mascotPage.close();
await browser.close();

console.log('done');
