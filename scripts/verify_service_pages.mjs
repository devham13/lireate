import puppeteer from 'puppeteer-core';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = 'http://127.0.0.1:8888';
const pages = [
  { path: '/zashchita_avtorskih_prav/', slug: 'zashchita-avtorskih-prav', reviews: false, calc: true },
  { path: '/prodlenie_tovarnogo_znaka/', slug: 'prodlenie-tovarnogo-znaka', reviews: true, calc: true },
  { path: '/registraciya_tovarnogo_znaka_v_kitae/', slug: 'registraciya-tovarnogo-znaka-v-kitae', reviews: true, calc: false },
];

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

const issues = [];
const allow404 = [/\/template\/img\//, /og-default\.jpg/, /favicon\.ico/];

for (const p of pages) {
  const url = BASE + p.path;
  const page = await browser.newPage();
  const logs = [];
  const bad404 = [];
  page.on('pageerror', (e) => logs.push('pageerror: ' + e.message));
  page.on('console', (msg) => {
    if (msg.type() === 'error') logs.push('console: ' + msg.text());
  });
  page.on('response', (res) => {
    if (res.status() === 404) {
      const u = res.url();
      if (!allow404.some((re) => re.test(u))) bad404.push(u);
    }
  });
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await new Promise((r) => setTimeout(r, 3500));
  const hasMain = await page.$('.lireate-svc-page');
  if (!hasMain) issues.push(`${p.slug}: missing .lireate-svc-page`);
  const mascot = await page.$('.lx-mascot-host');
  if (!mascot) issues.push(`${p.slug}: mascot host missing (desktop)`);
  if (p.reviews) {
    const rev = await page.$('#reviews .lx-review-frame');
    if (!rev) issues.push(`${p.slug}: reviews strip empty`);
  }
  const footer = await page.$('.lx-site-footer');
  if (!footer) issues.push(`${p.slug}: footer missing`);
  const reqs = await page.$eval('.lx-site-footer', (el) => el.textContent.includes('1648040894'));
  if (!reqs) issues.push(`${p.slug}: footer requisites missing`);
  const email = await page.$eval('.lx-site-footer', (el) => el.textContent.includes('info@lireate.ru'));
  if (!email) issues.push(`${p.slug}: footer email missing`);
  if (p.calc) {
    const calc = await page.$('[data-lx-calc]');
    if (!calc) issues.push(`${p.slug}: calculator block missing`);
    else {
      const out = await page.$eval('[data-lx-calc-out-gov]', (el) => el.textContent);
      if (!out || !out.includes('₽')) issues.push(`${p.slug}: calculator output empty`);
    }
  }
  await page.setViewport({ width: 390, height: 844 });
  await page.goto(url, { waitUntil: 'domcontentloaded' });
  await new Promise((r) => setTimeout(r, 2000));
  const overlap = await page.evaluate(() => {
    const cta = document.querySelector('.lx-hero-cta');
    const dock = document.querySelector('.lx-dock');
    if (!cta || !dock) return false;
    const a = cta.getBoundingClientRect();
    const d = dock.getBoundingClientRect();
    return a.right > d.left && a.bottom > d.top && a.top < d.bottom;
  });
  if (overlap) issues.push(`${p.slug}: hero CTA overlaps dock on mobile`);
  if (bad404.length) issues.push(`${p.slug} unexpected 404: ` + bad404.slice(0, 5).join(', '));
  if (logs.filter((l) => !l.includes('404')).length)
    issues.push(`${p.slug} JS: ` + logs.filter((l) => !l.includes('404')).join(' | '));
  await page.close();
}

await browser.close();
if (issues.length) {
  console.log('ISSUES:');
  issues.forEach((i) => console.log('-', i));
  process.exit(1);
}
console.log('OK: all checks passed');
