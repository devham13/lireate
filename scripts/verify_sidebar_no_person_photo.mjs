/**
 * Fail if #leftSideBar contains team person/* images (forbidden in left menu).
 * Usage: LIREATE_VERIFY_BASE=http://127.0.0.1:8888 node scripts/verify_sidebar_no_person_photo.mjs /findtz/
 */
import puppeteer from 'puppeteer-core';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = (process.env.LIREATE_VERIFY_BASE || 'http://127.0.0.1:8888').replace(/\/$/, '');
const paths = process.argv.slice(2).length
  ? process.argv.slice(2)
  : ['/findtz/', '/prodlenie_tovarnogo_znaka/'];

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

let failed = false;
for (const p of paths) {
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(`${BASE}${p}`, { waitUntil: 'networkidle2', timeout: 90000 });
  await new Promise((r) => setTimeout(r, 2500));
  const bad = await page.$$eval(
    '#leftSideBar img[src*="person/"], #leftSideBar .lsv7-sidebar-expert',
    (nodes) => nodes.map((n) => n.outerHTML.slice(0, 200)),
  );
  if (bad.length) {
    failed = true;
    console.error('FAIL', p, bad);
  } else {
    console.log('OK no person img in sidebar', p);
  }
  await page.close();
}

await browser.close();
if (failed) process.exit(1);