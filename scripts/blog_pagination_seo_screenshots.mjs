import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import puppeteer from 'puppeteer-core';

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
        LIREATE_BLOG_PREVIEW_FORCE_PAGES: '5',
        LIST_CSS_MODE: 'after',
      },
      encoding: 'utf-8',
    },
  );
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

const shots = [
  ['news-page1-desktop-1440', 1440, 900, '/news'],
  ['news-page1-mobile-390', 390, 844, '/news'],
  ['news-page2-desktop-1440', 1440, 900, '/news?page=2'],
  ['news-page2-mobile-390', 390, 844, '/news?page=2'],
];

for (const [name, w, h, p] of shots) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: h, isMobile: w < 500 });
  await page.goto(`http://127.0.0.1:${PORT}${p}`, { waitUntil: 'networkidle2', timeout: 120000 });
  const file = path.join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: true });
  console.log('OK', file);
  await page.close();
}

await browser.close();
server.close();