/**
 * Full-page screenshots: findtz + registraciya (live-align PR).
 * Requires preview docroot with /findtz/, /registraciya_tovarnogo_znaka/, /template/.
 */
import puppeteer from 'puppeteer-core';
import { mkdir } from 'fs/promises';
import { readFileSync } from 'fs';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const OUT = process.env.LIREATE_SCREENSHOT_OUT || '/opt/cursor/artifacts/live-align-pr75';

const routes = [
  { path: '/findtz/', tag: 'findtz' },
  { path: '/registraciya_tovarnogo_znaka/', tag: 'registraciya' },
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 20000) throw new Error(`${path} too small (${buf.length} bytes)`);
}

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

async function capture(page, url, file, w, h, fullPage) {
  await page.setViewport({ width: w, height: h, deviceScaleFactor: 1 });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });
  await new Promise((r) => setTimeout(r, 2500));
  const horiz = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 2);
  if (horiz) console.warn('WARN horizontal scroll', file, url);
  await page.screenshot({ path: file, fullPage });
  assertPng(file);
  console.log('OK', file);
}

for (const r of routes) {
  const page = await browser.newPage();
  const url = BASE + r.path;
  await capture(page, url, `${OUT}/${r.tag}-desktop-1440-full.png`, 1440, 900, true);
  await capture(page, url, `${OUT}/${r.tag}-mobile-390-full.png`, 390, 844, true);
  const risks = await page.$('#risks .lx-risk-grid');
  const form = await page.$('form#lireate-lead-form[action="/mail.php"]');
  console.log(r.tag, 'risk-grid', !!risks, 'mail-fallback', !!form);
  await page.close();
}

await browser.close();
console.log('done', OUT);
