#!/usr/bin/env node
/** /findtz/ hero photo — prod HTML + route CSS/JS from branch. */
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
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/findtz-hero-photo';
const BRANCH = process.env.LIREATE_PR_BRANCH || 'HEAD';
const VER = '20261128-findtz2';

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

function patchFindtzHtml(html) {
  let out = html.replace(/lireate-six-services-photo-v4\.css\?v=[^"]+/g, `lireate-six-services-photo-v4.css?v=${VER}`);
  out = out.replace(/lireate-six-services-photo-v4\.js\?v=[^"]+/g, `lireate-six-services-photo-v4.js?v=${VER}`);
  if (!out.includes('lireate-findtz-topnav-center lx-photo-v4')) {
    out = out.replace(
      /class="([^"]*lireate-findtz-topnav-center)"/,
      'class="$1 lx-photo-v4"',
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
  await page.goto(`${HOST}/findtz/`, { waitUntil: 'load', timeout: 120000 });
  await page.waitForTimeout(1200);
  const metrics = await page.evaluate(() => {
    const card = document.querySelector('.lx-photo-v4-expert-card');
    const img = document.querySelector('.lx-photo-v4-expert-media img');
    const cap = document.querySelector('.lx-photo-v4-expert-caption strong');
    const r = card?.getBoundingClientRect();
    return {
      card: !!card,
      caption: cap?.textContent?.trim() || '',
      imgOk: !!(img && img.complete && img.naturalWidth > 0),
      inFirstScreen1440: !!(r && r.top < 700 && r.height > 80),
    };
  });
  const file = `${OUT}/findtz-${tag}-hero.png`;
  await page.screenshot({ path: file });
  console.log(JSON.stringify({ file, vp: tag, ...metrics }));
  if (!metrics.card || !metrics.imgOk || metrics.caption !== 'ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ') {
    console.error('FAIL hero photo checks', tag);
    process.exitCode = 3;
  }
}
await browser.close();
console.log('DONE', OUT);