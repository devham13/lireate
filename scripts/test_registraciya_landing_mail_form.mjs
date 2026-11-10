/**
 * Мок-тесты цепочки формы лендинга → /mail.php (без запросов к боевому amoCRM).
 * node scripts/test_registraciya_landing_mail_form.mjs
 */
import assert from 'node:assert/strict';

// top-level await for mock submit
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import vm from 'node:vm';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const mailJs = readFileSync(
  path.join(root, 'pages/services/registraciya-tovarnogo-znaka/assets/lireate-shared/lireate-lead-mail.js'),
  'utf8'
);

const sandbox = { window: {}, FormData: MockFormData };
vm.runInNewContext(mailJs + '\n;this.lireateLeadMail = window.lireateLeadMail;', sandbox);
const api = sandbox.window.lireateLeadMail;

function MockFormData() {
  this._m = new Map();
}
MockFormData.prototype.append = function (k, v) {
  this._m.set(k, String(v));
};
MockFormData.prototype.set = function (k, v) {
  this._m.set(k, String(v));
};

assert.equal(api.normalizePhoneRu('+7 (960) 048-49-57'), '79600484957');
assert.equal(api.normalizePhoneRu('9600484957'), '79600484957');
assert.equal(api.parseMailPhpResponse('["success"]').ok, true);
assert.equal(api.parseMailPhpResponse('["error",{"tel":"Заполните поле с телефоном!"}]').ok, false);

const mockForm = {
  querySelectorAll: () => [],
  getAttribute: (n) => (n === 'data-lx-mail-action' ? '/mail.php' : n === 'data-lx-mail-mock' ? '1' : '')
};
const mockResult = await api.submitMailLead(mockForm, { phoneDigits: '79600484957', mock: true });
assert.equal(mockResult.ok, true);

const formHtml = readFileSync(
  path.join(root, 'pages/services/registraciya-tovarnogo-znaka/blocks/lireate-contact-form.inc.php'),
  'utf8'
);
assert.match(formHtml, /data-lx-mail-action="\/mail\.php"/);
assert.match(formHtml, /name="form-type" value="order"/);
assert.match(formHtml, /name="mail" value="mailorder"/);
assert.match(formHtml, /name="tel"/);
assert.doesNotMatch(formHtml, /name="phone"/);
assert.match(formHtml, /data-lx-lead-handler="landing"/);

console.log('test_registraciya_landing_mail_form: OK');
