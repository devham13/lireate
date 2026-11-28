#!/usr/bin/env node
/** Post-restore screenshots: reg + prod hero photo, no visible crumbs. */
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!HOST) {
  console.error('LIREATE_PUBLIC_SITE_URL required');
  process.exit(2);
}
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/restore-reg-prod-photo';
fs.mkdirSync(OUT, { recursive: true });

const pages = [
  { slug: 'registraciya', path: '/registraciya_tovarnogo_znaka/', name: 'Квасова' },
  { slug: 'prodlenie', path: '/prodlenie_tovarnogo_znaka/', name: 'Ульданова' },
];
const widths = [
  [1440, 900],
  [390, 844],
];

const browser = await chromium.launch({ headless: true });
let fail = 0;

for (const p of pages) {
  for (const [w, h] of widths) {
    const page = await browser.newPage();
    await page.setViewportSize({ width: w, height: h });
    await page.goto(`${HOST}${p.path}`, { waitUntil: 'load', timeout: 120000 });
    await page.waitForTimeout(1400);
    const m = await page.evaluate((expectName) => {
      const card = document.querySelector('.lx-photo-v4-expert-card');
      const img = document.querySelector('.lx-photo-v4-expert-media img');
      const crumbs = [...document.querySelectorAll('.lx-breadcrumbs--in-page, nav.lx-breadcrumbs')].filter(
        (el) => el.getBoundingClientRect().height > 0,
      ).length;
      const sb = document.getElementById('leftSideBar');
      const person = document.querySelectorAll('#leftSideBar img[src*="person/"]').length;
      const text = document.body.innerText;
      return {
        card: !!card,
        imgOk: !!(img && img.complete && img.naturalWidth > 0),
        crumbs,
        sidebar: !!sb,
        person,
        hasName: text.includes(expectName),
        hasSixCss: !!document.querySelector('link[href*="lireate-six-services-photo-v4"]'),
      };
    }, p.name);
    const file = path.join(OUT, `${p.slug}-${w}-hero.png`);
    await page.screenshot({ path: file });
    console.log(JSON.stringify({ file, ...p, vp: w, ...m }));
    if (!m.card || !m.imgOk || m.crumbs > 0 || !m.hasName || m.person > 0) fail += 1;
    await page.close();
  }
}
await browser.close();
process.exit(fail ? 3 : 0);