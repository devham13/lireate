#!/usr/bin/env node
/** /docs/ light v4 — prod + route (before|after). */
import fs from 'fs';
import path from 'path';
import { execFileSync } from 'node:child_process';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!HOST) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const MODE = (process.env.LIREATE_CAPTURE_MODE || 'after').toLowerCase();
const OUT = process.env.LIREATE_OUT_DIR || `/cursor/stores/self/docs-light-v4/${MODE}`;
const VER = '20261128-docs-light-v1';

fs.mkdirSync(OUT, { recursive: true });

function gitShow(rel) {
  const disk = path.join(REPO, rel);
  return fs.readFileSync(disk, 'utf8');
}

const CMS_CSS = gitShow('template/css/lireate-cms-pages-light-v4.css');
const DOCS_INC = gitShow('pages/docs/_shared/lireate-docs-page.inc.php');

function patchDocsHtml(html) {
  let out = html;
  if (MODE === 'after') {
    if (!out.includes('lireate-theme-light-v4')) {
      out = out.replace(/\bclass="([^"]*lireate-docs-body)([^"]*)"/, 'class="$1 lireate-theme-light-v4$2"');
      out = out.replace(/(<body[^>]*\blireate-docs-body)(?![^"]*lireate-theme-light-v4)/, '$1 lireate-theme-light-v4');
    }
    const incBlock = DOCS_INC.replace(/<\?php[\s\S]*?\?>\s*/g, '').trim();
    const linkNeed = 'lireate-cms-pages-light-v4.css';
    if (!out.includes(linkNeed)) {
      out = out.replace(
        /(<link rel="stylesheet" href="\/template\/page\/docs\/lireate-docs\.css[^>]+>)/,
        `$1\n<link rel="stylesheet" href="/template/css/lireate-light-v4.css?v=${VER}">\n<link rel="stylesheet" href="/template/css/lireate-cms-pages-light-v4.css?v=${VER}">`,
      );
    }
    if (out.includes('<section id="lireate-docs">') && !out.includes(incBlock.slice(0, 80))) {
      /* leave prod inner if already themed */
    }
  }
  return out;
}

async function setup(page) {
  await page.route('**/*', async (route) => {
    const req = route.request();
    let pathname = '';
    try {
      pathname = new URL(req.url()).pathname;
    } catch {
      await route.continue();
      return;
    }
    if (req.resourceType() === 'document' && (pathname === '/docs' || pathname === '/docs/')) {
      const resp = await route.fetch({ timeout: 90000 });
      const html = patchDocsHtml(await resp.text());
      await route.fulfill({
        status: resp.status(),
        contentType: 'text/html; charset=utf-8',
        body: html,
      });
      return;
    }
    if (MODE === 'after' && pathname === '/template/css/lireate-cms-pages-light-v4.css') {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: CMS_CSS,
      });
      return;
    }
    await route.continue();
  });
}

const viewports = [
  [1440, 900, '1440'],
  [390, 844, '390'],
];

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
await setup(page);

for (const [w, h, tag] of viewports) {
  await page.setViewportSize({ width: w, height: h });
  await page.goto(`${HOST}/docs/`, { waitUntil: 'load', timeout: 120000 });
  await page.waitForTimeout(1200);
  const metrics = await page.evaluate(() => {
    const pageEl = document.querySelector('.lireate-docs-page');
    const body = document.body;
    const bg = pageEl ? getComputedStyle(pageEl).backgroundColor : getComputedStyle(body).backgroundColor;
    const dark = (() => {
      const m = bg.match(/\d+/g);
      if (!m || m.length < 3) return false;
      const [r, g, b] = m.map(Number);
      return r + g + b < 120;
    })();
    return {
      bodyClass: body.className,
      hasLight: body.classList.contains('lireate-theme-light-v4'),
      hasCmsCss: !!document.querySelector('link[href*="lireate-cms-pages-light-v4"]'),
      bg,
      darkPage: dark,
    };
  });
  const file = `${OUT}/docs-${tag}.png`;
  await page.screenshot({ path: file, fullPage: false });
  console.log(JSON.stringify({ file, vp: tag, mode: MODE, ...metrics }));
  if (MODE === 'after' && metrics.darkPage) {
    console.error('FAIL: docs still dark', tag);
    process.exitCode = 3;
  }
}
await browser.close();
console.log('DONE', OUT);