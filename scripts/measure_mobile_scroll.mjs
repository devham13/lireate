/**
 * Mobile scroll metrics: scrollWidth, scrollX, main width, offenders.
 * Usage: node scripts/measure_mobile_scroll.mjs [baseUrl] [path ...]
 */
import puppeteer from 'puppeteer-core';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.argv[2] || 'http://127.0.0.1:8888';
const paths = process.argv.slice(3).length
  ? process.argv.slice(3)
  : ['/findtz/', '/registraciya_tovarnogo_znaka/', '/prodlenie_tovarnogo_znaka/'];

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

async function measure(page, url, mobile) {
  if (mobile) {
    await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  } else {
    await page.setViewport({ width: 1440, height: 900, isMobile: false });
  }
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 120000 });
  await new Promise((r) => setTimeout(r, 1500));
  return page.evaluate(async (mobile) => {
    if (mobile) {
      window.scrollTo(200, 0);
      await new Promise((r) => requestAnimationFrame(r));
    }
    const main = document.querySelector('main.lireate-svc-page, main');
    const offenders = [];
    const vw = window.innerWidth;
    document.querySelectorAll('*').forEach((el) => {
      const st = getComputedStyle(el);
      if (st.position === 'fixed' || st.position === 'sticky') return;
      const r = el.getBoundingClientRect();
      if (r.right > vw + 0.5 && r.width > 1) {
        offenders.push({
          tag: el.tagName,
          id: el.id || '',
          cls: (el.className && String(el.className).slice(0, 70)) || '',
          right: Math.round(r.right * 10) / 10,
          w: Math.round(r.width * 10) / 10,
        });
      }
    });
    offenders.sort((a, b) => b.right - a.right);
    const uniq = [];
    const seen = new Set();
    for (const o of offenders) {
      const k = `${o.tag}.${o.cls}`;
      if (seen.has(k)) continue;
      seen.add(k);
      uniq.push(o);
      if (uniq.length >= 12) break;
    }
    return {
      scrollWidth: document.documentElement.scrollWidth,
      bodyScrollWidth: document.body.scrollWidth,
      innerWidth: window.innerWidth,
      scrollX: window.scrollX,
      mainWidth: main ? Math.round(main.getBoundingClientRect().width * 10) / 10 : null,
      mainMaxWidth: main ? getComputedStyle(main).maxWidth : null,
      offenders: uniq,
    };
  }, mobile);
}

for (const p of paths) {
  const url = BASE.replace(/\/$/, '') + p;
  const page = await browser.newPage();
  const m = await measure(page, url, true);
  const d = await measure(page, url, false);
  console.log('\n===', p, '===');
  console.log('mobile', JSON.stringify(m, null, 2));
  console.log('desktop main', d.mainWidth, d.mainMaxWidth, 'docSW', d.scrollWidth);
  await page.close();
}

await browser.close();
