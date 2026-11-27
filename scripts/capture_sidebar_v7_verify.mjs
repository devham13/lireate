#!/usr/bin/env node
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const base = (process.argv[2] || process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
if (!base) {
  console.error('Usage: node capture_sidebar_v7_verify.mjs <baseUrl> [outDir]');
  process.exit(2);
}
const outDir = process.argv[3] || '/opt/cursor/artifacts';
const chrome = process.env.CHROME_PATH || '/usr/local/bin/google-chrome';
const paths = ['/', '/registraciya_tovarnogo_znaka/', '/patent', '/mktu', '/findtz/', '/patent_prom_obr'];

fs.mkdirSync(outDir, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: chrome,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const report = [];

for (const p of paths) {
  for (const [w, h, tag] of [
    [1440, 900, '1440'],
    [390, 844, '390'],
  ]) {
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: h });
    const url = base + p;
    try {
      await page.goto(url, { waitUntil: 'networkidle2', timeout: 120000 });
      await new Promise((r) => setTimeout(r, 3500));
      if (w < 768) {
        const toggle = await page.$('.lsv7-mobile-toggle');
        if (toggle) await toggle.click();
        await new Promise((r) => setTimeout(r, 600));
      }
      const slug = p.replace(/\//g, '_').replace(/^_|_$/g, '') || 'home';
      const checks = await page.evaluate(() => ({
        breadcrumbDropdown: !!document.querySelector('.lx-breadcrumb-services-trigger'),
        sidebarExpertAbsent: !document.querySelector('.lsv7-sidebar-expert'),
        lsv7: document.body.classList.contains('lsv7-managed'),
      }));
      const box = await page.$eval('#leftSideBar', (el) => {
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, width: Math.min(292, r.width), height: Math.min(360, r.height) };
      });
      const file = `sidebar-v7-${slug}-${tag}.png`;
      await page.screenshot({ path: path.join(outDir, file), clip: box });
      report.push({ url, tag, file, ...checks });
    } catch (e) {
      report.push({ url, tag, error: String(e) });
    }
    await page.close();
  }
}

await browser.close();
fs.writeFileSync(path.join(outDir, 'sidebar-v7-verify-report.json'), JSON.stringify(report, null, 2));
console.log(JSON.stringify(report, null, 2));