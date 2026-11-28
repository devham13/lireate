#!/usr/bin/env node
/**
 * Before/after screenshots for patent-v4 content centering on prod (page.route).
 * LIREATE_CAPTURE_MODE=before|after (after routes lireate-patent-light-v4.css from repo).
 */
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const _site = process.env.LIREATE_PUBLIC_SITE_URL;
if (!_site) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const HOST = _site.replace(/\/$/, '');
const MODE = (process.env.LIREATE_CAPTURE_MODE || 'after').toLowerCase();
const OUT =
  process.env.LIREATE_OUT_DIR ||
  `/cursor/stores/self/patent-v4-center/${MODE === 'before' ? 'before' : 'after'}`;

fs.mkdirSync(OUT, { recursive: true });

const CSS_PATH = path.join(REPO, 'template/css/lireate-patent-light-v4.css');
const CSS_BODY = fs.readFileSync(CSS_PATH, 'utf8');

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
    if (MODE === 'after' && pathname === '/template/css/lireate-patent-light-v4.css') {
      await route.fulfill({
        status: 200,
        contentType: 'text/css; charset=utf-8',
        body: CSS_BODY,
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
  [1100, 800, '1100'],
  [390, 844, '390'],
];

const browser = await chromium.launch({ headless: true });

for (const p of pages) {
  const page = await browser.newPage();
  await setup(page);
  for (const [w, h, tag] of viewports) {
    await page.setViewportSize({ width: w, height: h });
    await page.goto(`${HOST}${p.path}`, { waitUntil: 'load', timeout: 120000 });
    await page.waitForTimeout(1800);
  const metrics = await page.evaluate(() => {
      const root = document.querySelector('#lireate-patent-v4');
      const wrap = document.querySelector('#lireate-patent-v4 .lp4-wrap') || root;
      const sidebar = document.querySelector('#leftSideBar');
      const body = document.body;
      const vr = document.documentElement.getBoundingClientRect();
      const wr = wrap ? wrap.getBoundingClientRect() : null;
      const sr = sidebar ? sidebar.getBoundingClientRect() : null;
      return {
        lsv7: body.classList.contains('lsv7-managed'),
        patentBody: body.classList.contains('lireate-patent-light-v4'),
        sidebarVisible: !!sr && sr.width > 40 && sr.right > 0 && sr.left >= -5,
        menu: !!document.querySelector('#leftSideNav, .lsv7-sidebar, #leftSideBar'),
        wrapCenterDelta:
          wr && vr.width
            ? Math.round(wr.left + wr.width / 2 - (sr && sr.width > 40 ? sr.right : 0) - (vr.width - (sr && sr.width > 40 ? sr.width : 0)) / 2)
            : null,
      };
    });
    const file = `${OUT}/${p.key}-${tag}-top.png`;
    await page.screenshot({ path: file, fullPage: false });
    console.log(
      JSON.stringify({ file, page: p.key, vp: tag, mode: MODE, ...metrics }),
    );
    if (tag === '1440' && !metrics.menu) {
      console.error('FAIL: menu missing at 1440 for', p.key);
      process.exitCode = 3;
    }
  }
  await page.close();
}
await browser.close();
console.log('DONE', OUT, 'MODE', MODE);