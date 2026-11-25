import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-core';

const OUT = path.resolve('docs/light-v4-screenshots');
const base = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:19080';
if (!process.env.LIREATE_PUBLIC_SITE_URL) {
  throw new Error('Set LIREATE_PUBLIC_SITE_URL for before/ live screenshots');
}
const live = process.env.LIREATE_PUBLIC_SITE_URL.replace(/\/$/, '');

const shots = [
  { name: 'before-home-desktop', url: live + '/', w: 1440, h: 900 },
  { name: 'before-home-mobile', url: live + '/', w: 390, h: 844 },
  { name: 'after-home-cms-desktop', url: base + '/home', w: 1440, h: 900, prep: true },
  { name: 'after-home-cms-mobile', url: base + '/home', w: 390, h: 844, prep: true },
  { name: 'after-home-cms-viewport-desktop', url: base + '/home', w: 1440, h: 900, prep: true, viewportOnly: true },
  { name: 'after-home-cms-viewport-mobile', url: base + '/home', w: 390, h: 844, prep: true, viewportOnly: true },
  { name: 'before-tm-desktop', url: live + '/registraciya_tovarnogo_znaka/', w: 1440, h: 900 },
  { name: 'before-tm-mobile', url: live + '/registraciya_tovarnogo_znaka/', w: 390, h: 844 },
  { name: 'after-tm-desktop', url: base + '/tm', w: 1440, h: 900, prep: true },
  { name: 'after-tm-mobile', url: base + '/tm', w: 390, h: 844, prep: true },
  { name: 'after-tm-viewport-mobile', url: base + '/tm', w: 390, h: 844, prep: true, viewportOnly: true },
  { name: 'after-service-desktop', url: base + '/service', w: 1440, h: 900, prep: true },
  { name: 'after-service-mobile', url: base + '/service', w: 390, h: 844, prep: true },
  { name: 'after-patent-desktop', url: base + '/patent', w: 1440, h: 900, prep: true },
  { name: 'after-patent-mobile', url: base + '/patent', w: 390, h: 844, prep: true },
  { name: 'before-news-desktop', url: live + '/news/', w: 1440, h: 900, optional: true },
  { name: 'after-blog-list-desktop', url: base + '/blog-list', w: 1440, h: 900, prep: true },
  { name: 'after-blog-list-mobile', url: base + '/blog-list', w: 390, h: 844, prep: true },
  { name: 'after-blog-article-desktop', url: base + '/blog-article', w: 1440, h: 900, prep: true },
  { name: 'after-blog-article-mobile', url: base + '/blog-article', w: 390, h: 844, prep: true },
  { name: 'before-team-desktop', url: live + '/team/', w: 1440, h: 900 },
  { name: 'after-team-desktop', url: base + '/team', w: 1440, h: 900, prep: true },
  { name: 'after-team-mobile', url: base + '/team', w: 390, h: 844, prep: true },
  { name: 'before-contacts-desktop', url: live + '/contacts/', w: 1440, h: 900 },
  { name: 'after-contacts-desktop', url: base + '/contacts', w: 1440, h: 900, prep: true },
  { name: 'after-contacts-mobile', url: base + '/contacts', w: 390, h: 844, prep: true },
  { name: 'after-about-desktop', url: base + '/about', w: 1440, h: 900, prep: true },
  { name: 'after-about-mobile', url: base + '/about', w: 390, h: 844, prep: true },
  { name: 'after-portfolio-desktop', url: base + '/portfolio', w: 1440, h: 900, prep: true },
  { name: 'after-portfolio-mobile', url: base + '/portfolio', w: 390, h: 844, prep: true },
  { name: 'after-reviews-desktop', url: base + '/reviews', w: 1440, h: 900, prep: true },
  { name: 'after-docs-desktop', url: base + '/docs', w: 1440, h: 900, prep: true },
  { name: 'after-partners-desktop', url: base + '/partners', w: 1440, h: 900, prep: true },
];

async function prepareForScreenshot(page) {
  await page.evaluate(async () => {
    document.body.classList.add('lireate-screenshot-capture');
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('visible'));
    const delay = (ms) => new Promise((r) => setTimeout(r, ms));
    let y = 0;
    const maxY = Math.max(
      document.body.scrollHeight,
      document.documentElement.scrollHeight
    );
    while (y < maxY) {
      y += Math.max(window.innerHeight * 0.85, 400);
      window.scrollTo(0, y);
      await delay(120);
    }
    window.scrollTo(0, maxY);
    await delay(350);
    window.scrollTo(0, 0);
    await delay(250);
  });
  await new Promise((r) => setTimeout(r, 500));
}

async function prepareViewportOnly(page) {
  await page.evaluate(async () => {
    document.body.classList.add('lireate-screenshot-capture');
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('visible'));
    window.scrollTo(0, 0);
  });
  await new Promise((r) => setTimeout(r, 450));
}

fs.mkdirSync(OUT, { recursive: true });
const browser = await puppeteer.launch({
  headless: true,
  executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/google-chrome-stable',
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

for (const s of shots) {
  const out = path.join(OUT, `${s.name}.png`);
  try {
    const page = await browser.newPage();
    await page.setViewport({ width: s.w, height: s.h, deviceScaleFactor: 1 });
    await page.goto(s.url, { waitUntil: 'networkidle2', timeout: 180000 });
    if (s.name.includes('blog-list')) {
      await page
        .waitForFunction(
          () =>
            document.querySelectorAll('.lireate-blog-list-cover-img').length >= 8 ||
            document.querySelectorAll(
              '.lireate-blog-list-card-cover[style*="background-image"]'
            ).length >= 8,
          { timeout: 45000 }
        )
        .catch(() => {});
    }
    if (s.prep) {
      if (s.viewportOnly) {
        await prepareViewportOnly(page);
      } else {
        await prepareForScreenshot(page);
      }
    }
    await page.evaluate(() => window.scrollTo(0, 0));
    await new Promise((r) => setTimeout(r, 200));
    await page.screenshot({ path: out, fullPage: !s.viewportOnly, type: 'png' });
    await page.close();
    console.log('OK', s.name, fs.statSync(out).size);
  } catch (e) {
    if (s.optional) console.log('SKIP', s.name);
    else console.error('FAIL', s.name, e.message);
  }
}
await browser.close();