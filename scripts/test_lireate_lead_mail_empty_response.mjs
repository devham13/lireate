/**
 * Регрессия: пустой ответ mail.php, fuck spamers, обязательный mail_token (без POST на прод).
 * node scripts/test_lireate_lead_mail_empty_response.mjs
 */
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import vm from 'node:vm';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const mailJs = readFileSync(path.join(root, 'pages/services/_shared/lireate-lead-mail.js'), 'utf8');

function MockFormData() {
  this._m = new Map();
}
MockFormData.prototype.append = function (k, v) {
  this._m.set(k, String(v));
};
MockFormData.prototype.set = function (k, v) {
  this._m.set(k, String(v));
};
MockFormData.prototype.get = function (k) {
  return this._m.has(k) ? this._m.get(k) : null;
};

const phpMonth = crypto.createHash('md5').update('11.2026').digest('hex');
const ctx = { window: { lireateMailSpamGuard: phpMonth }, FormData: MockFormData };
vm.runInNewContext(mailJs, ctx);
const api = ctx.window.lireateLeadMail;

const emptyFail = api.parseMailPhpResponse('', 0);
assert.equal(emptyFail.ok, false);
assert.equal(emptyFail.emptyBody, true);

const emptyOk = api.parseMailPhpResponse('', 200);
assert.equal(emptyOk.ok, true);
assert.equal(emptyOk.assumedSuccess, true);
assert.equal(emptyOk.emptyBody, true);

const spam = api.parseMailPhpResponse('fuck spamers!');
assert.equal(spam.ok, false);
assert.equal(spam.spamGuard, true);

assert.equal(api.parseMailPhpResponse('["success"]').ok, true);

const formWithToken = {
  querySelector: function (sel) {
    if (sel === 'input[name="mail_token"]') {
      return { value: '503066424d5f7b5ad2097703ef3404d2' };
    }
    if (sel.indexOf('fuck_spamers') !== -1) {
      return { value: 'old' };
    }
    return null;
  },
  querySelectorAll: function (sel) {
    if (sel === 'input[type="hidden"]') {
      return [
        { name: 'mail_token', value: '503066424d5f7b5ad2097703ef3404d2' },
        { name: 'mail', value: 'mailorder' },
        { name: 'form-type', value: 'order' },
        { name: 'tomail', value: 'Test' },
        { name: 'consent_form', value: 'lx-test' }
      ];
    }
  return [];
  }
};

const formNoToken = {
  querySelector: function () {
    return null;
  },
  querySelectorAll: function () {
    return [];
  }
};

const validationFail = api.validateMailLeadForm(formNoToken);
assert.equal(validationFail.ok, false);
assert.equal(validationFail.missingMailToken, true);

const fd = api.buildMailLeadBody(formWithToken, '79600484957', 'тест');
assert.equal(fd.get('mail_token'), '503066424d5f7b5ad2097703ef3404d2');
assert.equal(fd.get('mail'), 'mailorder');
assert.equal(fd.get('consent_request'), '1');
assert.equal(fd.get('tel'), '79600484957');
assert.equal(fd.get('fuck_spamers'), phpMonth);

assert.equal(api.parseMailPhpResponse('not-json').showFallback, true);

console.log('test_lireate_lead_mail_empty_response: OK');
