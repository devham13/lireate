#!/usr/bin/env node
/**
 * Load prod page; page.route() fulfills only PR-changed static assets from a git branch.
 * Usage:
 *   LIREATE_PR_BRANCH=cursor/patent-hero-no-sidebar-expert-6022 \
 *   LIREATE_OUT_DIR=/cursor/stores/self/lse10b \
 *   node scripts/capture_prod_pr_route_screenshots.mjs \
 *     patent=https://PUBLIC_SITE/patent \
 *     patent-prom-obr=https://PUBLIC_SITE/patent_prom_obr \
 *     prodlenie:https://PUBLIC_SITE/prodlenie_tovarnogo_znaka/ \
 *     mktu:https://PUBLIC_SITE/mktu
 */
import puppeteer from 'puppeteer-core';
import { readFileSync, mkdirSync, existsSync } from 'fs';
import { execFileSync } from 'child_process';
import path from 'path';

const CHROME = '/usr/local/bin/google-chrome';
const REPO = process.env.LIREATE_REPO || '/agent/repos/lireate';
const BRANCH = process.env.LIREATE_PR_BRANCH;
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/lse10b';
if (!process.env.LIREATE_PUBLIC_SITE_URL) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const HOST = process.env.LIREATE_PUBLIC_SITE_URL.replace(/\/$/, '');

if (!BRANCH) {
  console.error('LIREATE_PR_BRANCH required');
  process.exit(2);
}

const SERVICE_URL_MAP = {
  prodlenie_tovarnogo_znaka: 'pages/services/prodlenie-tovarnogo-znaka',
};

function gitShow(repoRel) {
  return execFileSync('git', ['show', `${BRANCH}:${repoRel}`], {
    cwd: REPO,
    maxBuffer: 15 * 1024 * 1024,
  });
}

function contentType(urlPath) {
  if (urlPath.endsWith('.js')) return 'application/javascript; charset=utf-8';
  if (urlPath.endsWith('.css')) return 'text/css; charset=utf-8';
  if (urlPath.endsWith('.php')) return 'text/html; charset=utf-8';
  return 'text/html; charset=utf-8';
}

function pathnameToRepoRel(pathname) {
  if (pathname.startsWith('/template/')) {
    return pathname.slice(1);
  }
  const m = pathname.match(/^\/([^/]+)\/assets\/lireate-shared\/(.+)$/);
  if (m) {
    const slugDir = SERVICE_URL_MAP[m[1]];
    if (slugDir) return `${slugDir}/assets/lireate-shared/${m[2]}`;
  }
  return null;
}

function buildOverridesFromDiff() {
  const out = execFileSync(
    'git',
    ['diff', '--name-only', `origin/main...${BRANCH}`],
    { cwd: REPO, encoding: 'utf-8' },
  );
  const overrides = new Map();
  for (const repoRel of out.split('\n').filter(Boolean)) {
    if (repoRel.startsWith('pages/services/') && repoRel.includes('/assets/lireate-shared/')) {
      const parts = repoRel.split('/');
      const slug = parts[2];
      const file = parts.slice(4).join('/');
      let hosting = '';
      try {
        const indexPhp = execFileSync('git', ['show', `${BRANCH}:pages/services/${slug}/index.php`], {
          cwd: REPO,
          encoding: 'utf-8',
        });
        const pm = indexPhp.match(/\$path\s*=\s*'([^']+)'/);
        if (pm) hosting = pm[1].replace(/^\//, '').replace(/\/$/, '');
      } catch {
        /* noop */
      }
      if (!hosting && SERVICE_URL_MAP[slug.replace(/-/g, '_')]) {
        hosting = slug.replace(/-/g, '_');
      }
      if (!hosting) {
        const alias = slug.replace(/-/g, '_');
        if (SERVICE_URL_MAP[alias]) hosting = alias;
      }
      if (hosting) {
        overrides.set(`/${hosting}/assets/lireate-shared/${file}`, repoRel);
      }
    } else if (
      repoRel.startsWith('template/') &&
      (repoRel.endsWith('.js') || repoRel.endsWith('.css') || repoRel.endsWith('.html') || repoRel.endsWith('.php'))
    ) {
      const urlPath = '/' + repoRel.replace(/^template\//, 'template/');
      overrides.set(urlPath, repoRel);
    }
  }
  const requiredTemplate = [
    'template/js/lireate-sitewide-sidebar-v7.js',
    'template/css/lireate-sitewide-sidebar-v7.css',
    'template/lireate/lireate-sidebar-v7.html',
    'template/lireate/lireate-sidebar-v7-fragment.php',
  ];
  for (const repoRel of requiredTemplate) {
    try {
      gitShow(repoRel);
      const urlPath = '/' + repoRel;
      overrides.set(urlPath, repoRel);
    } catch {
      /* skip missing */
    }
  }
  return overrides;
}

const entries = process.argv.slice(2);
if (!entries.length) {
  console.error('Need key=url pairs');
  process.exit(2);
}

mkdirSync(OUT, { recursive: true });
const overrides = buildOverridesFromDiff();

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

for (const entry of entries) {
  const idx = entry.indexOf('=');
  if (idx < 1) throw new Error(`bad entry ${entry}`);
  const key = entry.slice(0, idx);
  const pageUrl = entry.slice(idx + 1);
  const fullUrl = pageUrl.startsWith('http') ? pageUrl : `${HOST}${pageUrl}`;

  const page = await browser.newPage();
  await page.setRequestInterception(true);
  page.on('request', (req) => {
    const type = req.resourceType();
    if (!['document', 'script', 'stylesheet', 'xhr', 'fetch'].includes(type)) {
      req.continue().catch(() => {});
      return;
    }
    let pathname;
    try {
      pathname = new URL(req.url()).pathname;
    } catch {
      req.continue().catch(() => {});
      return;
    }
    const repoRel = overrides.get(pathname);
    if (repoRel) {
      try {
        const body = gitShow(repoRel);
        req
          .respond({
            status: 200,
            contentType: contentType(pathname),
            body,
          })
          .catch(() => {});
        return;
      } catch (e) {
        console.warn('override miss', pathname, repoRel, e.message);
      }
    }
    req.continue().catch(() => {});
  });

  for (const [w, h, tag] of [
    [1440, 900, '1440'],
    [390, 844, '390'],
  ]) {
    await page.setViewport({ width: w, height: h });
    await page.goto(fullUrl, { waitUntil: 'networkidle2', timeout: 120000 });
    await page
      .waitForFunction(
        () => {
          const bar = document.getElementById('leftSideBar');
          return bar && bar.offsetWidth > 0;
        },
        { timeout: 45000 },
      )
      .catch(() => {});
    await page
      .waitForFunction(
        () =>
          !document.querySelector('.lx-breadcrumb-services-trigger') &&
          !!document.querySelector('.lx-breadcrumbs a[href="/uslugi/"]'),
        { timeout: 15000 },
      )
      .catch(() => {});
    await new Promise((r) => setTimeout(r, 3500));
  if (tag === '1440' && key === 'prodlenie') {
      const nav = await page.$('.lx-breadcrumbs--in-page');
      const cropFile = path.join(OUT, 'prodlenie-breadcrumbs-crop-1440.png');
      if (nav) {
        await nav.screenshot({ path: cropFile });
        console.log('OK breadcrumb crop', cropFile, readFileSync(cropFile).length);
      }
    }
    const file = path.join(OUT, `${key}-firstscreen-${tag}.png`);
    await page.screenshot({ path: file, fullPage: false });
    const sz = readFileSync(file).length;
    if (sz < 12000) console.warn('small', file, sz);
    const bad = await page.$$eval(
      '#leftSideBar img[src*="person/"], #leftSideBar .lsv7-sidebar-expert',
      (n) => n.length,
    );
    console.log(bad ? 'WARN sidebar person' : 'OK sidebar clean', key, tag, file, sz);
  }
  await page.close();
}

await browser.close();
console.log('done', OUT);