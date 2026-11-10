/**
 * Лендинг: mail.php разбор (пустой 200), цели tz_* / zayavka — только мок, без POST на прод.
 * node scripts/test_registraciya_landing_submit.mjs
 */
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import vm from 'node:vm';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const mailJs = readFileSync(path.join(root, 'pages/services/_shared/lireate-lead-mail.js'), 'utf8');

function MockFormData() {
  this._m = new Map();
}
MockFormData.prototype.append = MockFormData.prototype.set = function (k, v) {
  this._m.set(k, String(v));
};
MockFormData.prototype.get = function (k) {
  return this._m.has(k) ? this._m.get(k) : null;
};

const mailCtx = { window: { lireateMailSpamGuard: 'x' }, FormData: MockFormData };
vm.runInNewContext(mailJs, mailCtx);
const mailApi = mailCtx.window.lireateLeadMail;

assert.equal(mailApi.parseMailPhpResponse('', 200).ok, true);

const landingFdMock = {
  querySelector: (sel) => {
    if (sel === 'input[name="mail_token"]') return { value: '503066424d5f7b5ad2097703ef3404d2' };
    if (sel === 'input[name="fuck_spamers"]') return { value: 'x' };
    return null;
  },
  querySelectorAll: (sel) => {
    if (sel === 'input[type="hidden"]') {
      return [
        { name: 'mail_token', value: '503066424d5f7b5ad2097703ef3404d2' },
        { name: 'mail', value: 'mailorder' },
        { name: 'form-type', value: 'order' },
        { name: 'tomail', value: 't' },
        { name: 'consent_form', value: 'tz-landing-registraciya' }
      ];
    }
    return [];
  }
};

const fdNoName = mailApi.buildMailLeadBody(landingFdMock, '79600484957', '');
assert.equal(fdNoName.get('tel'), '79600484957');
assert.equal(fdNoName.get('consent_request'), '1');
assert.equal(fdNoName.get('name'), null);

const landingJs = readFileSync(
  path.join(root, 'pages/services/registraciya-tovarnogo-znaka/assets/registraciya-landing.js'),
  'utf8'
);
const formHtml = readFileSync(
  path.join(root, 'pages/services/registraciya-tovarnogo-znaka/blocks/lireate-contact-form.inc.php'),
  'utf8'
);
assert.match(landingJs, /submitMailLead/);
assert.match(landingJs, /reach\('tz_form_success'\)/);
assert.match(landingJs, /reach\('zayavka'\)/);
assert.match(landingJs, /reach\('tz_form_error'/);
assert.match(landingJs, /if \(!sent\)/);
assert.match(landingJs, /initHeroChannelMenu/);
assert.doesNotMatch(landingJs, /t\.me.*submit|telegram.*submit/i);

assert.match(formHtml, /lx-landing-messengers/);
assert.match(formHtml, /data-lx-tz-placement="contact"/);
assert.doesNotMatch(formHtml, /lx-form-alt/);
assert.doesNotMatch(formHtml, /class="lx-contact-side"/);

console.log('test_registraciya_landing_submit: OK');
