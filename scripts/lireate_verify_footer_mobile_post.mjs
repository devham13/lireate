#!/usr/bin/env node
import { chromium } from 'playwright';

const BASE = (process.env.LIREATE_VERIFY_BASE || process.env.LIREATE_PUBLIC_SITE_URL || '').replace(/\/$/, '');
const paths = ['/prodlenie_tovarnogo_znaka/', '/registraciya_tovarnogo_znaka_v_kitae/', '/patent'];
let fail = false;
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
await page.setViewportSize({ width: 390, height: 844 });
for (const p of paths) {
  await page.goto(`${BASE}${p}`, { waitUntil: 'load', timeout: 120000 });
  await page.waitForSelector('.lx-site-footer-copy', { timeout: 60000 });
  await page.locator('.lx-site-footer-copy').last().scrollIntoViewIfNeeded();
  await page.waitForTimeout(800);
  const row = await page.evaluate(() => {
    const sb = document.getElementById('leftSideBar');
    const menu = !!(sb && getComputedStyle(sb).display !== 'none');
    const person = document.querySelectorAll('#leftSideBar img[src*="person/"]').length;
    const copy = document.querySelector('.lx-site-footer-copy');
    const toggle = document.querySelector('.lsv7-mobile-toggle');
    if (!copy || !toggle) return { menu, person, ok: false, reason: 'missing' };
    const cr = copy.getBoundingClientRect();
    const tr = toggle.getBoundingClientRect();
    const ok = cr.bottom <= tr.top - 4 && cr.height > 0;
    return { menu, person, ok, gap: tr.top - cr.bottom };
  });
  const good = row.menu && row.person === 0 && row.ok;
  console.log(good ? 'OK' : 'FAIL', p, row);
  if (!good) fail = true;
}
await browser.close();
process.exit(fail ? 1 : 0);