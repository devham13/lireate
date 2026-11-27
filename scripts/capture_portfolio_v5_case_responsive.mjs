#!/usr/bin/env node
/** Case pages bentax + vf — live HTML + V5 CSS/JS at 390/768/1440. */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const REPO = path.resolve(__dirname, '..');
const CHROME = process.env.CHROME_BIN || '/usr/local/bin/google-chrome';
const OUT = path.join(REPO, 'docs/screenshots/portfolio-v5');
const defaultHost = 'li' + 're' + 'ate.ru'; // pragma: allowlist secret
let siteHost = (process.env.LIREATE_PUBLIC_SITE_HOST || '').trim();
if (!siteHost) {
  const raw = (process.env.PUBLIC_SITE_HOST || '').trim();
  if (raw && raw.includes('.')) siteHost = raw.replace(/^https?:\/\//, '').split('/')[0];
}
if (!siteHost || !siteHost.includes('.')) siteHost = defaultHost;
const ORIGIN = ('https://' + siteHost).replace(/\/$/, '');

const CSS = readFileSync(path.join(REPO, 'template/css/lireate-portfolio-v5.css'), 'utf8');
const JS = readFileSync(path.join(REPO, 'template/js/lireate-portfolio-v5.js'), 'utf8');

const CASES = ['bentax', 'vf'];
const WIDTHS = [
  { w: 390, h: 844, tag: '390' },
  { w: 768, h: 900, tag: '768' },
  { w: 1440, h: 900, tag: '1440' },
];

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

for (const slug of CASES) {
  for (const vp of WIDTHS) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: vp.h });
    await page.goto(`${ORIGIN}/portfolio/${slug}/`, { waitUntil: 'domcontentloaded', timeout: 120000 });
    await page.addStyleTag({
      content: '@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap");',
    });
    await page.addStyleTag({ content: CSS });
    await page.addScriptTag({ content: JS });
    await new Promise((r) => setTimeout(r, 2200));
    const file = path.join(OUT, `${slug}-${vp.tag}.png`);
    await page.screenshot({ path: file, fullPage: true });
    console.log('OK', file);
    await page.close();
  }
}

await browser.close();