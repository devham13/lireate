#!/usr/bin/env node
/** Prod first-screen with document HTML stripped of visible breadcrumbs (branch index.php). */
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
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/svc-no-breadcrumb-reg-prod';
const BRANCH = process.env.LIREATE_PR_BRANCH || 'HEAD';

fs.mkdirSync(OUT, { recursive: true });

function gitShow(rel) {
  const disk = path.join(REPO, rel);
  if (BRANCH === 'HEAD' && fs.existsSync(disk)) {
    return fs.readFileSync(disk, 'utf8');
  }
  try {
    return execFileSync('git', ['show', `${BRANCH}:${rel}`], {
      cwd: REPO,
      encoding: 'utf-8',
    });
  } catch {
    return fs.readFileSync(disk, 'utf8');
  }
}

const INDEX = {
  '/registraciya_tovarnogo_znaka/': 'pages/services/registraciya-tovarnogo-znaka/index.php',
  '/registraciya_tovarnogo_znaka': 'pages/services/registraciya-tovarnogo-znaka/index.php',
  '/prodlenie_tovarnogo_znaka/': 'pages/services/prodlenie-tovarnogo-znaka/index.php',
  '/prodlenie_tovarnogo_znaka': 'pages/services/prodlenie-tovarnogo-znaka/index.php',
};

function stripBreadcrumbs(html) {
  return html.replace(
    /<nav\b[^>]*class="[^"]*lx-breadcrumbs[^"]*"[^>]*>[\s\S]*?<\/nav>\s*/i,
    '',
  );
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
    const rel = INDEX[pathname];
    if (req.resourceType() === 'document' && rel) {
      const resp = await route.fetch({ timeout: 120000 });
      let html = await resp.text();
      html = stripBreadcrumbs(html);
      await route.fulfill({
        status: resp.status(),
        contentType: 'text/html; charset=utf-8',
        body: html,
      });
      return;
    }
    await route.continue();
  });
}

const pages = [
  ['registraciya', '/registraciya_tovarnogo_znaka/'],
  ['prodlenie', '/prodlenie_tovarnogo_znaka/'],
];
const viewports = [
  [1440, 900, '1440'],
  [390, 844, '390'],
];

const browser = await chromium.launch({ headless: true });
for (const [key, urlPath] of pages) {
  const page = await browser.newPage();
  await setup(page);
  for (const [w, h, tag] of viewports) {
    await page.setViewportSize({ width: w, height: h });
    await page.goto(`${HOST}${urlPath}`, { waitUntil: 'load', timeout: 120000 });
    await page.waitForSelector('#leftSideBar, .lx-nav-pill', { timeout: 60000 }).catch(() => {});
    await page.waitForTimeout(1200);
    const crumbs = await page.locator('nav.lx-breadcrumbs').count();
    const menu = await page.evaluate(() => !!document.getElementById('leftSideBar'));
    const file = `${OUT}/${key}-${tag}-top.png`;
    await page.screenshot({ path: file, fullPage: false });
    console.log('OK', file, 'crumbs', crumbs, 'menu', menu);
    if (crumbs > 0 || !menu) process.exitCode = 1;
  }
  await page.close();
}
await browser.close();
console.log('DONE', OUT);