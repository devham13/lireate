/* One-off preview shots for PR QA — current live site (pre-apply) or post-apply.
 * BASE=[REDACTED] CHROME=/usr/local/bin/google-chrome node scripts/lireate_sitewide_sidebar_v7_preview_shots.mjs
 */
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE = (process.env.BASE || '[REDACTED]').replace(/\/+$/, '');
const CHROME = process.env.CHROME || '/usr/local/bin/google-chrome';
const OUT = process.env.LSV7_SHOTS_DIR || '/opt/cursor/artifacts';
const PAGES = [
  { slug: 'findtz', path: '/findtz/' },
  { slug: 'registraciya-tz', path: '/registraciya_tovarnogo_znaka/' },
];

fs.mkdirSync(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

for (const item of PAGES) {
  for (const spec of [
    { tag: 'desktop-1440', width: 1440, height: 900, openMenu: false },
    { tag: 'mobile-390', width: 390, height: 844, openMenu: false },
    { tag: 'mobile-390-menu-open', width: 390, height: 844, openMenu: true },
  ]) {
    const page = await browser.newPage();
    await page.setViewport({ width: spec.width, height: spec.height, deviceScaleFactor: 1 });
    await page.goto(BASE + item.path, { waitUntil: 'networkidle2', timeout: 45000 });
    if (spec.openMenu) {
      const clicked = await page.evaluate(() => {
        const btn = document.querySelector('#lsv7-mobile-toggle,.lxq-mobile-nav-toggle,#lireatePublicCmsBurger,.hamburger');
        if (!btn) return false;
        btn.click();
        return true;
      });
      if (clicked) await new Promise((r) => setTimeout(r, 400));
    }
    const file = path.join(OUT, `lsv7-${item.slug}-${spec.tag}.png`);
    await page.screenshot({ path: file, fullPage: false });
    console.log('saved', file);
    await page.close();
  }
}
await browser.close();