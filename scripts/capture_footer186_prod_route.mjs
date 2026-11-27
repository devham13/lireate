#!/usr/bin/env node
/**
 * Real prod screenshots with page.route() CSS overrides from current branch (PR #186 assets).
 */
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const _site = process.env.LIREATE_PUBLIC_SITE_URL;
if (!_site) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const HOST = _site.replace(/\/$/, '');
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/footer184-fix';
const BRANCH = process.env.LIREATE_PR_BRANCH || 'main';

fs.mkdirSync(OUT, { recursive: true });
fs.mkdirSync('/opt/cursor/artifacts/footer184-fix', { recursive: true });

import { execFileSync } from 'node:child_process';

/** pathname (no query) → repo-relative file */
const PATH_OVERRIDES = {
  '/template/page/news/lireate-blog-list.css': 'pages/blog/_shared/lireate-blog-list.css',
  '/template/page/news/lireate-blog-light-v4.css': 'pages/blog/_shared/lireate-blog-light-v4.css',
  '/template/page/news/lireate-blog-footer.css': 'pages/blog/_shared/lireate-blog-footer.css',
  '/template/page/kontaktyi/lireate-contacts.css': 'pages/kontaktyi/_shared/lireate-contacts.css',
  '/template/css/lireate-cms-pages-light-v4.css': 'template/css/lireate-cms-pages-light-v4.css',
  '/_shared/lireate-light-v4.css': 'pages/services/_shared/lireate-light-v4.css',
};

function repoFile(rel) {
  try {
    return execFileSync('git', ['show', `${BRANCH}:${rel}`], {
      cwd: REPO,
      maxBuffer: 8 * 1024 * 1024,
    });
  } catch {
    return fs.readFileSync(path.join(REPO, rel));
  }
}

function localOverride(pathname) {
  let p = pathname;
  if (PATH_OVERRIDES[p]) return PATH_OVERRIDES[p];
  const base = path.posix.basename(p);
  for (const [key, rel] of Object.entries(PATH_OVERRIDES)) {
    if (key.endsWith('/' + base) || key === '/' + base) return rel;
  }
  const m = p.match(/\/assets\/lireate-shared\/lireate-light-v4\.css$/);
  if (m) return 'pages/services/_shared/lireate-light-v4.css';
  return null;
}

async function setupRoutes(page) {
  await page.route('**/*', async (route) => {
    const req = route.request();
    if (req.resourceType() !== 'stylesheet') {
      await route.continue();
      return;
    }
    let pathname;
    try {
      pathname = new URL(req.url()).pathname;
    } catch {
      await route.continue();
      return;
    }
    const rel = localOverride(pathname);
    if (!rel) {
      await route.continue();
      return;
    }
    try {
      const body = repoFile(rel);
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body,
      });
    } catch (e) {
      console.warn('override fail', pathname, rel, e.message);
      await route.continue();
    }
  });
}

async function waitMenu(page, w) {
  if (w >= 1151) {
    await page
      .waitForFunction(() => {
        const bar = document.getElementById('leftSideBar');
        return bar && bar.offsetWidth > 80;
      }, { timeout: 45000 })
      .catch(() => {});
  } else {
    await page
      .waitForSelector('.lsv7-mobile-toggle, #leftSideBar', { timeout: 30000 })
      .catch(() => {});
  }
  await page.waitForTimeout(1200);
}

async function shotBottom(page, file, w, h) {
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.waitForTimeout(600);
  const sh = await page.evaluate(() => document.body.scrollHeight);
  const clipH = Math.min(h, Math.max(420, Math.min(sh, 520)));
  await page.screenshot({
    path: file,
    clip: { x: 0, y: Math.max(0, sh - clipH), width: w, height: clipH },
  });
  console.log('OK', file);
}

const browser = await chromium.launch({ headless: true });

async function capturePage(key, urlPath, shots) {
  const page = await browser.newPage();
  await setupRoutes(page);
  for (const { w, h, tag, mode } of shots) {
    await page.setViewportSize({ width: w, height: h });
    await page.goto(`${HOST}${urlPath}`, { waitUntil: 'networkidle', timeout: 120000 });
    await waitMenu(page, w);
    const file = `${OUT}/${key}-${tag}.png`;
    if (mode === 'top') {
      await page.screenshot({ path: file });
    } else {
      await shotBottom(page, file, w, h);
    }
    console.log('OK', file);
    fs.copyFileSync(file, `/opt/cursor/artifacts/footer184-fix/${path.basename(file)}`);
  }
  await page.close();
}

await capturePage('news', '/news/', [
  { w: 1440, h: 900, tag: '1440-top', mode: 'top' },
  { w: 1440, h: 900, tag: '1440-bottom', mode: 'bottom' },
  { w: 390, h: 844, tag: '390-bottom', mode: 'bottom' },
]);

await capturePage('contacts', '/contacts/', [
  { w: 1440, h: 900, tag: '1440-top', mode: 'top' },
  { w: 1440, h: 900, tag: '1440-bottom', mode: 'bottom' },
]);

await capturePage('prodlenie', '/prodlenie_tovarnogo_znaka/', [
  { w: 390, h: 844, tag: '390-bottom', mode: 'bottom' },
]);

await browser.close();
console.log('DONE', OUT);