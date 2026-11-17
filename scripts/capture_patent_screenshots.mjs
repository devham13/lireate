/**
 * Скриншоты раздела «Патентные задачи» (preview server scripts/lireate_patent_preview_server.sh).
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PATENT_PREVIEW_BASE || 'http://127.0.0.1:8891';
const OUT = '/opt/cursor/artifacts/screenshots';

const routes = [
  { path: '/patentnye-zadachi/', key: 'patent-catalog' },
  { path: '/patentnye-zadachi/poshliny-registraciya/', key: 'patent-registraciya' },
  { path: '/patentnye-zadachi/poshliny-prodlenie/', key: 'patent-prodlenie' },
  { path: '/patentnye-zadachi/redaktor-mktu/', key: 'patent-mktu' },
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 8000) throw new Error(`${path} too small (${buf.length} bytes)`);
}

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

async function revealAll(page) {
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => {
      el.classList.add('lx-reveal--visible', 'is-visible', 'lx-reveal-visible');
      el.style.opacity = '1';
      el.style.transform = 'none';
    });
  });
}

async function shot(page, url, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await revealAll(page);
  await new Promise((r) => setTimeout(r, 1200));
  await page.screenshot({ path: file, fullPage: true });
  assertPng(file);
  console.log('OK', file);
}

for (const r of routes) {
  const page = await browser.newPage();
  const url = BASE + r.path;
  await shot(page, url, `${OUT}/${r.key}-desktop-1440.png`, 1440, 900);
  await shot(page, url, `${OUT}/${r.key}-mobile-390.png`, 390, 844);
  await page.close();
}

await browser.close();