import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/screenshots';
const BASE = (process.env.PUBLIC_SITE_URL || '').replace(/\/$/, '');
const htmlPath = process.env.HTML_PATH || '/opt/cursor/artifacts/patent-catalog-prod.html';
const html = readFileSync(htmlPath, 'utf8');

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

async function render(file, w, h) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: h });
  await page.setContent(html, { waitUntil: 'networkidle0', timeout: 120000, baseURL: `${BASE}/patentnye-zadachi/` });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => {
      el.classList.add('lx-reveal--visible');
      el.style.opacity = '1';
    });
  });
  await new Promise((r) => setTimeout(r, 1500));
  await page.screenshot({ path: file, fullPage: true });
  console.log('OK', file);
  await page.close();
}

await render(`${OUT}/patent-catalog-prod-desktop-1440-full.png`, 1440, 900);
await render(`${OUT}/patent-catalog-prod-mobile-390-full.png`, 390, 844);

const homeHtmlPath = process.env.HOME_HTML_PATH;
if (homeHtmlPath) {
  const home = readFileSync(homeHtmlPath, 'utf8');
  const mob = await browser.newPage();
  await mob.setViewport({ width: 390, height: 844 });
  await mob.setContent(home, { waitUntil: 'networkidle0', timeout: 120000, baseURL: `${BASE}/` });
  const btn = await mob.$('#leftSideNav, .js-openLeftSide, .menuButton, .header__burger, [class*="burger"]');
  if (btn) await btn.click().catch(() => {});
  await new Promise((r) => setTimeout(r, 600));
  await mob.screenshot({ path: `${OUT}/patent-menu-prod-mobile-390.png`, fullPage: false });
  console.log('OK', `${OUT}/patent-menu-prod-mobile-390.png`);
  await mob.close();
}

await browser.close();