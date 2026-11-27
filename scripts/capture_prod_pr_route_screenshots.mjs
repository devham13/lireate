#!/usr/bin/env node
/**
 * Load prod page; page.route() fulfills PR-changed static assets from a git branch.
 */
import puppeteer from 'puppeteer-core';
import { readFileSync, mkdirSync } from 'fs';
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
  registraciya_tovarnogo_znaka: 'pages/services/registraciya-tovarnogo-znaka',
};

const SERVICE_SHARED_ASSETS = [
  'lireate-breadcrumbs.js',
  'lireate-light-v4.css',
  'lireate-service-dark.css',
];

const VIEWPORTS_BY_KEY = {
  prodlenie: [
    [1440, 900, '1440'],
    [390, 844, '390'],
  ],
  registraciya: [
    [1440, 900, '1440'],
    [390, 844, '390'],
  ],
  'patent-prom-obr': [
    [1440, 900, '1440'],
    [768, 900, '768'],
    [390, 844, '390'],
  ],
  patent: [[390, 844, '390']],
  default: [
    [1440, 900, '1440'],
    [390, 844, '390'],
  ],
};

const BREADCRUMB_CROP_KEYS = new Set(['prodlenie', 'registraciya']);

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

function hostingPathFromSlug(slug) {
  try {
    const indexPhp = execFileSync('git', ['show', `${BRANCH}:pages/services/${slug}/index.php`], {
      cwd: REPO,
      encoding: 'utf-8',
    });
    const pm = indexPhp.match(/\$path\s*=\s*'([^']+)'/);
    if (pm) return pm[1].replace(/^\//, '').replace(/\/$/, '');
  } catch {
    /* noop */
  }
  return slug.replace(/-/g, '_');
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
      const hosting = hostingPathFromSlug(slug);
      if (hosting) {
        overrides.set(`/${hosting}/assets/lireate-shared/${file}`, repoRel);
      }
    } else if (
      repoRel.startsWith('template/') &&
      (repoRel.endsWith('.js') || repoRel.endsWith('.css') || repoRel.endsWith('.html') || repoRel.endsWith('.php'))
    ) {
      overrides.set(`/${repoRel}`, repoRel);
    } else if (repoRel.startsWith('pages/services/_shared/')) {
      const file = repoRel.replace('pages/services/_shared/', '');
      if (SERVICE_SHARED_ASSETS.includes(file)) {
        for (const [hosting, slugDir] of Object.entries(SERVICE_URL_MAP)) {
          overrides.set(`/${hosting}/assets/lireate-shared/${file}`, `${slugDir}/assets/lireate-shared/${file}`);
          try {
            gitShow(`${slugDir}/assets/lireate-shared/${file}`);
          } catch {
            overrides.set(`/${hosting}/assets/lireate-shared/${file}`, repoRel);
          }
        }
      }
    }
  }
  for (const [hosting, slugDir] of Object.entries(SERVICE_URL_MAP)) {
    for (const file of SERVICE_SHARED_ASSETS) {
      const url = `/${hosting}/assets/lireate-shared/${file}`;
      const repoRel = `${slugDir}/assets/lireate-shared/${file}`;
      try {
        gitShow(repoRel);
        overrides.set(url, repoRel);
      } catch {
        try {
          gitShow(`pages/services/_shared/${file}`);
          overrides.set(url, `pages/services/_shared/${file}`);
        } catch {
          /* skip */
        }
      }
    }
  }
  const requiredTemplate = [
    'template/js/lireate-sitewide-sidebar-v7.js',
    'template/css/lireate-sitewide-sidebar-v7.css',
    'template/lireate/lireate-sidebar-v7.html',
    'template/lireate/lireate-sidebar-v7-fragment.php',
    'template/css/lireate-patent-light-v4.css',
  ];
  for (const repoRel of requiredTemplate) {
    try {
      gitShow(repoRel);
      overrides.set(`/${repoRel}`, repoRel);
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
  const viewports = VIEWPORTS_BY_KEY[key] || VIEWPORTS_BY_KEY.default;

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

  for (const [w, h, tag] of viewports) {
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
    if (key === 'patent-prom-obr') {
      await page
        .waitForFunction(
          (viewportTag) => {
            const root = document.querySelector('#lireate-patent-v4.lp4-patent_prom_obr');
            const hero = root?.querySelector('.lp4-hero');
            const h1 = root?.querySelector('h1');
            const photo = root?.querySelector('.lp4-photo-card');
            if (!hero || !h1) return false;
            const colCount = getComputedStyle(hero).gridTemplateColumns.trim().split(/\s+/).length;
            const h1Style = getComputedStyle(h1);
            const hyphensOk =
              h1Style.hyphens === 'none' || h1Style.webkitHyphens === 'none' || h1Style.hyphens === 'manual';
            if (viewportTag === '1440') {
              return colCount >= 2 && hyphensOk;
            }
            if (viewportTag === '768' || viewportTag === '390') {
              if (colCount !== 1 || !hyphensOk) return false;
              if (!photo) return true;
              return photo.getBoundingClientRect().top >= h1.getBoundingClientRect().bottom - 4;
            }
            return true;
          },
          { timeout: 20000 },
          tag,
        )
        .catch(() => console.warn('WARN prom-obr layout assert', key, tag));
    }
    if (BREADCRUMB_CROP_KEYS.has(key)) {
      await page
        .waitForFunction(
          () => {
            const trigger = document.querySelector('.lx-breadcrumb-services-trigger');
            const link = document.querySelector('.lx-breadcrumbs a[href="/uslugi/"]');
            if (trigger) return false;
            if (!link) return false;
            const cs = getComputedStyle(link);
            const after = getComputedStyle(link, '::after');
            const borderOk = cs.borderTopWidth === '0px';
            const bg = cs.backgroundColor;
            const bgOk = bg === 'rgba(0, 0, 0, 0)' || bg === 'transparent' || bg.endsWith(', 0)');
            const arrowOk =
              after.content === 'none' || after.content === 'normal' || after.content === '""';
            return borderOk && bgOk && arrowOk;
          },
          { timeout: 20000 },
        )
        .catch(() => {
          console.warn('WARN breadcrumb assert timeout', key, tag);
        });
    }
    await new Promise((r) => setTimeout(r, 2500));
    if (BREADCRUMB_CROP_KEYS.has(key) && (tag === '1440' || tag === '390')) {
      const nav = await page.$('.lx-breadcrumbs--in-page');
      const cropFile = path.join(OUT, `${key}-breadcrumbs-crop-${tag}.png`);
      if (nav) {
        await nav.screenshot({ path: cropFile });
        console.log('OK breadcrumb crop', cropFile, readFileSync(cropFile).length);
      }
    }
    if (!BREADCRUMB_CROP_KEYS.has(key) || tag === '1440' || tag === '390') {
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
  }
  await page.close();
}

await browser.close();
console.log('done', OUT);