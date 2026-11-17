/**
 * Prod screenshots: patent catalog desktop + mobile (optional open nav).
 */
import puppeteer from 'puppeteer-core';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = (process.env.PUBLIC_SITE_URL || process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
const OUT = '/opt/cursor/artifacts/screenshots';
const NC = Date.now();

if (!BASE) {
  console.error('PUBLIC_SITE_URL required');
  process.exit(2);
}

const url = `${BASE}/patentnye-zadachi/?nc=${NC}`;

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const shot = async (page, file, w, h) => {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => {
      el.classList.add('lx-reveal--visible');
      el.style.opacity = '1';
    });
  });
  await new Promise((r) => setTimeout(r, 1200));
  await page.screenshot({ path: file, fullPage: true });
  console.log('OK', file);
};

const desk = await browser.newPage();
await shot(desk, `${OUT}/patent-catalog-prod-desktop-1440-full.png`, 1440, 900);
await desk.close();

const mob = await browser.newPage();
await mob.setViewport({ width: 390, height: 844 });
await mob.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });
const burger = await mob.$('.menuButton, .js-open-menu, .headerMenu, #menuButton, .mobileMenuButton');
if (burger) {
  await burger.click();
  await new Promise((r) => setTimeout(r, 800));
}
await mob.screenshot({ path: `${OUT}/patent-catalog-prod-mobile-390-full.png`, fullPage: true });
console.log('OK', `${OUT}/patent-catalog-prod-mobile-390-full.png`);
await mob.close();

await browser.close();