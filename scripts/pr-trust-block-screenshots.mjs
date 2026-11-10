/**
 * Блок #trust «Опыт и факты» — превью лендинга registraciya.
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

for (const [w, name] of [
  [1440, 'trust-desktop-1440'],
  [390, 'trust-mobile-390']
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: w < 500 ? 844 : 900 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.getElementById('trust')?.scrollIntoView({ block: 'center' });
  });
  await new Promise((r) => setTimeout(r, 400));
  const el = await page.$('#trust');
  if (el) await el.screenshot({ path: path.join(OUT, `${name}.png`) });
  console.log('wrote', name);
  await page.close();
}

await browser.close();
console.log('done', OUT);
