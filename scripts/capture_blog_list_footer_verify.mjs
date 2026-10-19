import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import puppeteer from 'puppeteer';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const ARTIFACT_DIR = '/opt/cursor/artifacts/screenshots';
const PORT = 8767;
const PHP = 'php';
const VIEWPORTS = [
  { width: 1280, height: 900, name: '1280' },
  { width: 390, height: 844, name: '390' },
];

fs.mkdirSync(ARTIFACT_DIR, { recursive: true });

function readRepoCss(rel) {
  return fs.readFileSync(path.join(REPO, rel));
}

const server = http.createServer((req, res) => {
  const url = new URL(req.url, `http://127.0.0.1:${PORT}`);
  if (url.pathname === '/template/page/news/lireate-blog-list.css') {
    res.writeHead(200, { 'Content-Type': 'text/css' });
    res.end(readRepoCss('pages/blog/_shared/lireate-blog-list.css'));
    return;
  }
  if (url.pathname === '/template/page/news/lireate-blog-footer.css') {
    res.writeHead(200, { 'Content-Type': 'text/css' });
    res.end(readRepoCss('pages/blog/_shared/lireate-blog-footer.css'));
    return;
  }
  if (url.pathname === '/template/images/logo/logo.svg') {
    res.writeHead(200, { 'Content-Type': 'image/svg+xml' });
    res.end('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 40"><text x="0" y="28" fill="#93c5fd" font-size="20">LIREATE</text></svg>');
    return;
  }
  if (url.pathname.startsWith('/template/images/blog/')) {
    const rel = url.pathname.replace('/template/images/blog/', '');
    const local = path.join(REPO, 'template/images/blog', rel);
    if (fs.existsSync(local)) {
      res.writeHead(200, { 'Content-Type': 'image/jpeg' });
      res.end(fs.readFileSync(local));
      return;
    }
    res.writeHead(404);
    res.end('');
    return;
  }
  if (url.pathname === '/' || url.pathname.includes('list-cms-shell.php')) {
    const php = spawnSync(PHP, [path.join(REPO, 'pages/blog/_preview/list-cms-shell.php')], {
      cwd: REPO,
      env: {
        ...process.env,
        LIREATE_BLOG_PREVIEW_DOCROOT: REPO,
        LIST_CSS_MODE: 'after',
        LIREATE_BLOG_PREVIEW_FORCE_PAGES: url.searchParams.get('force_pages') || '21',
        LIREATE_BLOG_PREVIEW_PAGE: url.searchParams.get('page') || '2',
      },
      encoding: 'utf-8',
    });
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(php.stdout || php.stderr);
    return;
  }
  res.writeHead(404);
  res.end('not found');
});

function parseRgb(rgb) {
  const m = /rgba?\((\d+),\s*(\d+),\s*(\d+)/.exec(rgb || '');
  if (!m) return null;
  return { r: +m[1], g: +m[2], b: +m[3] };
}

function isDark(rgb) {
  if (!rgb) return false;
  const lum = 0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b;
  return lum < 40;
}

await new Promise((resolve) => server.listen(PORT, '127.0.0.1', resolve));
const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox', '--disable-setuid-sandbox'] });
const results = [];

for (const vp of VIEWPORTS) {
  const page = await browser.newPage();
  await page.setViewport({ width: vp.width, height: vp.height, deviceScaleFactor: 1 });
  await page.goto(`http://127.0.0.1:${PORT}/list-cms-shell.php?page=2&force_pages=21`, {
    waitUntil: 'networkidle2',
    timeout: 120000,
  });
  const metrics = await page.evaluate(() => {
    const sidebar = document.querySelector('#leftSideBar');
    const h1 = document.querySelector('.lireate-blog-list-h1-main');
    const sb = sidebar ? sidebar.getBoundingClientRect() : null;
    const hb = h1 ? h1.getBoundingClientRect() : null;
    const pagerLinks = document.querySelectorAll('.lireate-blog-list-page-link');
    const legacyFooter = document.querySelector('footer.footer');
    const lxFooter = document.querySelector('.lx-site-footer');
    const title = lxFooter ? lxFooter.querySelector('.lx-site-footer-title') : null;
    const legacyBox = legacyFooter ? legacyFooter.getBoundingClientRect() : null;
    const subscribeText = document.querySelector('.lireate-blog-subscribe-text');
    const st = subscribeText ? getComputedStyle(subscribeText).color : '';
    return {
      overlap: sb && hb ? hb.left < sb.right - 2 : false,
      pagerLinkCount: pagerLinks.length,
      legacyFooterHeight: legacyBox ? legacyBox.height : 0,
      legacyFooterDisplay: legacyFooter ? getComputedStyle(legacyFooter).display : '',
      footerTitle: title ? getComputedStyle(title).color : '',
      subscribeColor: st,
      docHeight: document.documentElement.scrollHeight,
    };
  });
  await page.evaluate(() => window.scrollTo(0, document.documentElement.scrollHeight));
  await new Promise((r) => setTimeout(r, 200));
  const shot = path.join(ARTIFACT_DIR, `blog-list-footer-${vp.name}.png`);
  await page.screenshot({ path: shot, fullPage: true });
  const bottomProbe = await page.evaluate(() => {
    const footer = document.querySelector('.lx-site-footer');
    if (footer) {
      const bg = getComputedStyle(footer).backgroundColor;
      const rect = footer.getBoundingClientRect();
      const atBottom = rect.bottom >= window.innerHeight - 2 || rect.bottom >= document.documentElement.scrollHeight - 2;
      return { tag: footer.tagName, className: footer.className, bg, atBottom };
    }
    const y = Math.max(0, document.documentElement.scrollHeight - 8);
    window.scrollTo(0, y);
    const x = Math.floor(window.innerWidth / 2);
    const el = document.elementFromPoint(x, window.innerHeight - 2);
    if (!el) return { tag: '', bg: getComputedStyle(document.body).backgroundColor };
    return { tag: el.tagName, className: el.className, bg: getComputedStyle(el).backgroundColor };
  });
  metrics.bottomBg = bottomProbe.bg;
  metrics.bottomDark = isDark(parseRgb(bottomProbe.bg));
  metrics.screenshot = shot;
  results.push({ viewport: vp.name, metrics });
  await page.close();
}

await browser.close();
server.close();

console.log(JSON.stringify({ results }, null, 2));

let failed = false;
for (const row of results) {
  const m = row.metrics;
  if (m.overlap) failed = true;
  if (m.pagerLinkCount > 8) failed = true;
  if (m.legacyFooterDisplay !== 'none') failed = true;
  if (!m.bottomDark) failed = true;
}
process.exit(failed ? 1 : 0);
