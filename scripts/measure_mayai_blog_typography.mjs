import puppeteer from 'puppeteer';

const URL = 'https://mayai.ru/blog/';
const SELECTORS = [
  { key: 'hero_kicker', sel: 'header h1 + p, .blog-hero p, [class*="hero"] p, h1 ~ p' },
  { key: 'hero_h1', sel: 'h1' },
  { key: 'hero_lead', sel: 'h1 ~ p, .lead, [class*="subtitle"]' },
  { key: 'filter_chip', sel: 'a[class*="filter"], nav a, .tags a' },
  { key: 'featured_title', sel: '[class*="featured"] h2, [class*="featured"] h3, article h2' },
  { key: 'card_title', sel: 'article h2, [class*="card"] h2, [class*="Card"] h2' },
  { key: 'card_meta', sel: 'article time, article [class*="date"], [class*="card"] time' },
  { key: 'card_excerpt', sel: 'article p, [class*="card"] p' },
  { key: 'card_author', sel: '[class*="author"]' },
];

async function measure(page, width) {
  await page.setViewport({ width, height: 900, deviceScaleFactor: 1 });
  await page.goto(URL, { waitUntil: 'networkidle2', timeout: 120000 });
  const fonts = await page.evaluate(() =>
    [...document.querySelectorAll('link[rel="stylesheet"], link[href*="fonts"]')].map((l) => l.href)
  );
  const fontFaces = await page.evaluate(() =>
    [...document.fonts].map((f) => ({ family: f.family, weight: f.weight, style: f.style }))
  );
  const out = { width, fonts, fontFaces: fontFaces.slice(0, 40) };
  for (const { key, sel } of SELECTORS) {
    const data = await page.evaluate((selector) => {
      const nodes = [...document.querySelectorAll(selector)].filter((el) => {
        const r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0;
      });
      const el = nodes[0];
      if (!el) return null;
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return {
        text: (el.textContent || '').trim().slice(0, 80),
        fontFamily: cs.fontFamily,
        fontSize: cs.fontSize,
        fontWeight: cs.fontWeight,
        lineHeight: cs.lineHeight,
        letterSpacing: cs.letterSpacing,
        color: cs.color,
        width: r.width,
        height: r.height,
      };
    }, sel);
    out[key] = data;
  }
  return out;
}

const browser = await puppeteer.launch({
  headless: 'new',
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});
const page = await browser.newPage();
const desktop = await measure(page, 1280);
const mobile = await measure(page, 390);
await browser.close();
console.log(JSON.stringify({ desktop, mobile }, null, 2));
