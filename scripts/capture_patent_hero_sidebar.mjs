/**
 * Patent/patent_prom_obr: hero + sidebar (1440/390) via lireate_patent_cms_preview_server.sh (8892).
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PATENT_PREVIEW_BASE || 'http://127.0.0.1:8892';
const OUT = process.env.LIREATE_SCREENSHOT_OUT || '/opt/cursor/artifacts/screenshots/patent-hero';

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 10000) throw new Error(`${path} too small`);
}

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const routes = [
  { path: '/patent/?page=patent', slug: 'patent' },
  { path: '/patent_prom_obr/?page=patent_prom_obr', slug: 'patent-prom-obr' },
];

for (const r of routes) {
  const page = await browser.newPage();
  for (const [w, h, tag] of [
    [1440, 900, '1440'],
    [390, 844, '390'],
  ]) {
    await page.setViewport({ width: w, height: h });
    await page.goto(BASE + r.path, { waitUntil: 'networkidle2', timeout: 90000 });
    await new Promise((t) => setTimeout(t, 2800));
    const hero = await page.$('#lireate-patent-v4 .lp4-hero');
    const sidebar = await page.$('#leftSideBar');
    const fileHero = `${OUT}/${r.slug}-hero-${tag}.png`;
    const fileSide = `${OUT}/${r.slug}-sidebar-${tag}.png`;
    if (hero) {
      await hero.screenshot({ path: fileHero });
      assertPng(fileHero);
      console.log('OK', fileHero);
    }
    if (sidebar) {
      await sidebar.screenshot({ path: fileSide });
      assertPng(fileSide);
      console.log('OK', fileSide);
    }
  }
  await page.close();
}

await browser.close();