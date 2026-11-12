/**
 * Full-page + crop screenshots: findtz + registraciya (live-align PR).
 * Reveals sections via stepped scroll (IntersectionObserver) + lx-active fallback in-page only.
 */
import puppeteer from 'puppeteer-core';
import { mkdir } from 'fs/promises';
import { readFileSync } from 'fs';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PREVIEW_BASE || 'http://127.0.0.1:8888';
const OUT = process.env.LIREATE_SCREENSHOT_OUT || '/opt/cursor/artifacts/live-align-pr75';

const routes = [
  { path: '/findtz/', tag: 'findtz' },
  { path: '/registraciya_tovarnogo_znaka/', tag: 'registraciya' },
];

function assertPng(path, minBytes = 20000) {
  const buf = readFileSync(path);
  if (buf.length < minBytes) throw new Error(`${path} too small (${buf.length} bytes)`);
}

async function steppedScrollReveal(page) {
  const metrics = await page.evaluate(async () => {
    const step = 600;
    const pause = 150;
    const maxY = Math.max(
      document.documentElement.scrollHeight - window.innerHeight,
      0,
    );
    for (let y = 0; y <= maxY; y += step) {
      window.scrollTo(0, y);
      await new Promise((r) => setTimeout(r, pause));
    }
    window.scrollTo(0, maxY);
    await new Promise((r) => setTimeout(r, pause * 2));
    for (let y = maxY; y >= 0; y -= step) {
      window.scrollTo(0, y);
      await new Promise((r) => setTimeout(r, pause));
    }
    window.scrollTo(0, 0);
    await new Promise((r) => setTimeout(r, pause));
    document.querySelectorAll('.lx-reveal:not(.lx-active)').forEach((el) => {
      el.classList.add('lx-active');
    });
    await Promise.race([
      Promise.all(
        Array.from(document.images).map((img) => {
          if (img.complete) return Promise.resolve();
          return new Promise((resolve) => {
            img.addEventListener('load', resolve, { once: true });
            img.addEventListener('error', resolve, { once: true });
          });
        }),
      ),
      new Promise((r) => setTimeout(r, 4000)),
    ]);
    await new Promise((r) => setTimeout(r, 300));
    const reveals = document.querySelectorAll('.lx-reveal');
    const active = document.querySelectorAll('.lx-reveal.lx-active');
    return {
      revealTotal: reveals.length,
      revealActive: active.length,
      scrollHeight: document.documentElement.scrollHeight,
    };
  });
  if (metrics.revealActive < metrics.revealTotal * 0.85) {
    throw new Error(
      `reveal not active: ${metrics.revealActive}/${metrics.revealTotal}`,
    );
  }
  return metrics;
}

async function assertLayout(page, isMobile) {
  await page.evaluate(() => window.scrollTo(200, 0));
  await new Promise((r) => setTimeout(r, 80));
  const metrics = await page.evaluate(() => {
    const main = document.querySelector('main.lireate-svc-page, main');
    return {
      scrollWidth: document.documentElement.scrollWidth,
      scrollX: window.scrollX,
      mainWidth: main ? main.getBoundingClientRect().width : null,
    };
  });
  if (isMobile && (metrics.scrollWidth !== 390 || metrics.scrollX !== 0)) {
    throw new Error(`mobile scrollWidth=${metrics.scrollWidth} scrollX=${metrics.scrollX}`);
  }
  if (!isMobile && metrics.mainWidth && Math.round(metrics.mainWidth) !== 1120) {
    throw new Error(`desktop mainWidth=${metrics.mainWidth}`);
  }
}

async function prepViewport(page, url, w, h, isMobile) {
  await page.setViewport({
    width: w,
    height: h,
    deviceScaleFactor: 1,
    isMobile: !!isMobile,
    hasTouch: !!isMobile,
  });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 120000 });
  await new Promise((r) => setTimeout(r, 1200));
  await steppedScrollReveal(page);
  await assertLayout(page, isMobile);
}

async function shotFull(page, file) {
  await page.evaluate(() => window.scrollTo(0, 0));
  await new Promise((r) => setTimeout(r, 200));
  await page.screenshot({ path: file, fullPage: true });
  assertPng(file, 80000);
  console.log('OK', file);
}

async function scrollToSelector(page, selector) {
  const handle = await page.$(selector);
  if (!handle) throw new Error(`missing selector ${selector}`);
  await handle.evaluate((el) => el.scrollIntoView({ block: 'start', behavior: 'instant' }));
  await new Promise((r) => setTimeout(r, 450));
}

async function shotViewport(page, selector, file, pad = 8, fullViewport = false) {
  await scrollToSelector(page, selector);
  const box = await page.evaluate((sel) => {
    const el = document.querySelector(sel);
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return { x: r.x, y: r.y, w: r.width, h: r.height };
  }, selector);
  if (!box || box.w < 10) throw new Error(`missing selector ${selector}`);
  if (fullViewport) {
    await page.screenshot({ path: file, fullPage: false });
  } else {
    const clip = {
      x: Math.max(0, Math.floor(box.x - pad)),
      y: Math.max(0, Math.floor(box.y - pad)),
      width: Math.min(
        await page.evaluate(() => window.innerWidth),
        Math.ceil(box.w + pad * 2),
      ),
      height: Math.min(
        await page.evaluate(() => window.innerHeight),
        Math.ceil(box.h + pad * 2),
      ),
    };
    await page.screenshot({ path: file, clip });
  }
  assertPng(file, 15000);
  console.log('OK', file);
}

async function shotViewportWhole(page, file) {
  await page.screenshot({ path: file, fullPage: false });
  assertPng(file, 15000);
  console.log('OK', file);
}

async function shotLocator(page, selector, file) {
  const handle = await page.waitForSelector(selector, { visible: true, timeout: 60000 });
  if (!handle) throw new Error(`locator missing: ${selector}`);
  await handle.evaluate((el) => el.scrollIntoView({ block: 'center', behavior: 'instant' }));
  await new Promise((r) => setTimeout(r, 450));
  await handle.screenshot({ path: file });
  await handle.dispose();
  assertPng(file, 12000);
  console.log('OK', file);
}

async function assertVkCasesStyled(page) {
  const ok = await page.evaluate(() => {
    const card = document.querySelector('#cases .lireate-vk-case-card');
    const img = document.querySelector('#cases .lireate-vk-case-img');
    if (!card || !img) return false;
    const cardBg = getComputedStyle(card).backgroundColor;
    const imgW = img.naturalWidth;
    const ph = card.querySelector('.lireate-vk-case-media--placeholder');
    return !ph && imgW > 50 && cardBg !== 'rgba(0, 0, 0, 0)';
  });
  if (!ok) throw new Error('VK cases not styled or images missing');
}

async function assertSections(page, tag) {
  const ok = await page.evaluate((t) => {
    const checks = {};
    if (t === 'findtz') {
      checks.risks = !!document.querySelector('#risks .lx-risk-grid .lx-risk-item');
      checks.riskCount = document.querySelectorAll('#risks .lx-risk-item').length;
      checks.vk = !!document.querySelector('#cases .lireate-vk-case-card');
      checks.reviews = !!document.querySelector('#reviews .lx-review-frame');
      checks.form = !!document.querySelector('form#lireate-lead-form[action="/mail.php"]');
      return (
        checks.risks &&
        checks.riskCount >= 4 &&
        checks.vk &&
        checks.reviews &&
        checks.form
      );
    }
    checks.reviews = !!document.querySelector('#reviews .lx-review-frame');
    checks.form = !!document.querySelector('form#lireate-lead-form[action="/mail.php"]');
    checks.vk = !!document.querySelector('#cases .lireate-vk-case-card');
    return checks.reviews && checks.form && checks.vk;
  }, tag);
  if (!ok) throw new Error(`section visibility check failed for ${tag}`);
}

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  protocolTimeout: 300000,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const written = [];

for (const r of routes) {
  const url = BASE.replace(/\/$/, '') + r.path;

  {
    const page = await browser.newPage();
    await prepViewport(page, url, 1440, 900, false);
    await assertSections(page, r.tag);
    const f = `${OUT}/${r.tag}-desktop-1440-full.png`;
    await shotFull(page, f);
    written.push(f);
    if (r.tag === 'findtz') {
      await assertVkCasesStyled(page);
      const c = `${OUT}/findtz-risks-1440.png`;
      await shotLocator(page, '#risks .lx-risk-grid', c);
      written.push(c);
      const vk = `${OUT}/findtz-cases-1440.png`;
      await shotLocator(page, '#cases .lireate-vk-cases-grid', vk);
      written.push(vk);
    }
    await page.close();
  }

  {
    const page = await browser.newPage();
    await prepViewport(page, url, 390, 844, true);
    await assertSections(page, r.tag);
    const f = `${OUT}/${r.tag}-mobile-390-full.png`;
    await shotFull(page, f);
    written.push(f);

    if (r.tag === 'findtz') {
      const r1 = `${OUT}/findtz-risks-390.png`;
      await shotViewport(page, '#risks', r1, 10);
      written.push(r1);
      const r2 = `${OUT}/findtz-form-390.png`;
      await shotViewport(page, '#contact form#lireate-lead-form, #contact', r2, 10, true);
      written.push(r2);
    }
    if (r.tag === 'registraciya') {
      await assertVkCasesStyled(page);
      const vk = `${OUT}/registraciya-cases-390.png`;
      await shotLocator(page, '#cases .lireate-vk-cases-grid', vk);
      written.push(vk);
      const r2 = `${OUT}/registraciya-form-390.png`;
      await shotViewport(page, '#contact form#lireate-lead-form, #contact', r2, 10, true);
      written.push(r2);
      await page.evaluate(() => window.scrollTo(0, 0));
      await new Promise((res) => setTimeout(res, 200));
      const fBar = `${OUT}/registraciya-mobilebar-390.png`;
      await shotViewportWhole(page, fBar);
      written.push(fBar);
    }
    await page.close();
  }
}

await browser.close();

console.log('done');
written.forEach((p) => console.log('WROTE', p));
