#!/usr/bin/env node
/**
 * Скриншоты страницы услуги после полной загрузки (reveal + меню «Услуги»).
 * Использование: node scripts/lireate_service_screenshot.mjs <baseUrl> <outDir>
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const baseUrl = (process.argv[2] || 'http://127.0.0.1:8888/registraciya_tovarnogo_znaka/').replace(/\/?$/, '/');
const outDir = process.argv[3] || '/opt/cursor/artifacts';
const chrome = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';

fs.mkdirSync(outDir, { recursive: true });

const viewports = [
  { w: 1440, h: 900, tag: 'desktop-1440' },
  { w: 768, h: 1024, tag: 'tablet-768' },
  { w: 390, h: 844, tag: 'mobile-390' },
  { w: 375, h: 812, tag: 'mobile-375' },
  { w: 320, h: 568, tag: 'mobile-320' },
];

const browser = await puppeteer.launch({
  executablePath: chrome,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
});

const consoleErrors = [];

async function snap(page, name) {
  await page.screenshot({ path: path.join(outDir, name), fullPage: false });
}

for (const vp of viewports) {
  const page = await browser.newPage();
  page.on('console', (msg) => {
    if (msg.type() === 'error') consoleErrors.push(`[${vp.tag}] ${msg.text()}`);
  });
  page.on('pageerror', (err) => consoleErrors.push(`[${vp.tag}] ${err.message}`));

  await page.setViewport({ width: vp.w, height: vp.h, deviceScaleFactor: 1 });
  await page.goto(baseUrl, { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => el.classList.add('lx-active'));
    document.documentElement.classList.add('lx-no-motion');
  });
  await new Promise((r) => setTimeout(r, 400));
  await snap(page, `registraciya-${vp.tag}-hero-form-top.png`);

  const contact = await page.$('#contact');
  if (contact) {
    await page.evaluate(() => {
      const c = document.getElementById('contact');
      if (c) c.scrollIntoView({ block: 'start' });
    });
    await new Promise((r) => setTimeout(r, 450));
    await snap(page, `registraciya-${vp.tag}-form-anchor.png`);
  }

  const trigger = await page.$('#lx-breadcrumb-services-trigger');
  if (trigger) {
    await trigger.click();
    await page.evaluate(() => {
      const menu = document.getElementById('lx-breadcrumb-services-menu');
      const btn = document.getElementById('lx-breadcrumb-services-trigger');
      if (menu) menu.removeAttribute('hidden');
      if (btn) btn.setAttribute('aria-expanded', 'true');
    });
    await new Promise((r) => setTimeout(r, 300));
    await snap(page, `registraciya-${vp.tag}-services-dropdown.png`);
  }

  const team = await page.$('#team');
  if (team) {
    await page.evaluate(() => {
      const t = document.getElementById('team');
      if (t) t.scrollIntoView({ block: 'start' });
    });
    await new Promise((r) => setTimeout(r, 500));
    await snap(page, `registraciya-${vp.tag}-team.png`);
  }
  await page.close();
}

await browser.close();

const reportPath = path.join(outDir, 'registraciya-screenshot-console.txt');
fs.writeFileSync(
  reportPath,
  consoleErrors.length ? consoleErrors.join('\n') : 'No console/page errors.',
  'utf8',
);
console.log('Wrote screenshots to', outDir);
if (consoleErrors.length) {
  console.error('Console errors:', consoleErrors.length);
  process.exitCode = 1;
}
