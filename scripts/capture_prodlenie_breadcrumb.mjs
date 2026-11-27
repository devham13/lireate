/**
 * Prodlenie breadcrumb strip (1440 + 390) via service preview :8888
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const OUT = process.env.LIREATE_SCREENSHOT_OUT || '/opt/cursor/artifacts/screenshots/breadcrumb';
const PATH = '/prodlenie_tovarnogo_znaka/';

function assertPng(path) {
  if (readFileSync(path).length < 8000) throw new Error(`${path} too small`);
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
  await page.goto(BASE + PATH, { waitUntil: 'networkidle2', timeout: 90000 });
  await new Promise((r) => setTimeout(r, 2500));
  const nav = await page.$('.lx-breadcrumbs');
  const file = `${OUT}/prodlenie-breadcrumb-${tag}.png`;
  if (nav) {
    await nav.screenshot({ path: file });
  } else {
    await page.screenshot({ path: file, clip: { x: 0, y: 0, width: w, height: 120 } });
  }
  assertPng(file);
  console.log('OK', file);
}

await browser.close();