import puppeteer from 'puppeteer-core';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = 'http://127.0.0.1:8888/prodlenie_tovarnogo_znaka/';
const OUT = '/opt/cursor/artifacts/seo-p1-contacts';

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox'],
});
for (const [w, h, tag] of [
  [1440, 900, 'desktop-1440-footer'],
  [390, 844, 'mobile-390-footer'],
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: h, isMobile: w < 500, hasTouch: w < 500 });
  await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((e) => e.classList.add('lx-active'));
    document.querySelector('.lx-site-footer')?.scrollIntoView({ block: 'end' });
  });
  await new Promise((r) => setTimeout(r, 500));
  const file = `${OUT}/prodlenie-${tag}.png`;
  await page.screenshot({ path: file, fullPage: false });
  console.log('OK', file);
  await page.close();
}
await browser.close();