import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
const require = createRequire(new URL('../tools/seo/unlighthouse/package.json', import.meta.url));
const puppeteer = require('puppeteer-core');
const browser = await puppeteer.launch({ executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/google-chrome', args: ['--no-sandbox'] });
try {
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.setRequestInterception(true);
  page.on('request', async request => {
    if (request.url().includes('vk.com/js/api/openapi.js')) {
      await new Promise(resolve => setTimeout(resolve, 300));
      await request.respond({ contentType: 'text/javascript', body: 'window.VK = {init: function () {window.vkReady = true;}};' });
    } else await request.continue();
  });
  await page.goto('http://127.0.0.1:19003/template/main.php', { waitUntil: 'networkidle0' });
  assert.equal(await page.evaluate(() => window.parsedBeforeSDK), true, 'SDK must not block HTML parsing');
  assert.equal(await page.evaluate(() => window.vkReady), true, 'VK init must run after its SDK');
  assert.equal(await page.$eval('#whatsapp_icon', node => node.getAttribute('aria-label')), 'Написать в WhatsApp');
  assert.equal(await page.evaluate(() => document.querySelectorAll('[role="main"]').length), 1);
  assert.equal(await page.evaluate(() => getComputedStyle(document.querySelector('.lx-site-footer-copy')).color), 'rgb(148, 163, 184)');
  await page.type('[name="phone"]', '+79991234567');
  await page.click('button[type="submit"]');
  assert.equal(await page.evaluate(() => window.formSubmitted), true, 'Existing submit handler must keep working');
  assert.equal(await page.evaluate(() => window.analyticsID), 'keep-existing-id');
  assert.deepEqual(errors, []);
  console.log('OK: deferred SDK ordering, accessible names, main landmark, contrast and form submit');
} finally {
  await browser.close();
}
