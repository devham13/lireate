#!/usr/bin/env node
import { chromium } from 'playwright';

const BASE = (process.env.LIREATE_VERIFY_BASE || process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!BASE) {
  console.error('LIREATE_VERIFY_BASE required');
  process.exit(2);
}
const pages = [
  { path: '/patent', img: 'patent-inventions.webp' },
  { path: '/patent_prom_obr', img: 'patent-industrial-design.webp' },
];
const widths = [1440, 390];
let fail = false;
const browser = await chromium.launch({ headless: true });
for (const { path, img } of pages) {
  for (const w of widths) {
    const page = await browser.newPage();
    await page.setViewportSize({ width: w, height: 900 });
    const resp = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 120000 });
    await page.waitForTimeout(1200);
    const row = await page.evaluate((needle) => {
      const sb = document.getElementById('leftSideBar');
      const menu = !!(sb && getComputedStyle(sb).display !== 'none');
      const person = document.querySelectorAll('#leftSideBar img[src*="person/"]').length;
      const hero = document.querySelector(`img[src*="${needle}"]`);
      return { menu, person, hero: !!hero };
    }, img);
    const ok =
      resp?.status() === 200 &&
      row.menu &&
      row.person === 0 &&
      row.hero;
    console.log(ok ? 'OK' : 'FAIL', path, w, row, resp?.status());
    if (!ok) fail = true;
    await page.close();
  }
}
await browser.close();
process.exit(fail ? 1 : 0);