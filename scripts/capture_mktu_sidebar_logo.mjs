import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_MKTU_PREVIEW_BASE || 'http://127.0.0.1:8893';
const OUT = process.env.LIREATE_SCREENSHOT_OUT || '/opt/cursor/artifacts/screenshots/mktu-logo';

function assertPng(path) {
  if (readFileSync(path).length < 1500) throw new Error(`${path} too small`);
}

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const page = await browser.newPage();
for (const [w, h, tag] of [
  [1440, 900, '1440'],
  [390, 844, '390'],
]) {
  await page.setViewport({ width: w, height: h });
  await page.goto(`${BASE}/mktu/`, { waitUntil: 'networkidle2', timeout: 60000 });
  await new Promise((r) => setTimeout(r, 2000));
  const head = await page.$('#leftSideBar .lpc-sidebar-head');
  const file = `${OUT}/mktu-sidebar-logo-${tag}.png`;
  if (head) {
    await head.screenshot({ path: file });
  } else {
    const bar = await page.$('#leftSideBar');
    if (bar) await bar.screenshot({ path: file });
  }
  assertPng(file);
  console.log('OK', file);
}

await browser.close();
