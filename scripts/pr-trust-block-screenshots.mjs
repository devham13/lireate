/**
 * PR #70 — блок #trust и таблица #pricing-brief (лендинг registraciya).
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/pr-trust-glue-fix';

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage']
});

const url = `${BASE}/index.php`;

async function shotSection(page, sectionId, filename) {
  await page.evaluate((id) => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ block: 'center' });
  }, sectionId);
  await new Promise((r) => setTimeout(r, 450));
  const el = await page.$(`#${sectionId}`);
  if (el) await el.screenshot({ path: path.join(OUT, filename) });
  console.log('wrote', filename);
}

for (const [w, tag] of [
  [1440, 'desktop-1440'],
  [390, 'mobile-390']
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: w < 500 ? 844 : 900 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await shotSection(page, 'trust', `trust-${tag}.png`);
  await shotSection(page, 'pricing-brief', `pricing-brief-${tag}.png`);
  await page.close();
}

await browser.close();
console.log('done', OUT);
