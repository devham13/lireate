/**
 * Smoke: объединённая marketing+ads галочка → два поля в FormData (логика main.js).
 */
import assert from 'node:assert/strict';

function syncMarketingCombined(fd, checked) {
  fd.delete('consent_marketing_data');
  fd.delete('consent_ads');
  if (checked) {
    fd.set('consent_marketing_data', '1');
    fd.set('consent_ads', '1');
  }
}

const off = new FormData();
off.set('consent_request', '1');
syncMarketingCombined(off, false);
assert.equal(off.get('consent_marketing_data'), null);
assert.equal(off.get('consent_ads'), null);

const on = new FormData();
on.set('consent_request', '1');
syncMarketingCombined(on, true);
assert.equal(on.get('consent_marketing_data'), '1');
assert.equal(on.get('consent_ads'), '1');

console.log('ok consent marketing combined');