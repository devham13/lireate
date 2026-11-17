/**
 * Скриншоты раздела «Патентные задачи» — baseline + интерактивные состояния.
 */
import puppeteer from 'puppeteer-core';
import { readFileSync } from 'fs';
import { mkdir } from 'fs/promises';

const CHROME = '/usr/local/bin/google-chrome';
const BASE = process.env.LIREATE_PATENT_PREVIEW_BASE || 'http://127.0.0.1:8891';
const OUT = '/opt/cursor/artifacts/screenshots';

const PAGES = [
  { path: '/patentnye-zadachi/', key: 'patent-catalog' },
  { path: '/patentnye-zadachi/poshliny-registraciya/', key: 'patent-registraciya' },
  { path: '/patentnye-zadachi/poshliny-prodlenie/', key: 'patent-prodlenie' },
  { path: '/patentnye-zadachi/redaktor-mktu/', key: 'patent-mktu' },
];

function assertPng(path) {
  const buf = readFileSync(path);
  if (buf.length < 8000) throw new Error(`${path} too small (${buf.length} bytes)`);
}

async function revealAll(page) {
  await page.evaluate(() => {
    document.querySelectorAll('.lx-reveal').forEach((el) => {
      el.classList.add('lx-reveal--visible', 'is-visible', 'lx-reveal-visible');
      el.style.opacity = '1';
      el.style.transform = 'none';
    });
  });
}

async function shotFull(page, url, file, w, h) {
  await page.setViewport({ width: w, height: h });
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
  await revealAll(page);
  await new Promise((r) => setTimeout(r, 800));
  await page.screenshot({ path: file, fullPage: true });
  assertPng(file);
  console.log('OK', file);
}

await mkdir(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
});

for (const p of PAGES) {
  const page = await browser.newPage();
  const url = BASE + p.path;
  await shotFull(page, url, `${OUT}/${p.key}-desktop-1440-full.png`, 1440, 900);
  await shotFull(page, url, `${OUT}/${p.key}-mobile-390-full.png`, 390, 844);
  await page.close();
}

const regPage = await browser.newPage();
await regPage.setViewport({ width: 1440, height: 900 });
await regPage.goto(BASE + '/patentnye-zadachi/poshliny-registraciya/', { waitUntil: 'networkidle2' });
await regPage.click('[data-lx-patent-mode="list"]');
const mktuTwoClasses =
  '01\n' +
  'позиция1; позиция2; позиция3; позиция4; позиция5; позиция6; позиция7; позиция8; позиция9; позиция10; позиция11; позиция12\n' +
  '02\n' +
  'бета1; бета2; бета3; бета4; бета5; бета6; бета7; бета8';
await regPage.$eval('[data-lx-patent-mktu-in]', (el, val) => {
  el.value = val;
}, mktuTwoClasses);
await regPage.click('[data-lx-patent-calc]');
await regPage.waitForFunction(
  () => {
    const t = document.querySelector('[data-lx-patent-total]');
    return t && t.textContent && t.textContent.indexOf('39 500') >= 0;
  },
  { timeout: 10000 }
);
await revealAll(regPage);
await regPage.screenshot({ path: `${OUT}/patent-registraciya-result-2class-39500-desktop.png`, fullPage: true });
assertPng(`${OUT}/patent-registraciya-result-2class-39500-desktop.png`);
console.log('OK', `${OUT}/patent-registraciya-result-2class-39500-desktop.png`);

await regPage.click('[data-lx-patent-clear]');
await regPage.click('[data-lx-patent-mode="classes"]');
await regPage.click('[data-lx-patent-calc]');
await regPage.waitForFunction(
  () => {
    const e = document.querySelector('[data-lx-patent-error]');
    return e && !e.hasAttribute('hidden') && e.textContent.indexOf('0') < 0;
  },
  { timeout: 5000 }
);
await regPage.screenshot({ path: `${OUT}/patent-registraciya-empty-error-desktop.png`, fullPage: true });
assertPng(`${OUT}/patent-registraciya-empty-error-desktop.png`);
console.log('OK', `${OUT}/patent-registraciya-empty-error-desktop.png`);
await regPage.close();

const renewPage = await browser.newPage();
await renewPage.setViewport({ width: 1440, height: 900 });
await renewPage.goto(BASE + '/patentnye-zadachi/poshliny-prodlenie/', { waitUntil: 'networkidle2' });
await renewPage.$eval('[data-lx-patent-mktu-in]', (el) => {
  el.value = '01\nа;б;в;г;д;е;ж;з;и;к';
});
await renewPage.click('[data-lx-patent-extra-term]');
await renewPage.click('[data-lx-patent-calc]');
await renewPage.waitForFunction(
  () => {
    const t = document.querySelector('[data-lx-patent-total]');
    return t && t.textContent && t.textContent.indexOf('25 000') >= 0;
  },
  { timeout: 10000 }
);
await renewPage.screenshot({ path: `${OUT}/patent-prodlenie-result-extra-25000-desktop.png`, fullPage: true });
assertPng(`${OUT}/patent-prodlenie-result-extra-25000-desktop.png`);
console.log('OK', `${OUT}/patent-prodlenie-result-extra-25000-desktop.png`);
await renewPage.close();

const mktuPage = await browser.newPage();
await mktuPage.setViewport({ width: 1440, height: 900 });
await mktuPage.goto(BASE + '/patentnye-zadachi/redaktor-mktu/', { waitUntil: 'networkidle2' });
const sample =
  '09\n' +
  'программы для ЭВМ; мобильные приложения; SaaS-платформы\n' +
  '42\n' +
  'разработка ПО; консультации в области ИТ';
await mktuPage.$eval('[data-lx-mktu-source]', (el, val) => {
  el.value = val;
}, sample);
await mktuPage.click('[data-lx-mktu-run]');
await mktuPage.waitForFunction(
  () => document.querySelector('[data-lx-mktu-result]')?.value?.length > 10,
  { timeout: 5000 }
);
await mktuPage.screenshot({ path: `${OUT}/patent-mktu-parsed-desktop.png`, fullPage: true });
assertPng(`${OUT}/patent-mktu-parsed-desktop.png`);
console.log('OK', `${OUT}/patent-mktu-parsed-desktop.png`);

await Promise.all([
  mktuPage.click('[data-lx-mktu-to-calc]'),
  mktuPage.waitForNavigation({ waitUntil: 'networkidle2' }),
]);
await mktuPage.waitForFunction(
  () => document.querySelector('[data-lx-patent-class-table]')?.children?.length >= 2,
  { timeout: 8000 }
);
await mktuPage.click('[data-lx-patent-calc]');
await mktuPage.waitForFunction(
  () => {
    const t = document.querySelector('[data-lx-patent-total]');
    const text = t && t.textContent ? t.textContent.trim() : '';
    return text.length > 2 && text !== '—' && text !== '0 ₽' && /₽/.test(text);
  },
  { timeout: 10000 }
);
await mktuPage.screenshot({ path: `${OUT}/patent-registraciya-after-mktu-handoff-desktop.png`, fullPage: true });
assertPng(`${OUT}/patent-registraciya-after-mktu-handoff-desktop.png`);
console.log('OK', `${OUT}/patent-registraciya-after-mktu-handoff-desktop.png`);
await mktuPage.close();

await browser.close();