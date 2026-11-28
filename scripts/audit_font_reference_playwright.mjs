#!/usr/bin/env node
/** Playwright/CDP font audit for a page — stdout JSON. */
import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const url = process.argv[2];
const label = process.argv[3] || url;
if (!url) {
  console.error('Usage: audit_font_reference_playwright.mjs <url> [label]');
  process.exit(2);
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
const fontResponses = [];
page.on('response', (resp) => {
  const u = resp.url();
  const ct = resp.headers()['content-type'] || '';
  if (/font|woff2?/i.test(u) || /font/i.test(ct)) {
    fontResponses.push({ url: u, status: resp.status(), contentType: ct });
  }
});
await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 120000 });
await page.waitForTimeout(4000);
await page.waitForTimeout(1500);

const report = await page.evaluate(() => {
  const sel = {
    body: document.body,
    p: document.querySelector('main p, .content p, article p, p'),
    h1: document.querySelector('h1'),
    h2: document.querySelector('h2'),
    button: document.querySelector('button, .btn, a.button, [class*="btn"]'),
  };
  const computed = {};
  for (const [k, el] of Object.entries(sel)) {
    if (!el) continue;
    const cs = getComputedStyle(el);
    computed[k] = {
      tag: el.tagName,
      fontFamily: cs.fontFamily,
      fontWeight: cs.fontWeight,
      fontSize: cs.fontSize,
      lineHeight: cs.lineHeight,
      letterSpacing: cs.letterSpacing,
    };
  }
  const faces = [];
  try {
    for (const f of document.fonts) {
      faces.push({
        family: f.family,
        weight: f.weight,
        style: f.style,
        status: f.status,
      });
    }
  } catch (e) {
    faces.push({ error: String(e) });
  }
  const atRules = [];
  for (const sheet of [...document.styleSheets]) {
    let rules;
    try {
      rules = sheet.cssRules;
    } catch {
      atRules.push({ href: sheet.href, blocked: true });
      continue;
    }
    if (!rules) continue;
    for (const rule of rules) {
      if (rule.type === CSSRule.FONT_FACE_RULE) {
        const st = rule.style;
        atRules.push({
          href: sheet.href,
          fontFamily: st.getPropertyValue('font-family'),
          fontWeight: st.getPropertyValue('font-weight'),
          fontStyle: st.getPropertyValue('font-style'),
          unicodeRange: st.getPropertyValue('unicode-range'),
          src: st.getPropertyValue('src'),
          fontDisplay: st.getPropertyValue('font-display'),
        });
      }
    }
  }
  const checks = {};
  const probe = 'АаБбЁё123';
  const fonts = new Set();
  for (const el of document.querySelectorAll('body, h1, h2, p, button, a')) {
    const ff = getComputedStyle(el).fontFamily;
    ff.split(',').forEach((x) => fonts.add(x.trim().replace(/^["']|["']$/g, '')));
  }
  for (const fam of fonts) {
    if (!fam || fam === 'inherit') continue;
    try {
      checks[fam] = document.fonts.check(`16px "${fam}"`, probe);
    } catch {
      checks[fam] = null;
    }
  }
  return { computed, faces, atRules, fontCheckCyrillic: checks };
});

const out = {
  label,
  url,
  fontNetwork: fontResponses,
  ...report,
};
const outPath =
  process.env.LIREATE_FONT_AUDIT_OUT ||
  `/cursor/stores/self/findtz-font-v1/audit-${label.replace(/[^a-z0-9]+/gi, '-').toLowerCase()}.json`;
fs.mkdirSync(path.dirname(outPath), { recursive: true });
fs.writeFileSync(outPath, JSON.stringify(out, null, 2));
console.log(JSON.stringify({ outPath, atRuleCount: report.atRules.length, fontUrls: fontResponses.length }));
await browser.close();