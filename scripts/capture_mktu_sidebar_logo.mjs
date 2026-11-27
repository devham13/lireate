#!/usr/bin/env node
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const baseUrl = (process.argv[2] || (process.env.LIREATE_PUBLIC_SITE_URL ? `${process.env.LIREATE_PUBLIC_SITE_URL.replace(/\/$/, '')}/mktu` : '')).replace(/\/?$/, '');
if (!baseUrl) {
  console.error('Usage: node capture_mktu_sidebar_logo.mjs <url> [outDir]');
  process.exit(2);
}
const outDir = process.argv[3] || '/opt/cursor/artifacts';
const chrome = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';

fs.mkdirSync(outDir, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: chrome,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

async function waitSidebar(page) {
  await page.waitForSelector('#leftSideBar a.leftSideLogo', { timeout: 60000 });
  await page.waitForFunction(
    () => {
      const el = document.querySelector('#leftSideBar a.leftSideLogo');
      if (!el) return false;
      const s = getComputedStyle(el);
      return s.backgroundImage.includes('logo-light-v7') || s.maskImage === 'none';
    },
    { timeout: 30000 },
  );
  await new Promise((r) => setTimeout(r, 500));
}

async function clipLogo(page, file) {
  const box = await page.$eval('#leftSideBar', (bar) => {
    const r = bar.getBoundingClientRect();
    const w = Math.min(Math.max(r.width, 74), 292);
    return { x: Math.max(0, r.x), y: Math.max(0, r.y), width: w, height: 88 };
  });
  await page.screenshot({ path: path.join(outDir, file), clip: box });
}

for (const [w, h, tag] of [
  [1440, 900, '1440'],
  [390, 844, '390'],
]) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: h });
  await page.goto(baseUrl, { waitUntil: 'networkidle2', timeout: 120000 });

  if (w < 768) {
    const toggle = await page.$('.lsv7-mobile-toggle');
    if (toggle) {
      await toggle.click();
      await page.waitForSelector('body.lsv7-open', { timeout: 10000 }).catch(() => {});
    }
  } else {
    document_ready: await page.waitForFunction(
      () => document.body.classList.contains('lsv7-managed') || document.body.classList.contains('lsv7-sidebar-present'),
      { timeout: 15000 },
    ).catch(() => {});
  }

  await waitSidebar(page);
  await clipLogo(page, `mktu-sidebar-logo-expanded-${tag}.png`);

  if (w >= 768) {
    const btn = await page.$('#collapseSide');
    if (btn) {
      await btn.click();
    } else {
      await page.evaluate(() => document.body.classList.add('lsv7-collapsed'));
    }
    await new Promise((r) => setTimeout(r, 450));
    await clipLogo(page, `mktu-sidebar-logo-collapsed-${tag}.png`);
  } else {
    await page.evaluate(() => {
      document.querySelector('.lsv7-veil')?.click();
    });
    await new Promise((r) => setTimeout(r, 300));
    await page.screenshot({
      path: path.join(outDir, `mktu-sidebar-logo-collapsed-${tag}.png`),
      fullPage: false,
    });
  }

  await page.close();
}

await browser.close();
console.log('saved to', outDir);