#!/usr/bin/env node
/** Prod /patent and /patent_prom_obr with route overrides for PR-changed patent data (HTML swap + CSS). */
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
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/patent-hero-elvira';
const BRANCH = process.env.LIREATE_PR_BRANCH || 'HEAD';

fs.mkdirSync(OUT, { recursive: true });

function gitShow(rel) {
  try {
    return execFileSync('git', ['show', `${BRANCH}:${rel}`], {
      cwd: REPO,
      encoding: 'utf-8',
    });
  } catch {
    return fs.readFileSync(path.join(REPO, rel), 'utf8');
  }
}

const dataPhp = gitShow('pages/patents/_shared/lireate-patent-data.php');
const imgM = dataPhp.match(/'image'\s*=>\s*'([^']+)'/);
const capM = dataPhp.match(/'photo_caption'\s*=>\s*'([^']+)'/);
const OLD_CAP = 'Горбачев Юрий Александрович';
const NEW_CAP = capM ? capM[1] : 'Габдрахманова Эльвира Дамировна';
const NEW_IMG = imgM ? imgM[1] : '/template/images/team/person/1.webp';

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
    if (pathname.endsWith('lireate-patent-light-v4.css')) {
      try {
        const body = gitShow('template/css/lireate-patent-light-v4.css');
        await route.fulfill({ status: 200, contentType: 'text/css', body });
        return;
      } catch {
        /* fallthrough */
      }
    }
    if (type === 'document' && (pathname === '/patent' || pathname === '/patent/' || pathname === '/patent_prom_obr' || pathname === '/patent_prom_obr/')) {
      const resp = await route.fetch({ timeout: 90000 });
      let html = await resp.text();
      html = html.replaceAll(OLD_CAP, NEW_CAP);
      html = html.replaceAll('/template/images/team/person/11.webp', NEW_IMG);
      html = html.replaceAll('/template/images/team/person/11.jpg', NEW_IMG.replace('.webp', '.jpg'));
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
console.log('HERO', NEW_CAP, NEW_IMG);
console.log('DONE', OUT);