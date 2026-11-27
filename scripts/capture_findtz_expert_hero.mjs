/**
 * Findtz hero screenshots (1440 + 390) via lireate_service_preview_server.sh
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const OUT = process.env.LIREATE_SCREENSHOT_OUT || '/opt/cursor/artifacts/screenshots/findtz-expert';
const PATH = '/findtz/';

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 12000) throw new Error(`${path} too small (${buf.length} bytes)`);
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
  await new Promise((r) => setTimeout(r, 3200));
  const file = `${OUT}/findtz-hero-${tag}.png`;
  const hero = await page.$('.lx-hero');
  if (hero) {
    await hero.screenshot({ path: file });
  } else {
    await page.screenshot({ path: file, fullPage: false });
  }
  assertPng(file);
  console.log('OK', file);
}

await browser.close();