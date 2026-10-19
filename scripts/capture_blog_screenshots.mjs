import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_BLOG_PREVIEW_BASE || 'http://127.0.0.1:8890/blog_preview/';
const OUT = '/opt/cursor/artifacts/screenshots';

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 25000) throw new Error(`${path} too small (${buf.length} bytes)`);
  const text = buf.toString('latin1', 0, 400);
  if (text.includes('Directory listing')) throw new Error(`${path} is directory listing`);
}

await mkdir(OUT, { recursive: true });
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900 });
await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 60000 });
await new Promise((r) => setTimeout(r, 2500));
const desk = `${OUT}/blog-article-desktop.png`;
await page.screenshot({ path: desk, fullPage: true });
assertPng(desk);
console.log('OK', desk);

await page.setViewport({ width: 390, height: 844 });
await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 60000 });
await new Promise((r) => setTimeout(r, 2000));
const mob = `${OUT}/blog-article-mobile.png`;
await page.screenshot({ path: mob, fullPage: true });
assertPng(mob);
console.log('OK', mob);

await browser.close();
console.log('done');
