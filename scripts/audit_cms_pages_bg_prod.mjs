#!/usr/bin/env node
/** Quick prod audit: CMS routes body/.page background luminance. */
import { chromium } from 'playwright';

const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
const PATHS = [
  '/docs/',
  '/news/',
  '/team/',
  '/kontaktyi/',
  '/contacts/',
  '/o_kompanii/',
  '/partners/',
  '/portfolio/',
  '/video-otzyvy/',
  '/reviews/',
];

function isDark(rgb) {
  const m = rgb.match(/\d+/g);
  if (!m || m.length < 3) return null;
  const [r, g, b] = m.map(Number);
  return r + g + b < 140;
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
await page.setViewportSize({ width: 1440, height: 900 });
const rows = [];
for (const p of PATHS) {
  try {
    await page.goto(`${HOST}${p}`, { waitUntil: 'load', timeout: 60000 });
    await page.waitForTimeout(800);
    const row = await page.evaluate(() => {
      const pick =
        document.querySelector('.lireate-docs-page') ||
        document.querySelector('.lireate-team-page') ||
        document.querySelector('.lireate-about-page') ||
        document.querySelector('.lireate-contacts-page') ||
        document.querySelector('.page_container') ||
        document.body;
      const bg = getComputedStyle(pick).backgroundColor;
      return { bodyClass: document.body.className, bg };
    });
    rows.push({ path: p, dark: isDark(row.bg), ...row });
  } catch (e) {
    rows.push({ path: p, error: String(e) });
  }
}
await browser.close();
for (const r of rows) console.log(JSON.stringify(r));