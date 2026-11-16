import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import puppeteer from 'puppeteer-core';
import crypto from 'node:crypto';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const OUT = '/opt/cursor/artifacts/blog-pagination-seo';
const PORT = 8770;
const PHP = 'php';
const CHROME = '/usr/local/bin/google-chrome';

fs.mkdirSync(OUT, { recursive: true });

function renderHtml(pageNum) {
  const php = spawnSync(
    PHP,
    ['-d', 'display_errors=1', path.join(REPO, 'pages/blog/_preview/list-cms-shell.php')],
    {
      cwd: REPO,
      env: {
        ...process.env,
        LIREATE_BLOG_PREVIEW_DOCROOT: REPO,
        LIREATE_BLOG_PREVIEW_PAGE: String(pageNum),
        LIREATE_BLOG_PREVIEW_FORCE_PAGES: '3',
        LIREATE_BLOG_PREVIEW_USE_SHARED: '1',
        LIST_CSS_MODE: 'after',
      },
      encoding: 'utf-8',
    },
  );
  if (php.status !== 0) {
    console.error(php.stderr);
  }
  return php.stdout || php.stderr;
}

const server = http.createServer((req, res) => {
  const url = new URL(req.url, `http://127.0.0.1:${PORT}`);
  if (url.pathname === '/template/page/news/lireate-blog-list.css') {
    res.writeHead(200, { 'Content-Type': 'text/css' });
    res.end(fs.readFileSync(path.join(REPO, 'pages/blog/_shared/lireate-blog-list.css')));
    return;
  }
  if (url.pathname === '/template/page/news/lireate-blog-footer.css') {
    res.writeHead(200, { 'Content-Type': 'text/css' });
    res.end(fs.readFileSync(path.join(REPO, 'pages/blog/_shared/lireate-blog-footer.css')));
    return;
  }
  if (url.pathname.startsWith('/blog_preview/')) {
    const rel = url.pathname.replace('/blog_preview/', '');
    const file = path.join(REPO, 'pages/blog/_preview', rel);
    if (fs.existsSync(file) && fs.statSync(file).isFile()) {
      res.writeHead(200, { 'Content-Type': 'text/plain' });
      res.end(fs.readFileSync(file));
      return;
    }
  }
  if (url.pathname === '/news') {
    const page = url.searchParams.get('page') || '1';
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(renderHtml(page));
    return;
  }
  res.writeHead(404);
  res.end('not found');
});

await new Promise((resolve) => server.listen(PORT, '127.0.0.1', resolve));

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox'],
});

const meta = [];

async function capture(name, w, h, pagePath, scrollTo) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: h, isMobile: w < 500 });
  await page.goto(`http://127.0.0.1:${PORT}${pagePath}`, { waitUntil: 'networkidle2', timeout: 120000 });
  const info = await page.evaluate((scrollTo) => {
    const h1 = document.querySelector('.lireate-blog-list-h1-main');
    const sub = document.querySelector('.lireate-blog-list-h1-sub');
    const firstCard = document.querySelector('.lireate-blog-list-card-title, .lireate-blog-list-featured-title');
    const pagerLinks = [...document.querySelectorAll('.lireate-blog-list-page-link')].map((a) => a.getAttribute('href'));
    const el =
      scrollTo === 'pager'
        ? document.querySelector('.lireate-blog-list-pagination')
        : document.querySelector('.lireate-blog-list-hero');
    if (el) {
      el.scrollIntoView({ block: scrollTo === 'pager' ? 'center' : 'start' });
    }
    return {
      h1: h1 ? h1.textContent.trim() : '',
      hasSub: !!sub && sub.textContent.trim() !== '',
      firstCard: firstCard ? firstCard.textContent.trim() : '',
      pagerLinks,
    };
  }, scrollTo);
  await new Promise((r) => setTimeout(r, 400));
  const file = path.join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: false });
  const md5 = crypto.createHash('md5').update(fs.readFileSync(file)).digest('hex');
  console.log('OK', file, 'md5=' + md5, JSON.stringify(info));
  meta.push({ name, file, md5, ...info });
  await page.close();
}

await capture('news-page1-hero-desktop-1440', 1440, 900, '/news', 'hero');
await capture('news-page1-hero-mobile-390', 390, 844, '/news', 'hero');
await capture('news-page2-hero-desktop-1440', 1440, 900, '/news?page=2', 'hero');
await capture('news-page2-hero-mobile-390', 390, 844, '/news?page=2', 'hero');
await capture('news-page2-pager-desktop-1440', 1440, 900, '/news?page=2', 'pager');
await capture('news-page2-pager-mobile-390', 390, 844, '/news?page=2', 'pager');

fs.writeFileSync(path.join(OUT, 'capture-meta.json'), JSON.stringify(meta, null, 2));
const p1 = meta.find((m) => m.name.includes('page1-hero-desktop'));
const p2 = meta.find((m) => m.name.includes('page2-hero-desktop'));
if (p1 && p2 && p1.md5 === p2.md5) {
  console.error('FAIL: page1 and page2 hero screenshots are identical');
  process.exit(1);
}
const pager = meta.find((m) => m.name === 'news-page2-pager-desktop-1440');
if (pager && pager.pagerLinks.some((u) => u && u.indexOf('view=') !== -1)) {
  console.error('FAIL: pagination still contains view=');
  process.exit(1);
}
if (pager && !pager.pagerLinks.some((u) => u === '/news?page=3')) {
  console.error('FAIL: expected /news?page=N pagination hrefs', pager.pagerLinks);
  process.exit(1);
}

await browser.close();
server.close();