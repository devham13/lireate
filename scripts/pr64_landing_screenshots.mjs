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

// Mobile 390 — вьюпорт после #contact (форма + панель)
{
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.getElementById('contact')?.scrollIntoView({ block: 'start' });
  });
  await new Promise((r) => setTimeout(r, 500));
  await page.screenshot({ path: path.join(OUT, '04-mobile-viewport-390-contact.png'), fullPage: false });
  console.log('wrote 04-mobile-viewport-390-contact.png');
  await page.close();
}

// Mobile 320 — вьюпорт формы
{
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: 320, height: 700 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.getElementById('contact')?.scrollIntoView({ block: 'start' });
  });
  await new Promise((r) => setTimeout(r, 500));
  await page.screenshot({ path: path.join(OUT, '05-form-mobile-320-viewport.png'), fullPage: false });
  console.log('wrote 05-form-mobile-320-viewport.png');
  await page.close();
}

const widths = [320, 375, 390, 768, 1440];
for (const w of widths) {
  const page = await browser.newPage();
  await prepPage(page);
  await page.setViewport({ width: w, height: w < 500 ? 844 : 900 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active')));
  const dims = await page.evaluate(() => {
    const iw = window.innerWidth;
  const ih = window.innerHeight;
    let maxRight = 0;
    const visit = (el) => {
      if (!el || el.nodeType !== 1) return;
      const style = window.getComputedStyle(el);
      if (style.position === 'fixed' || style.display === 'none' || style.visibility === 'hidden') {
        for (const ch of el.children) visit(ch);
        return;
      }
      const rect = el.getBoundingClientRect();
      if (rect.width > 0 && rect.height > 0 && rect.top < ih && rect.bottom > 0) {
        if (rect.right > maxRight) maxRight = rect.right;
      }
      for (const ch of el.children) visit(ch);
    };
    visit(document.body);
    return {
      sw: document.documentElement.scrollWidth,
      cw: document.documentElement.clientWidth,
      iw,
      maxVisibleRight: Math.round(maxRight * 100) / 100
    };
  });
  const ok = dims.maxVisibleRight <= dims.iw + 0.5;
  console.log(
    `overflow-check ${w}: scrollWidth=${dims.sw} clientWidth=${dims.cw} maxVisibleRight=${dims.maxVisibleRight} innerWidth=${dims.iw} ok=${ok}`
  );
  if (!ok) {
    console.error(`HORIZONTAL OVERFLOW at ${w}px`);
    process.exitCode = 1;
  }
  await page.close();
}

await browser.close();
console.log('done', OUT);
