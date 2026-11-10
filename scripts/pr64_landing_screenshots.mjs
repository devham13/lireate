/**
 * PR #64 — превью лендинга registraciya (локальный PHP + mock mail.php).
 * node scripts/pr64_landing_screenshots.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const CHROME = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const OUT = '/opt/cursor/artifacts/pr64-landing';

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage']
});

const url = `${BASE}/index.php`;

async function prepPage(page) {
  await page.setRequestInterception(true);
  page.on('request', (req) => {
    const u = req.url();
    if (u.includes('/mail.php') && req.method() === 'POST') {
      req.respond({ status: 200, contentType: 'application/json', body: '["success"]' });
      return;
    }
    req.continue();
  });
}

// Desktop 1440 — первый экран
{
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active')));
  await page.screenshot({ path: path.join(OUT, '01-hero-desktop-1440.png') });
  console.log('wrote 01-hero-desktop-1440.png');
  await page.close();
}

// Desktop 1440 — форма #contact
{
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.getElementById('contact')?.scrollIntoView({ block: 'start' });
  });
  await new Promise((r) => setTimeout(r, 400));
  const el = await page.$('#contact');
  if (el) await el.screenshot({ path: path.join(OUT, '02-form-desktop-1440.png') });
  console.log('wrote 02-form-desktop-1440.png');
  await page.close();
}

// Mobile 390 — hero
{
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active')));
  await page.screenshot({ path: path.join(OUT, '03-hero-mobile-390.png') });
  console.log('wrote 03-hero-mobile-390.png');
  await page.close();
}

// Mobile 390 — форма после #contact + нижняя панель
{
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.getElementById('contact')?.scrollIntoView({ block: 'start' });
  });
  await new Promise((r) => setTimeout(r, 400));
  const bar = await page.$('.lx-mobile-bar');
  if (bar) await bar.screenshot({ path: path.join(OUT, '04-mobile-bar-390.png') });
  const form = await page.$('#contact');
  if (form) await form.screenshot({ path: path.join(OUT, '05-form-mobile-390.png') });
  console.log('wrote 04-05 mobile form/bar');
  await page.close();
}

await browser.close();
console.log('done', OUT);
