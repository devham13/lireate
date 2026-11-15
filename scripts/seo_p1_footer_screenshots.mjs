import puppeteer from 'puppeteer-core';
import { mkdir, writeFile } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = 'http://127.0.0.1:8888/prodlenie_tovarnogo_znaka/';
const OUT = '/opt/cursor/artifacts/seo-p1-contacts';

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox'],
});

const meta = [];

for (const [w, h, tag] of [
  [1440, 900, 'desktop-1440-kontaktyi-card'],
  [390, 844, 'mobile-390-kontaktyi-card'],
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: h, isMobile: w < 500, hasTouch: w < 500 });
  await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((e) => e.classList.add('lx-active'));
  });
  const handle = await page.$('a.lx-related-card[href="/kontaktyi"]');
  if (!handle) throw new Error('Kontaktyi related card not found');
  await handle.evaluate((el) => el.scrollIntoView({ block: 'center', inline: 'center' }));
  await new Promise((r) => setTimeout(r, 400));
  const outerHTML = await handle.evaluate((el) => el.outerHTML);
  const file = `${OUT}/prodlenie-${tag}.png`;
  await handle.screenshot({ path: file });
  console.log('OK', file);
  console.log('outerHTML:', outerHTML);
  meta.push({ tag, file, outerHTML, viewport: `${w}x${h}` });
  await page.close();
}
await writeFile(`${OUT}/kontaktyi-related-meta.json`, JSON.stringify(meta, null, 2));
await browser.close();