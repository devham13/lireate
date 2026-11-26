#!/usr/bin/env node
/** Inject PR portfolio V5 CSS/JS into live site HTML and capture 1440/390. */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const REPO = path.resolve(__dirname, '..');
const CHROME = process.env.CHROME_BIN || '/usr/local/bin/google-chrome';
const OUT = process.env.OUT_DIR || '/opt/cursor/artifacts/portfolio-v5';
const siteHost = process.env.LIREATE_PUBLIC_SITE_HOST || process.env.PUBLIC_SITE_HOST;
if (!siteHost) {
  console.error('Set LIREATE_PUBLIC_SITE_HOST or PUBLIC_SITE_HOST');
  process.exit(1);
}
const ORIGIN = ('https://' + siteHost).replace(/\/$/, ''); // pragma: allowlist secret

const CSS = readFileSync(path.join(REPO, 'template/css/lireate-portfolio-v5.css'), 'utf8');
const JS = readFileSync(path.join(REPO, 'template/js/lireate-portfolio-v5.js'), 'utf8');

const PAGES = [
  { slug: 'portfolio', url: `${ORIGIN}/portfolio/`, name: 'portfolio-index' },
  { slug: 'bentax', url: `${ORIGIN}/portfolio/bentax/`, name: 'bentax' },
  { slug: 'svoya-kultura', url: `${ORIGIN}/portfolio/svoya-kultura/`, name: 'svoya-kultura' },
  { slug: 'doza', url: `${ORIGIN}/portfolio/doza/`, name: 'doza' },
  { slug: 'vf', url: `${ORIGIN}/portfolio/vf/`, name: 'vf' },
];

const WIDTHS = [
  { w: 1440, h: 900, tag: '1440' },
  { w: 390, h: 844, tag: '390' },
];

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

for (const pageDef of PAGES) {
  for (const vp of WIDTHS) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: vp.h });
    await page.goto(pageDef.url, { waitUntil: 'networkidle2', timeout: 120000 });
    await page.addStyleTag({ content: CSS });
    await page.addScriptTag({ content: JS });
    await new Promise((r) => setTimeout(r, 2200));
  const file = path.join(OUT, `${pageDef.name}-${vp.tag}.png`);
    await page.screenshot({ path: file, fullPage: true });
    console.log('OK', file);
    await page.close();
  }
}

await browser.close();