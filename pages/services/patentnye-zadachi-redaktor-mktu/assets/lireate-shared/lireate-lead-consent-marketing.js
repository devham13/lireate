(function (root) {
  'use strict';
  function syncMarketingCombined(form) {
    if (!form) {
      return;
    }
    var cb = form.querySelector('.lireate-consent-marketing-combined');
    var m = form.querySelector('input[name="consent_marketing_data"]');
    var a = form.querySelector('input[name="consent_ads"]');
    if (!cb || !m || !a) {
      return;
    }
    if (cb.checked) {
      m.disabled = false;
      a.disabled = false;
      m.value = '1';
      a.value = '1';
    } else {
      m.disabled = true;
      a.disabled = true;
      m.value = '';
      a.value = '';
    }
  }
  function bindMarketingCombined(form) {
    if (!form) {
      return;
    }
    var cb = form.querySelector('.lireate-consent-marketing-combined');
    if (!cb) {
      return;
    }
    cb.addEventListener('change', function () {
      syncMarketingCombined(form);
    });
    syncMarketingCombined(form);
  }
  root.lireateBindMarketingConsent = bindMarketingCombined;
  if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function () {
      var forms = document.querySelectorAll('form.lx-mail-lead-form, form#lh4-lead-form');
      for (var i = 0; i < forms.length; i++) {
        bindMarketingCombined(forms[i]);
      }
    });
  }
})(typeof window !== 'undefined' ? window : this);