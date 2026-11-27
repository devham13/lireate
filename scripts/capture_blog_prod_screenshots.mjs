import puppeteer from 'puppeteer-core';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/screenshots';
const BASE = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!BASE) {
  throw new Error('Set LIREATE_PUBLIC_SITE_URL');
}
const PAGES = [
  { name: 'news-list', url: `${BASE}/news` },
  {
    name: 'article-kompensatsiya',
    url: `${BASE}/kompensatsiya_za_narushenie_tovarnogo_znaka_raschet_i_dokazatelstva/`,
  },
  {
    name: 'article-ekspertiza',
    url: `${BASE}/ekspertiza-tovarnyh-znakov-formalnaya-po-suschestvu/`,
  },
];

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});
const page = await browser.newPage();

for (const { name, url } of PAGES) {
  for (const vp of [
    { tag: 'desktop-1440', width: 1440, height: 900 },
    { tag: 'mobile-390', width: 390, height: 844 },
  ]) {
    await page.setViewport({ width: vp.width, height: vp.height });
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 120000 });
    await new Promise((r) => setTimeout(r, 2000));
    const path = `${OUT}/${name}-${vp.tag}.png`;
    await page.screenshot({ path, fullPage: true });
    console.log('OK', path);
  }
}

await browser.close();
console.log('done');