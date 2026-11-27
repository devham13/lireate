#!/usr/bin/env node
/**
 * Prod route override + Playwright asserts: plain «Услуги» breadcrumb (no pill/arrow).
 * Usage:
 *   LIREATE_PUBLIC_SITE_URL=https://example.test \
 *   LIREATE_PR_BRANCH=origin/cursor/breadcrumb-plain-uslugi-6022 \
 *   node scripts/verify_breadcrumb_plain_uslugi.mjs /prodlenie_tovarnogo_znaka/ /registraciya_tovarnogo_znaka/
 */
import puppeteer from 'puppeteer-core';
import { execFileSync } from 'child_process';

const CHROME = '/usr/local/bin/google-chrome';
const REPO = process.env.LIREATE_REPO || '/agent/repos/lireate';
const BRANCH = process.env.LIREATE_PR_BRANCH || 'origin/cursor/breadcrumb-plain-uslugi-6022';
if (!process.env.LIREATE_PUBLIC_SITE_URL) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const HOST = process.env.LIREATE_PUBLIC_SITE_URL.replace(/\/$/, '');

const SERVICE_URL_MAP = {
  prodlenie_tovarnogo_znaka: 'pages/services/prodlenie-tovarnogo-znaka',
  registraciya_tovarnogo_znaka: 'pages/services/registraciya-tovarnogo-znaka',
};

const SHARED_OVERRIDE_FILES = [
  'lireate-breadcrumbs.js',
  'lireate-light-v4.css',
  'lireate-service-dark.css',
];

function gitShow(repoRel) {
  return execFileSync('git', ['show', `${BRANCH}:${repoRel}`], {
    cwd: REPO,
    maxBuffer: 20 * 1024 * 1024,
  });
}

function hostingFromSlug(slug) {
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

function buildOverrides() {
  const overrides = new Map();
  for (const [hosting, slugDir] of Object.entries(SERVICE_URL_MAP)) {
    for (const file of SHARED_OVERRIDE_FILES) {
      const repoRel = `${slugDir}/assets/lireate-shared/${file}`;
      try {
        gitShow(repoRel);
        overrides.set(`/${hosting}/assets/lireate-shared/${file}`, repoRel);
      } catch {
        const fallback = `pages/services/_shared/${file}`;
        gitShow(fallback);
        overrides.set(`/${hosting}/assets/lireate-shared/${file}`, fallback);
      }
    }
  }
  const templateFiles = [
    'template/js/lireate-sitewide-sidebar-v7.js',
    'template/css/lireate-sitewide-sidebar-v7.css',
  ];
  for (const repoRel of templateFiles) {
    try {
      gitShow(repoRel);
      overrides.set(`/${repoRel}`, repoRel);
    } catch {
      /* skip */
    }
  }
  return overrides;
}

async function assertBreadcrumb(page, label) {
  const res = await page.evaluate(() => {
    const trigger = document.querySelector('.lx-breadcrumb-services-trigger');
    const link = document.querySelector('.lx-breadcrumbs a[href="/uslugi/"]');
    if (trigger) {
      const cs = getComputedStyle(trigger);
      return { ok: false, why: 'trigger-present', border: cs.borderWidth, bg: cs.backgroundColor };
    }
    if (!link) {
      return { ok: false, why: 'missing-uslugi-link' };
    }
    const cs = getComputedStyle(link);
    const after = getComputedStyle(link, '::after');
    const menu = document.querySelector('.lx-breadcrumb-services-menu');
    const home = document.querySelector('.lx-breadcrumbs a[href="/"]');
    const homeBorder = home ? getComputedStyle(home).borderWidth : 'n/a';
    const border0 = cs.borderWidth === '0px' || cs.borderTopWidth === '0px';
    const bgTrans =
      cs.backgroundColor === 'rgba(0, 0, 0, 0)' ||
      cs.backgroundColor === 'transparent' ||
      cs.backgroundColor.endsWith(', 0)');
    const noArrow =
      after.content === 'none' || after.content === 'normal' || after.content === '""' || after.content === '';
    return {
      ok: border0 && bgTrans && !menu && noArrow,
      border: cs.borderWidth,
      bg: cs.backgroundColor,
      after: after.content,
      hasMenu: !!menu,
      homeBorder,
      uslugiBorder: cs.borderWidth,
    };
  });
  if (!res.ok) {
    console.error('FAIL', label, res);
    return false;
  }
  console.log('OK', label, res);
  return true;
}

const paths = process.argv.slice(2);
if (!paths.length) {
  console.error('Need path(s) e.g. /prodlenie_tovarnogo_znaka/');
  process.exit(2);
}

const overrides = buildOverrides();
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

let failed = false;
for (const p of paths) {
  const page = await browser.newPage();
  await page.setRequestInterception(true);
  page.on('request', (req) => {
    const type = req.resourceType();
    if (!['document', 'script', 'stylesheet'].includes(type)) {
      req.continue().catch(() => {});
      return;
    }
    const pathname = new URL(req.url()).pathname;
    const repoRel = overrides.get(pathname);
    if (repoRel) {
      const ct = pathname.endsWith('.css')
        ? 'text/css; charset=utf-8'
        : 'application/javascript; charset=utf-8';
      req.respond({ status: 200, contentType: ct, body: gitShow(repoRel) }).catch(() => {});
      return;
    }
    req.continue().catch(() => {});
  });
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(`${HOST}${p.startsWith('/') ? p : `/${p}`}`, {
    waitUntil: 'networkidle2',
    timeout: 120000,
  });
  await page
    .waitForFunction(
      () =>
        !document.querySelector('.lx-breadcrumb-services-trigger') &&
        !!document.querySelector('.lx-breadcrumbs a[href="/uslugi/"]'),
      { timeout: 20000 },
    )
    .catch(() => {});
  failed = !(await assertBreadcrumb(page, p)) || failed;
  await page.close();
}

await browser.close();
process.exit(failed ? 1 : 0);