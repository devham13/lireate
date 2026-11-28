#!/usr/bin/env node
/** /findtz/ hero photo — prod HTML + route CSS/JS from branch → findtz-hero-photo-v3 */
import fs from 'fs';
import path from 'path';
import { execFileSync } from 'node:child_process';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const _site = process.env.LIREATE_PUBLIC_SITE_URL;
if (!_site) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const HOST = _site.replace(/\/$/, '');
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/findtz-hero-photo-v4';
const BRANCH = process.env.LIREATE_PR_BRANCH || 'HEAD';
const VER = '20261128-findtz6';

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

function heroBlockFromIndex() {
  const raw = gitShow('pages/services/proverka-tovarnogo-znaka/index.php');
  const start = raw.indexOf('<section class="lx-hero"');
  const end = raw.indexOf('<section class="lx-section lx-reveal" id="when">');
  if (start < 0 || end < 0) {
    throw new Error('findtz hero block not found in index.php');
  }
  let block = raw.slice(start, end);
  block = block.replace(/<\?php[\s\S]*?\?>\s*/g, '');
  block = block.replace(/findtz-hero\.(webp|jpg)\?v=[^"]+/g, `findtz-hero.$1?v=${VER}`);
  return block;
}

const HERO_BLOCK = heroBlockFromIndex();
const cssBody = gitShow('template/css/lireate-six-services-photo-v4.css');
const jsBody = gitShow('template/js/lireate-six-services-photo-v4.js');
const findtzTopnavCss = gitShow('pages/services/proverka-tovarnogo-znaka/assets/findtz-topnav-center.css');
const findtzHeroWebp = fs.readFileSync(path.join(REPO, 'template/images/findtz/findtz-hero.webp'));
const findtzHeroJpg = fs.readFileSync(path.join(REPO, 'template/images/findtz/findtz-hero.jpg'));

function patchFindtzHtml(html) {
  let out = html.replace(/lireate-six-services-photo-v4\.css\?v=[^"]+/g, `lireate-six-services-photo-v4.css?v=${VER}`);
  out = out.replace(/lireate-six-services-photo-v4\.js\?v=[^"]+/g, `lireate-six-services-photo-v4.js?v=${VER}`);
  out = out.replace(/findtz-topnav-center\.css\?v=[^"]+/g, `findtz-topnav-center.css?v=${VER}`);
  if (!out.includes('lireate-findtz-topnav-center lx-photo-v4')) {
    out = out.replace(
      /class="([^"]*lireate-findtz-topnav-center)"/,
      'class="$1 lx-photo-v4"',
    );
  }
  if (!out.includes('findtz-topnav-center.css')) {
    out = out.replace(
      /<\/head>/i,
      `<link rel="stylesheet" href="/template/css/findtz-topnav-center.css?v=${VER}">\n</head>`,
    );
  }
  const heroRe = /<section class="lx-hero"[\s\S]*?<\/section>\s*(?:<div class="lx-glass[\s\S]*?<\/div>\s*)?/;
  if (heroRe.test(out)) {
    out = out.replace(heroRe, `${HERO_BLOCK}\n`);
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
    if (req.resourceType() === 'document' && (pathname === '/findtz' || pathname === '/findtz/')) {
      const resp = await route.fetch({ timeout: 90000 });
      const html = patchFindtzHtml(await resp.text());
      await route.fulfill({
        status: resp.status(),
        headers: resp.headers(),
        contentType: 'text/html; charset=utf-8',
        body: html,
      });
      return;
    }
    if (pathname === '/template/css/lireate-six-services-photo-v4.css') {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: cssBody,
      });
      return;
    }
    if (pathname === '/template/js/lireate-six-services-photo-v4.js') {
      await route.fulfill({
        status: 200,
        contentType: 'application/javascript; charset=utf-8',
        body: jsBody,
      });
      return;
    }
    if (pathname.includes('findtz-topnav-center.css')) {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: findtzTopnavCss,
      });
      return;
    }
    if (pathname.includes('/template/images/findtz/findtz-hero.webp')) {
      await route.fulfill({
        status: 200,
        contentType: 'image/webp',
        body: findtzHeroWebp,
      });
      return;
    }
    if (pathname.includes('/template/images/findtz/findtz-hero.jpg')) {
      await route.fulfill({
        status: 200,
        contentType: 'image/jpeg',
        body: findtzHeroJpg,
      });
      return;
    }
    await route.continue();
  });
}

async function metrics(page) {
  return page.evaluate(() => {
    const card = document.querySelector('.lx-photo-v4-expert-card');
    const img = document.querySelector('.lx-photo-v4-expert-media--findtz img');
    const kicker = document.querySelector('.lx-photo-v4-expert-caption-kicker');
    const lead = document.querySelector('.lx-photo-v4-expert-caption-lead');
    const r = card?.getBoundingClientRect();
    const nav = document.querySelector('.lx-nav-shell');
    const navPill = document.querySelector('.lx-nav-pill');
    const consultBtn = document.querySelector('.lx-nav-pill > .lx-btn');
    const navBottom = nav?.getBoundingClientRect().bottom ?? 0;
    const person = document.querySelectorAll('#leftSideBar img[src*="person/"]').length;
    const vw = window.innerWidth;
    const pill = navPill?.getBoundingClientRect();
    const btn = consultBtn?.getBoundingClientRect();
    const capBox = document.querySelector('.lx-photo-v4-expert-caption')?.getBoundingClientRect();
    const leadBox = lead?.getBoundingClientRect();
    const noCaptionOverlap =
      !capBox ||
      !leadBox ||
      leadBox.bottom <= capBox.bottom + 2;
    return {
      card: !!card,
      kicker: kicker?.textContent?.trim() || '',
      lead: lead?.textContent?.trim() || '',
      imgOk: !!(img && img.complete && img.naturalWidth > 0),
      imgVisible: !!(img && img.offsetParent !== null && img.getBoundingClientRect().height > 40),
      noElviraName: !document.body.innerText.includes('Габдрахманова'),
      noExpertName: !document.querySelector('.lx-photo-v4-expert-name'),
      cardBelowNav: !!(r && r.top >= navBottom - 6),
      consultVisible: !!(btn && btn.right <= vw - 4 && btn.width > 40),
      navPillInView: !!(pill && pill.right <= vw + 2),
      noCaptionOverlap,
      sidebarPerson: person,
    };
  });
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
await setup(page);

await page.setViewportSize({ width: 1440, height: 900 });
await page.goto(`${HOST}/findtz/`, { waitUntil: 'load', timeout: 120000 });
await page.waitForTimeout(1200);
const m1440 = await metrics(page);
const f1440 = `${OUT}/findtz-1440-first-screen.png`;
await page.screenshot({ path: f1440 });
console.log(JSON.stringify({ file: f1440, vp: '1440', ...m1440 }));

await page.setViewportSize({ width: 390, height: 844 });
await page.goto(`${HOST}/findtz/`, { waitUntil: 'load', timeout: 120000 });
await page.waitForTimeout(900);
const m390top = await metrics(page);
const f390top = `${OUT}/findtz-390-first-screen.png`;
await page.screenshot({ path: f390top });
console.log(JSON.stringify({ file: f390top, vp: '390-top', ...m390top }));

const card = await page.$('.lx-photo-v4-expert-card');
if (card) {
  await card.scrollIntoViewIfNeeded();
  await page.waitForTimeout(400);
}
const m390photo = await metrics(page);
const f390photo = `${OUT}/findtz-390-to-photo.png`;
await page.screenshot({ path: f390photo, fullPage: false });
console.log(JSON.stringify({ file: f390photo, vp: '390-photo', ...m390photo }));

await browser.close();

let fail = 0;
const kickerOk = (t) => t.includes('LIREATE') && t.includes('ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ');
for (const m of [m1440, m390top, m390photo]) {
  if (
    !m.card ||
    !m.imgOk ||
    !m.imgVisible ||
    !kickerOk(m.kicker) ||
    !m.lead ||
    !m.noElviraName ||
    m.noExpertName === false ||
    !m.noCaptionOverlap
  ) {
    fail += 1;
  }
}
if (!m1440.cardBelowNav || !m1440.consultVisible || !m1440.navPillInView) fail += 1;
if (m1440.sidebarPerson > 0 || m390top.sidebarPerson > 0) fail += 1;

if (fail) {
  console.error('FAIL checks', fail);
  process.exitCode = 3;
} else {
  console.log('DONE', OUT);
}