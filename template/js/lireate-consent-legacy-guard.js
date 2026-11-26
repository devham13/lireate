/**
 * Не дублировать блоки согласия legacy main.js на формах с готовым consent_request (PHP / Light V4).
 */
(function () {
  if (typeof window.jQuery === 'undefined') {
    return;
  }
  var $ = window.jQuery;
  $(function () {
    $('.ajax_form, .ajax_form_main, .simple_form, .ajax_form_find, form[id="double-form"]').each(function () {
      var form = $(this);
      if (form.attr('data-lireate-consent-managed') === '1' || form.attr('data-lireate-consent-skip') === '1') {
        form.addClass('lireate-consent-skip-inject');
      }
      if (form.find('input[name="consent_request"]').length) {
        form.addClass('lireate-consent-skip-inject');
      }
    });
  });
})();