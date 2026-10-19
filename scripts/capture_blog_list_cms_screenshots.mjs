import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import puppeteer from 'puppeteer';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const OUT = '/opt/cursor/artifacts/screenshots';
const PORT = 8765;
const PHP = 'php';

fs.mkdirSync(OUT, { recursive: true });
fs.mkdirSync(path.join(REPO, 'pages/blog/_preview/assets'), { recursive: true });

const beforeCss = path.join(REPO, 'pages/blog/_preview/assets/list-before.css');
if (!fs.existsSync(beforeCss)) {
  const r = spawnSync('git', ['show', 'fbee9f3:pages/blog/_shared/lireate-blog-list.css'], {
    cwd: REPO,
    encoding: 'utf-8',
  });
  if (r.status === 0) {
    fs.writeFileSync(beforeCss, r.stdout);
  }
}

const server = http.createServer((req, res) => {
  const url = new URL(req.url, `http://127.0.0.1:${PORT}`);
  if (url.pathname === '/template/page/news/lireate-blog-list.css') {
    const css = fs.readFileSync(path.join(REPO, 'pages/blog/_shared/lireate-blog-list.css'));
    res.writeHead(200, { 'Content-Type': 'text/css' });
    res.end(css);
    return;
  }
  if (url.pathname === '/template/page/news/lireate-blog-footer.css') {
    const css = fs.readFileSync(path.join(REPO, 'pages/blog/_shared/lireate-blog-footer.css'));
    res.writeHead(200, { 'Content-Type': 'text/css' });
    res.end(css);
    return;
  }
  if (url.pathname.startsWith('/blog_preview/')) {
    const rel = url.pathname.replace('/blog_preview/', '');
    const file = path.join(REPO, 'pages/blog/_preview', rel);
    if (fs.existsSync(file) && fs.statSync(file).isFile()) {
      const ext = path.extname(file);
      const type = ext === '.css' ? 'text/css' : ext === '.php' ? 'application/octet-stream' : 'text/plain';
      res.writeHead(200, { 'Content-Type': type });
      res.end(fs.readFileSync(file));
      return;
    }
  }
  if (url.pathname === '/' || url.pathname.startsWith('/list-cms-shell.php')) {
    const qs = url.search || '';
    const listCss = url.searchParams.get('list_css') || 'after';
    const php = spawnSync(
      PHP,
      ['-d', 'display_errors=1', path.join(REPO, 'pages/blog/_preview/list-cms-shell.php')],
      {
        cwd: REPO,
        env: {
          ...process.env,
          LIREATE_BLOG_PREVIEW_DOCROOT: REPO,
          LIST_CSS_MODE: listCss,
        },
        encoding: 'utf-8',
      }
    );
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(php.stdout || php.stderr);
    return;
  }
  res.writeHead(404);
  res.end('not found');
});

await new Promise((resolve) => server.listen(PORT, '127.0.0.1', resolve));

const browser = await puppeteer.launch({
  headless: 'new',
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});
const page = await browser.newPage();

async function shot(label, width, listCss) {
  await page.setViewport({ width, height: 900, deviceScaleFactor: 1 });
  await page.goto(`http://127.0.0.1:${PORT}/list-cms-shell.php?list_css=${listCss}`, {
    waitUntil: 'networkidle2',
    timeout: 120000,
  });
  const file = path.join(OUT, `blog-list-${listCss}-${label}.png`);
  await page.screenshot({ path: file, fullPage: true });
  return file;
}

const files = [];
for (const mode of ['before', 'after']) {
  files.push(await shot('desktop-1280', 1280, mode));
  files.push(await shot('mobile-390', 390, mode));
}

await browser.close();
server.close();
console.log(JSON.stringify({ screenshots: files }, null, 2));
