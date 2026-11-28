#!/usr/bin/env node
import fs from 'fs';
import path from 'path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
const OUT = process.env.LIREATE_OUT_DIR || '/cursor/stores/self/header-center-v2/compare';
const TOPNAV = fs.readFileSync(path.join(REPO, 'pages/services/_shared/lireate-svc-topnav-center.css'), 'utf8');
const PHOTO_CSS = fs.readFileSync(path.join(REPO, 'template/css/lireate-six-services-photo-v4.css'), 'utf8');
const PHOTO_JS = fs.readFileSync(path.join(REPO, 'template/js/lireate-six-services-photo-v4.js'), 'utf8');

async function capture(page, slug, inject, file) {
  await page.unroute('**/*');
  await page.route('**/*', async (route) => {
    const u = route.request().url();
    if (inject && u.includes('lireate-svc-topnav-center.css')) {
      return route.fulfill({ status: 200, contentType: 'text/css', body: TOPNAV });
    }
    if (inject && u.includes('lireate-six-services-photo-v4.css')) {
      return route.fulfill({ status: 200, contentType: 'text/css', body: PHOTO_CSS });
    }
    if (inject && u.includes('lireate-six-services-photo-v4.js')) {
      return route.fulfill({
        status: 200,
        contentType: 'application/javascript',
        body: PHOTO_JS,
      });
    }
    if (
      inject &&
      route.request().resourceType() === 'document' &&
      u.replace(/\/$/, '').endsWith(slug.replace(/\/$/, ''))
    ) {
      const r = await route.fetch();
      let html = await r.text();
      if (!html.includes('lireate-svc-topnav-center')) {
        html = html.replace(/(class="[^"]*)(lireate-theme-light-v4)/, '$1$2 lireate-svc-topnav-center');
      }
      return route.fulfill({ status: 200, contentType: 'text/html', body: html });
    }
    return route.continue();
  });
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${HOST}${slug}`, { waitUntil: 'domcontentloaded', timeout: 120000 });
  await page.waitForSelector('.lx-photo-v4-expert-stack', { timeout: 60000 });
  await page.waitForTimeout(inject ? 1500 : 800);
  const meta = await page.evaluate(() => {
    const stack = document.querySelector('.lx-photo-v4-expert-stack');
    const card = stack?.querySelector('.lx-photo-v4-expert-card');
    const next = stack?.querySelector('.lx-photo-v4-expert-next');
    const cap = stack?.querySelector('.lx-photo-v4-expert-caption');
    return {
      order: stack ? [...stack.children].map((c) => c.className.split(' ')[0]) : [],
      cardTop: card?.getBoundingClientRect().top,
      nextTop: next?.getBoundingClientRect().top,
      caption: cap ? getComputedStyle(cap).display : null,
    };
  });
  const box = await page.locator('.lx-photo-v4-expert-stack').boundingBox();
  await page.screenshot({ path: file, clip: box || undefined });
  return meta;
}

fs.mkdirSync(OUT, { recursive: true });
const browser = await chromium.launch();
const page = await browser.newPage();
let fail = 0;
for (const [slug, label] of [
  ['/registraciya_tovarnogo_znaka/', 'reg'],
  ['/prodlenie_tovarnogo_znaka/', 'prodlenie'],
]) {
  const prodMeta = await capture(page, slug, false, path.join(OUT, `${label}-expert-prod-1440.png`));
  const prevMeta = await capture(page, slug, true, path.join(OUT, `${label}-expert-preview-1440.png`));
  const layoutOk =
    prevMeta.cardTop < prevMeta.nextTop &&
    prevMeta.order[0] === 'lx-photo-v4-expert-card' &&
    (prevMeta.caption === 'none' || prevMeta.caption === null);
  console.log(JSON.stringify({ label, prodMeta, prevMeta, layoutOk }));
  if (!layoutOk) fail += 1;
}
await browser.close();
process.exit(fail ? 2 : 0);