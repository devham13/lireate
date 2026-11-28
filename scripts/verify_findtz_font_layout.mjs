#!/usr/bin/env node
/** After preview: no stray ">" text; pill/h1 top within ±2px of prod before. */
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!HOST) process.exit(2);

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

function patchAfter(html) {
  html = html.replace(
    /(<body[^>]*class="[^"]*)(lireate-findtz-topnav-center)/,
    '$1$2 lireate-findtz-inter-v1',
  );
  const inject = [];
  if (!html.includes('findtz-inter-font.css')) {
    inject.push('<link rel="stylesheet" href="/assets/findtz-inter-font.css?v=20261128-fint1">');
  }
  if (!html.includes('findtz-inter-family-v1.css')) {
    inject.push('<link rel="stylesheet" href="/assets/findtz-inter-family-v1.css?v=20261128-fint1">');
  }
  if (inject.length) html = html.replace('</head>', `${inject.join('\n  ')}\n</head>`);
  html = html.replace(
    /<link rel="preconnect" href="https:\/\/fonts\.googleapis\.com">[\s\S]*?fonts\.googleapis\.com[^>]+>\s*/i,
    '',
  );
  return html;
}

async function measure(page, inject, viewport) {
  await page.unroute('**/*');
  await page.route('**/*', async (route) => {
    const req = route.request();
    const u = req.url();
    if (inject && u.includes('findtz-inter-family-v1.css')) {
      return route.fulfill({ status: 200, contentType: 'text/css', body: familyCss });
    }
    if (inject && u.includes('findtz-inter-font.css')) {
      return route.fulfill({ status: 200, contentType: 'text/css', body: fontCss });
    }
    if (inject && u.includes('inter-cyrillic.woff2')) {
      return route.fulfill({ status: 200, contentType: 'font/woff2', body: cy });
    }
    if (inject && u.includes('inter-latin.woff2')) {
      return route.fulfill({ status: 200, contentType: 'font/woff2', body: la });
    }
    if (inject && req.resourceType() === 'document' && u.includes('/findtz')) {
      const r = await route.fetch();
      return route.fulfill({
        status: r.status(),
        contentType: 'text/html; charset=utf-8',
        body: patchAfter(await r.text()),
      });
    }
    return route.continue();
  });
  await page.setViewportSize(viewport);
  await page.goto(`${HOST}/findtz/`, { waitUntil: 'domcontentloaded', timeout: 120000 });
  await page.waitForSelector('#leftSideBar', { timeout: 60000 }).catch(() => {});
  await page.waitForSelector('.lx-hero h1', { timeout: 60000 });
  await page.waitForTimeout(1200);
  return page.evaluate(() => {
    const firstText = (() => {
      const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
      let n;
      while ((n = walker.nextNode())) {
        const t = (n.textContent || '').trim();
        if (t) return t.slice(0, 40);
      }
      return '';
    })();
    const pill = document.querySelector('.lx-nav-pill');
    const h1 = document.querySelector('.lx-hero h1');
    return {
      firstText,
      pillTop: pill ? pill.getBoundingClientRect().top : null,
      h1Top: h1 ? h1.getBoundingClientRect().top : null,
    };
  });
}

const viewports = [
  [1440, 900],
  [1024, 900],
  [390, 844],
];
const browser = await chromium.launch();
const page = await browser.newPage();
let ok = true;
const report = [];
for (const [w, h] of viewports) {
  const vp = { width: w, height: h };
  const before = await measure(page, false, vp);
  const after = await measure(page, true, vp);
  const pillDelta = Math.abs((before.pillTop ?? 0) - (after.pillTop ?? 0));
  const h1Delta = Math.abs((before.h1Top ?? 0) - (after.h1Top ?? 0));
  const rowOk =
    firstTextNotGt(before.firstText) &&
    firstTextNotGt(after.firstText) &&
    pillDelta <= 2 &&
    h1Delta <= 2;
  ok = ok && rowOk;
  report.push({ w, h, before, after, pillDelta, h1Delta, ok: rowOk });
}
await browser.close();
console.log(JSON.stringify({ ok, report }));
process.exit(ok ? 0 : 3);

function firstTextNotGt(text) {
  return text !== '>' && !text.startsWith('>');
}