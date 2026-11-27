/**
 * Local CSS preview screenshots (no PHP): /news, /contacts, prodlenie footer @390.
 */
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import { chromium } from 'playwright';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const OUT = '/cursor/stores/self/footer184-fix';
const PORT = 8784;

fs.mkdirSync(OUT, { recursive: true });
fs.mkdirSync('/opt/cursor/artifacts/footer184-fix', { recursive: true });

const cssFiles = {
  '/css/blog-list.css': 'pages/blog/_shared/lireate-blog-list.css',
  '/css/blog-light-v4.css': 'pages/blog/_shared/lireate-blog-light-v4.css',
  '/css/cms-pages.css': 'template/css/lireate-cms-pages-light-v4.css',
  '/css/sidebar-v7.css': 'template/css/lireate-sitewide-sidebar-v7.css',
  '/css/light-v4.css': 'pages/services/_shared/lireate-light-v4.css',
  '/css/contacts.css': 'pages/kontaktyi/_shared/lireate-contacts.css',
};

const server = http.createServer((req, res) => {
  const p = cssFiles[req.url?.split('?')[0] || ''];
  if (!p) {
    res.writeHead(404);
    res.end('');
    return;
  }
  res.writeHead(200, { 'Content-Type': 'text/css' });
  res.end(fs.readFileSync(path.join(REPO, p)));
});

const footerHtml = `
<footer class="lx-site-footer" role="contentinfo">
  <div class="lx-site-footer-inner">
    <p class="lx-site-footer-title">LIREATE • Товарные знаки</p>
    <p class="lx-site-footer-lead">Регистрация и защита товарных знаков.</p>
    <p class="lx-site-footer-copy">© 2026 LIREATE</p>
  </div>
</footer>`;

const BASE_TOKEN = '__BASE__';
const blogShell = (mobile) => `<!DOCTYPE html>
<html lang="ru" class="lireate-blog-html">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="${BASE_TOKEN}/css/blog-list.css">
<link rel="stylesheet" href="${BASE_TOKEN}/css/blog-light-v4.css">
<link rel="stylesheet" href="${BASE_TOKEN}/css/cms-pages.css">
<link rel="stylesheet" href="${BASE_TOKEN}/css/sidebar-v7.css">
<style>#leftSideBar{position:fixed;left:0;top:0;width:270px;height:100vh;background:#262c40;z-index:99}${mobile ? '#leftSideBar{display:none}' : ''}</style>
</head>
<body class="cms-default lireate-blog-body lireate-theme-light-v4${mobile ? ' lsv7-managed lireate-svc-body' : ''}">
${mobile ? '<button type="button" class="lsv7-mobile-toggle" aria-label="Меню">☰ Меню</button>' : '<div id="leftSideBar" aria-hidden="true"></div>'}
<div class="wrapper"><div class="container content"><section id="blog">
<div class="lireate-blog-list-page">
  <header class="lireate-blog-list-hero"><div class="lireate-blog-list-wrap">
    <p class="lireate-blog-list-kicker">Блог LIREATE</p>
    <h1 class="lireate-blog-list-h1"><span class="lireate-blog-list-h1-main">Новости и материалы</span></h1>
  </div></header>
  <div class="lireate-blog-list-grid"><article class="lireate-blog-list-card"><p class="lireate-blog-list-card-title">Пример статьи</p></article></div>
</div>
${footerHtml}
</section></div></div>
</body></html>`;

const contactsShell = (base) => `<!DOCTYPE html>
<html lang="ru" class="lireate-contacts-html">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="${base}/css/contacts.css">
<link rel="stylesheet" href="${base}/css/cms-pages.css">
<link rel="stylesheet" href="${base}/css/sidebar-v7.css">
<style>#leftSideBar{position:fixed;left:0;top:0;width:270px;height:100vh;background:#262c40;z-index:99}</style>
</head>
<body class="lireate-contacts-body lireate-theme-light-v4">
<div id="leftSideBar" aria-hidden="true"></div>
<div class="wrapper"><div class="container content">
<section id="lireate-contacts"><div class="lireate-contacts-page">
  <header class="lireate-contacts-hero"><div class="lireate-contacts-wrap"><h1 class="lireate-contacts-h1">Контакты</h1></div></header>
</div></section>
${footerHtml}
</div></div>
</body></html>`;

const inlineSvcCss = [
  fs.readFileSync(path.join(REPO, 'pages/services/_shared/lireate-light-v4.css'), 'utf8'),
  fs.readFileSync(path.join(REPO, 'template/css/lireate-sitewide-sidebar-v7.css'), 'utf8'),
].join('\n');

const svcShell = () => `<!DOCTYPE html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<style>${inlineSvcCss.replace(/<\/style/gi, '<\\/style')}</style>
</head>
<body class="lsv7-managed lireate-svc-body lireate-theme-light-v4">
<button type="button" class="lsv7-mobile-toggle" aria-label="Меню">☰ Меню</button>
<main class="lireate-svc-page" style="min-height:70vh;padding:24px">Продление товарного знака</main>
${footerHtml}
</body></html>`;

await new Promise((r) => server.listen(PORT, '127.0.0.1', r));
const base = `http://127.0.0.1:${PORT}`;
const mkBlog = (m) => blogShell(m).replaceAll(BASE_TOKEN, base);

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();

await page.setViewportSize({ width: 1440, height: 900 });
await page.setContent(mkBlog(false), { waitUntil: 'networkidle' });
await page.screenshot({ path: `${OUT}/news-1440-top.png`, clip: { x: 0, y: 0, width: 1440, height: 520 } });
console.log('OK', `${OUT}/news-1440-top.png`);

await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
await page.screenshot({ path: `${OUT}/news-1440-bottom.png`, fullPage: true });
console.log('OK', `${OUT}/news-1440-bottom.png`);

const bodyBg = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
const gapBg = await page.evaluate(() => {
  const el = document.elementFromPoint(400, 400);
  return el ? getComputedStyle(el).backgroundColor : '';
});
fs.writeFileSync(`${OUT}/news-bg-probe.json`, JSON.stringify({ bodyBg, gapBg }, null, 2));

await page.setViewportSize({ width: 390, height: 844 });
await page.setContent(mkBlog(true), { waitUntil: 'networkidle' });
await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
const h = await page.evaluate(() => document.body.scrollHeight);
await page.screenshot({
  path: `${OUT}/news-390-bottom.png`,
  clip: { x: 0, y: Math.max(0, h - 420), width: 390, height: 420 },
});
console.log('OK', `${OUT}/news-390-bottom.png`);

await page.setViewportSize({ width: 390, height: 844 });
await page.setContent(svcShell(), { waitUntil: 'load' });
await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
const sh = await page.evaluate(() => document.body.scrollHeight);
await page.screenshot({
  path: `${OUT}/prodlenie-390-bottom.png`,
  clip: { x: 0, y: Math.max(0, sh - 440), width: 390, height: 440 },
});
console.log('OK', `${OUT}/prodlenie-390-bottom.png`);

await page.setViewportSize({ width: 1440, height: 900 });
await page.setContent(contactsShell(base), { waitUntil: 'networkidle' });
await page.screenshot({ path: `${OUT}/contacts-1440.png`, fullPage: true });
console.log('OK', `${OUT}/contacts-1440.png`);

for (const f of fs.readdirSync(OUT)) {
  if (f.endsWith('.png')) {
    fs.copyFileSync(`${OUT}/${f}`, `/opt/cursor/artifacts/footer184-fix/${f}`);
  }
}

await browser.close();
server.close();
console.log('DONE', OUT);