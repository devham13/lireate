#!/usr/bin/env node
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/findtz-font-v1';
const mode = process.argv[2] || 'after';
if (!HOST) process.exit(2);

const sub = mode === 'before' ? 'before' : 'after';
const dir = path.join(OUT, sub);
fs.mkdirSync(dir, { recursive: true });

const familyCss = fs.readFileSync(
  path.join(REPO, 'pages/services/proverka-tovarnogo-znaka/assets/findtz-inter-family-v1.css'),
  'utf8',
);
const fontCss = fs.readFileSync(
  path.join(REPO, 'pages/services/proverka-tovarnogo-znaka/assets/findtz-inter-font.css'),
  'utf8',
);
const cy = fs.readFileSync(
  path.join(REPO, 'pages/services/proverka-tovarnogo-znaka/assets/fonts/inter/inter-cyrillic.woff2'),
);
const la = fs.readFileSync(
  path.join(REPO, 'pages/services/proverka-tovarnogo-znaka/assets/fonts/inter/inter-latin.woff2'),
);

async function setup(page, inject) {
  await page.route('**/*', async (route) => {
    const req = route.request();
    const url = req.url();
    if (inject && url.includes('findtz-inter-family-v1.css')) {
      return route.fulfill({ status: 200, contentType: 'text/css', body: familyCss });
    }
    if (inject && url.includes('findtz-inter-font.css')) {
      return route.fulfill({ status: 200, contentType: 'text/css', body: fontCss });
    }
    if (inject && url.includes('inter-cyrillic.woff2')) {
      return route.fulfill({ status: 200, contentType: 'font/woff2', body: cy });
    }
    if (inject && url.includes('inter-latin.woff2')) {
      return route.fulfill({ status: 200, contentType: 'font/woff2', body: la });
    }
    if (
      inject &&
      req.resourceType() === 'document' &&
      (url.includes('/findtz') || url.endsWith('/findtz/'))
    ) {
      const resp = await route.fetch({ timeout: 90000 });
      let html = await resp.text();
      html = html.replace(
        /(<body[^>]*class="[^"]*)(lireate-findtz-topnav-center)/,
        '$1$2 lireate-findtz-inter-v1',
      );
      const headInject = [];
      if (!html.includes('findtz-inter-font.css')) {
        headInject.push(
          '<link rel="stylesheet" href="/assets/findtz-inter-font.css?v=20261128-fint1">',
        );
      }
      if (!html.includes('findtz-inter-family-v1.css')) {
        headInject.push(
          '<link rel="stylesheet" href="/assets/findtz-inter-family-v1.css?v=20261128-fint1">',
        );
      }
      if (headInject.length) {
        html = html.replace('</head>', `${headInject.join('\n  ')}\n</head>`);
      }
      html = html.replace(
        /<link rel="preconnect" href="https:\/\/fonts\.googleapis\.com">[\s\S]*?fonts\.googleapis\.com[^>]+>\s*/i,
        '',
      );
      return route.fulfill({
        status: resp.status(),
        contentType: 'text/html; charset=utf-8',
        body: html,
      });
    }
    return route.continue();
  });
}

const plan = [
  ['top-1440.png', 1440, 900, 0],
  ['mid-1440.png', 1440, 900, 1400],
  ['top-1024.png', 1024, 900, 0],
  ['mid-1024.png', 1024, 900, 1200],
  ['top-390.png', 390, 844, 0],
  ['mid-390.png', 390, 844, 900],
];

const browser = await chromium.launch({ headless: true });
for (const [file, w, h, scrollY] of plan) {
  const page = await browser.newPage();
  await setup(page, mode === 'after');
  await page.setViewportSize({ width: w, height: h });
  await page.goto(`${HOST}/findtz/`, { waitUntil: 'domcontentloaded', timeout: 120000 });
  await page.waitForSelector('.lx-hero h1', { timeout: 60000 });
  await page.waitForTimeout(1500);
  if (scrollY) await page.evaluate((y) => window.scrollTo(0, y), scrollY);
  await page.waitForTimeout(400);
  const m = await page.evaluate(() => {
    const h1 = document.querySelector('.lx-hero h1');
    const p = document.querySelector('.lireate-svc-page .lx-section p');
    const cs = (el) => (el ? getComputedStyle(el) : null);
    const doc = document.documentElement;
    return {
      overflowX: doc.scrollWidth > doc.clientWidth + 2,
      h1Font: cs(h1)?.fontFamily,
      pFont: cs(p)?.fontFamily,
      interOk: document.fonts.check('16px Inter', 'Проверка товарного знака'),
    };
  });
  const out = path.join(dir, file);
  await page.screenshot({ path: out });
  console.log(JSON.stringify({ sub, file: out, ...m }));
  await page.close();
}
await browser.close();