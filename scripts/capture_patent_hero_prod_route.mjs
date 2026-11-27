#!/usr/bin/env node
/** Prod /patent and /patent_prom_obr with route overrides for PR patent-data + patent-v4 hero images. */
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
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/patent-hero-v4-restore';
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

const dataPhp = gitShow('pages/patents/_shared/lireate-patent-data.php');
const patentBlock = dataPhp.slice(
  dataPhp.indexOf("if ($key === 'patent')"),
  dataPhp.indexOf("if ($key === 'patent_prom_obr')"),
);
const promBlock = dataPhp.slice(
  dataPhp.indexOf("if ($key === 'patent_prom_obr')"),
  dataPhp.indexOf('throw new InvalidArgumentException'),
);
function pick(block, key) {
  const m = block.match(new RegExp("'" + key + "'\\s*=>\\s*'([^']*)'"));
  return m ? m[1] : '';
}
const PATENT = {
  image: pick(patentBlock, 'image'),
  caption: pick(patentBlock, 'photo_caption'),
  alt: pick(patentBlock, 'photo_alt'),
};
const PROM = {
  image: pick(promBlock, 'image'),
  caption: pick(promBlock, 'photo_caption'),
  alt: pick(promBlock, 'photo_alt'),
};

const BIN_OVERRIDES = {
  '/template/images/patent-v4/patent-inventions.webp': 'template/images/patent-v4/patent-inventions.webp',
  '/template/images/patent-v4/patent-industrial-design.webp':
    'template/images/patent-v4/patent-industrial-design.webp',
};

async function setup(page) {
  await page.route('**/*', async (route) => {
    const req = route.request();
    const type = req.resourceType();
    let pathname = '';
    try {
      pathname = new URL(req.url()).pathname;
    } catch {
      await route.continue();
      return;
    }
    const binRel = BIN_OVERRIDES[pathname];
    if (binRel) {
      const body = fs.readFileSync(path.join(REPO, binRel));
      await route.fulfill({ status: 200, contentType: 'image/webp', body });
      return;
    }
    if (type === 'document' && (pathname === '/patent' || pathname === '/patent/' || pathname === '/patent_prom_obr' || pathname === '/patent_prom_obr/')) {
      const resp = await route.fetch({ timeout: 90000 });
      let html = await resp.text();
      const cfg = pathname.includes('prom_obr') ? PROM : PATENT;
      html = html.replace(/\/template\/images\/team\/person\/11\.webp/g, cfg.image);
      html = html.replace(/\/template\/images\/team\/person\/1\.webp/g, cfg.image);
      html = html.replace(/Горбачев Юрий Александрович — управляющий партнёр LIREATE/g, cfg.alt);
      html = html.replace(/Горбачев Юрий Александрович/g, cfg.caption);
      html = html.replace(/Габдрахманова Эльвира Дамировна — руководитель[^']*/g, cfg.alt);
      html = html.replace(/Габдрахманова Эльвира Дамировна/g, cfg.caption);
      if (!html.includes(cfg.image)) {
        html = html.replace(
          /(<div class="lp4-photo-frame"><img src=")([^"]+)(")/,
          `$1${cfg.image}$3`,
        );
      }
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
  { key: 'patent', path: '/patent/' },
  { key: 'patent_prom_obr', path: '/patent_prom_obr/' },
];
const viewports = [
  [1440, 900, '1440'],
  [390, 844, '390'],
];

const browser = await chromium.launch({ headless: true });
for (const p of pages) {
  const page = await browser.newPage();
  await setup(page);
  for (const [w, h, tag] of viewports) {
    await page.setViewportSize({ width: w, height: h });
    await page.goto(`${HOST}${p.path}`, { waitUntil: 'load', timeout: 120000 });
    await page.waitForTimeout(1500);
    const file = `${OUT}/${p.key}-${tag}-firstscreen.png`;
    await page.screenshot({ path: file });
    console.log('OK', file);
  }
  await page.close();
}
await browser.close();
console.log('PATENT_HERO', PATENT);
console.log('PROM_HERO', PROM);
console.log('DONE', OUT);