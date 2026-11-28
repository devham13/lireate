#!/usr/bin/env node
/** Prod HTML + branch topnav CSS → header-center-v1 screenshots. */
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!HOST) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/header-center-v2';
const VER = '20261128-hcv2';

fs.mkdirSync(OUT, { recursive: true });

const TOPNAV_CSS = fs.readFileSync(
  path.join(REPO, 'pages/services/_shared/lireate-svc-topnav-center.css'),
  'utf8',
);
const FINDTZ_CSS = fs.readFileSync(
  path.join(REPO, 'pages/services/proverka-tovarnogo-znaka/assets/findtz-topnav-center.css'),
  'utf8',
);
const PHOTO_CSS = fs.readFileSync(
  path.join(REPO, 'template/css/lireate-six-services-photo-v4.css'),
  'utf8',
);
const PHOTO_JS = fs.readFileSync(
  path.join(REPO, 'template/js/lireate-six-services-photo-v4.js'),
  'utf8',
);

function patchBody(html) {
  if (!html.includes('lireate-svc-topnav-center')) {
    html = html.replace(
      /(<body[^>]*class="[^"]*)(lireate-theme-light-v4)/,
      '$1$2 lireate-svc-topnav-center',
    );
  }
  if (!html.includes('lireate-svc-topnav-center.css')) {
    html = html.replace(
      /<\/head>/i,
      `<link rel="stylesheet" href="/assets/lireate-shared/lireate-svc-topnav-center.css?v=${VER}">\n</head>`,
    );
  }
  return html;
}

async function setupPage(page, slugPath) {
  await page.route('**/*', async (route) => {
    const req = route.request();
    let pathname = '';
    try {
      pathname = new URL(req.url()).pathname;
    } catch {
      await route.continue();
      return;
    }
    if (pathname.includes('lireate-svc-topnav-center.css')) {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: TOPNAV_CSS,
      });
      return;
    }
    if (pathname.includes('findtz-topnav-center.css')) {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: FINDTZ_CSS,
      });
      return;
    }
    if (pathname.includes('lireate-six-services-photo-v4.css')) {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: PHOTO_CSS,
      });
      return;
    }
    if (pathname.includes('lireate-six-services-photo-v4.js')) {
      await route.fulfill({
        status: 200,
        contentType: 'application/javascript; charset=utf-8',
        body: PHOTO_JS,
      });
      return;
    }
    if (
      req.resourceType() === 'document' &&
      (pathname === slugPath || pathname === `${slugPath}/`)
    ) {
      const resp = await route.fetch({ timeout: 90000 });
      const html = patchBody(await resp.text());
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

async function metrics(page) {
  return page.evaluate(() => {
    const pill = document.querySelector('.lx-nav-pill');
    const col = document.querySelector('.lireate-svc-page') || document.querySelector('#primary');
    const eye = document.querySelector('.lx-eyebrow');
    const pr = pill?.getBoundingClientRect();
    const cr = col?.getBoundingClientRect();
    const er = eye?.getBoundingClientRect();
    const pillCenterX = pr ? pr.left + pr.width / 2 : 0;
    const heroCenterX = cr ? cr.left + cr.width / 2 : 0;
    const stack = document.querySelector('.lx-photo-v4-expert-stack');
    let expert = null;
    if (stack) {
      const kids = [...stack.children];
      const card = stack.querySelector('.lx-photo-v4-expert-card');
      const next = stack.querySelector('.lx-photo-v4-expert-next');
      const cap = stack.querySelector('.lx-photo-v4-expert-caption');
      expert = {
        kidOrder: kids.map((k) => k.className.split(' ')[0]),
        cardBeforeNext: !!(card && next && card.getBoundingClientRect().top < next.getBoundingClientRect().top),
        captionDisplay: cap ? getComputedStyle(cap).display : null,
      };
    }
    return {
      pillHeroDelta: Math.round(Math.abs(pillCenterX - heroCenterX)),
      eyebrowGap: pr && er ? Math.round(er.top - pr.bottom) : null,
      eyebrowVisible: !!(er && pr && er.top >= pr.bottom + 18),
      expert,
    };
  });
}

async function shot(browser, slug, file, w, h, control) {
  const page = await browser.newPage();
  await setupPage(page, slug);
  await page.setViewportSize({ width: w, height: h });
  await page.goto(`${HOST}${slug}/`, { waitUntil: 'networkidle', timeout: 120000 });
  if (!control) {
    await page.waitForSelector('#leftSideBar', { timeout: 90000 }).catch(() => {});
    await page.waitForFunction(
      () => document.body.classList.contains('lx-photo-v4') || !document.querySelector('.lx-hero'),
      { timeout: 15000 },
    ).catch(() => {});
  }
  await page.waitForTimeout(800);
  const m = await metrics(page);
  const out = path.join(OUT, file);
  await page.screenshot({ path: out });
  console.log(JSON.stringify({ file: out, slug, w, control: !!control, ...m }));
  await page.close();
  return m;
}

const browser = await chromium.launch({ headless: true });
let fail = 0;
const plan = [
  ['/registraciya_tovarnogo_znaka', 'reg-1440.png', 1440, 900, false],
  ['/registraciya_tovarnogo_znaka', 'reg-1100.png', 1100, 900, false],
  ['/registraciya_tovarnogo_znaka', 'reg-390.png', 390, 844, false],
  ['/findtz', 'findtz-1440.png', 1440, 900, false],
  ['/findtz', 'findtz-1100.png', 1100, 900, false],
  ['/findtz', 'findtz-390.png', 390, 844, false],
  ['/prodlenie_tovarnogo_znaka', 'prodlenie-1440.png', 1440, 900, false],
  ['/prodlenie_tovarnogo_znaka', 'prodlenie-1100.png', 1100, 900, false],
  ['/prodlenie_tovarnogo_znaka', 'prodlenie-390.png', 390, 844, false],
  ['/patent', 'patent-1440-control.png', 1440, 900, true],
];

for (const [slug, file, w, h, control] of plan) {
  const m = await shot(browser, slug, file, w, h, control);
  if (!control) {
    if (m.pillHeroDelta > 28) fail += 1;
    const gapMin = w >= 1151 ? 20 : 10;
    if (m.eyebrowGap !== null && (m.eyebrowGap < gapMin || m.eyebrowGap > 48)) fail += 1;
    if (w >= 1151 && !m.eyebrowVisible) fail += 1;
    if (
      m.expert &&
      (slug === '/registraciya_tovarnogo_znaka' || slug === '/prodlenie_tovarnogo_znaka')
    ) {
      if (!m.expert.cardBeforeNext) fail += 1;
      if (m.expert.captionDisplay && m.expert.captionDisplay !== 'none') fail += 1;
    }
  }
}
await browser.close();
process.exit(fail ? 3 : 0);