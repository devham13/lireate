#!/usr/bin/env node
/**
 * Prod mobile footer: bottom clip 390px. Modes:
 *   LIREATE_CAPTURE_MODE=prod (default) — raw prod CSS
 *   LIREATE_CAPTURE_MODE=fix — route overrides from repo (PR preview)
 */
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
const MODE = (process.env.LIREATE_CAPTURE_MODE || 'prod').toLowerCase();
const OUT =
  process.env.LIREATE_OUT_DIR ||
  `/cursor/stores/self/footer-mobile-390-${MODE}`;
const BRANCH = process.env.LIREATE_PR_BRANCH || 'HEAD';

fs.mkdirSync(OUT, { recursive: true });
fs.mkdirSync('/opt/cursor/artifacts/footer-mobile-390', { recursive: true });

function repoFile(rel) {
  if (BRANCH === 'HEAD' && fs.existsSync(path.join(REPO, rel))) {
    return fs.readFileSync(path.join(REPO, rel));
  }
  try {
    return execFileSync('git', ['show', `${BRANCH}:${rel}`], {
      cwd: REPO,
      maxBuffer: 8 * 1024 * 1024,
    });
  } catch {
    return fs.readFileSync(path.join(REPO, rel));
  }
}

const OVERRIDE_MAP = {
  '/template/css/lireate-light-v4.css': 'template/css/lireate-light-v4.css',
  '/template/css/lireate-sitewide-sidebar-v7.css': 'template/css/lireate-sitewide-sidebar-v7.css',
  '/template/css/lireate-service-mobile-bar.css': 'template/css/lireate-service-mobile-bar.css',
  '/_shared/lireate-light-v4.css': 'pages/services/_shared/lireate-light-v4.css',
};

function localOverride(pathname) {
  if (OVERRIDE_MAP[pathname]) return OVERRIDE_MAP[pathname];
  if (pathname.endsWith('/assets/lireate-shared/lireate-light-v4.css')) {
    return 'pages/services/_shared/lireate-light-v4.css';
  }
  return null;
}

async function setupRoutes(page) {
  if (MODE !== 'fix') return;
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
    const body = repoFile(rel);
    await route.fulfill({
      status: 200,
      contentType: 'text/css; charset=utf-8',
      body,
    });
  });
}

async function shotFooter(page, file, w, h) {
  const copy = page.locator('.lx-site-footer-copy').last();
  await copy.scrollIntoViewIfNeeded({ timeout: 60000 }).catch(async () => {
    await page.evaluate(() => window.scrollTo(0, 1e9));
  });
  await page.waitForTimeout(1200);
  await page.screenshot({ path: file, fullPage: false });
  const copyVis = await page
    .locator('.lx-site-footer-copy')
    .last()
  .isVisible()
    .catch(() => false);
  console.log('OK', file, 'mode', MODE, 'copy_visible', copyVis);
  fs.copyFileSync(file, path.join('/opt/cursor/artifacts/footer-mobile-390', path.basename(file)));
}

const pages = [
  ['prodlenie', '/prodlenie_tovarnogo_znaka/'],
  ['registraciya', '/registraciya_tovarnogo_znaka_v_kitae/'],
  ['patent', '/patent'],
];

const browser = await chromium.launch({ headless: true });
for (const [key, urlPath] of pages) {
  const page = await browser.newPage();
  await setupRoutes(page);
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${HOST}${urlPath}`, { waitUntil: 'load', timeout: 120000 });
  await page.waitForSelector('.lsv7-mobile-toggle, footer.lx-site-footer', { timeout: 45000 }).catch(() => {});
  await page.waitForTimeout(1200);
  await shotFooter(page, `${OUT}/${key}-390-bottom-${MODE}.png`, 390, 844);
  await page.close();
}
await browser.close();
console.log('DONE', OUT);