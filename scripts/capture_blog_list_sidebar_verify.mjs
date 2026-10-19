import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import puppeteer from 'puppeteer';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const OUT = '/opt/cursor/artifacts/screenshots/blog-list-sidebar-1280.png';
const PORT = 8766;
const PHP = 'php';

fs.mkdirSync(path.dirname(OUT), { recursive: true });

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
    const listCss = url.searchParams.get('list_css') || 'after';
    const php = spawnSync(PHP, [path.join(REPO, 'pages/blog/_preview/list-cms-shell.php')], {
      cwd: REPO,
      env: { ...process.env, LIREATE_BLOG_PREVIEW_DOCROOT: REPO, LIST_CSS_MODE: listCss },
      encoding: 'utf-8',
    });
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(php.stdout || php.stderr);
    return;
  }
  res.writeHead(404);
  res.end('not found');
});

await new Promise((resolve) => server.listen(PORT, '127.0.0.1', resolve));
const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox', '--disable-setuid-sandbox'] });
const page = await browser.newPage();
await page.setViewport({ width: 1280, height: 900, deviceScaleFactor: 1 });
await page.goto(`http://127.0.0.1:${PORT}/list-cms-shell.php?list_css=after`, {
  waitUntil: 'networkidle2',
  timeout: 120000,
});
const metrics = await page.evaluate(() => {
  const sidebar = document.querySelector('#leftSideBar');
  const h1 = document.querySelector('.lireate-blog-list-h1-main');
  const covers = [...document.querySelectorAll('.lireate-blog-list-card-cover, .lireate-blog-list-featured-cover')];
  const sb = sidebar ? sidebar.getBoundingClientRect() : null;
  const hb = h1 ? h1.getBoundingClientRect() : null;
  let emptyCovers = 0;
  for (const el of covers) {
    const bg = getComputedStyle(el).backgroundImage;
    if (!bg || bg === 'none') emptyCovers += 1;
  }
  return {
    sidebarRight: sb ? sb.right : 0,
    h1Left: hb ? hb.left : 0,
    overlap: sb && hb ? hb.left < sb.right - 2 : false,
    coverCount: covers.length,
    emptyCovers,
  };
});
await page.screenshot({ path: OUT, fullPage: true });
await browser.close();
server.close();
console.log(JSON.stringify({ screenshot: OUT, metrics }, null, 2));
if (metrics.overlap) process.exit(2);
if (metrics.emptyCovers > 0) process.exit(3);
