(function () {
  'use strict';

  var PRESETS = {
    'prodlenie-tz': {
      base: 22000,
      perClassOver5: 2000,
      lateFee: 3000,
      label: 'Ориентир госпошлины Роспатента (п. 2.22, 2.25; ПП РФ № 941)',
    },
    'po-reg': {
      base: 5000,
      label: 'Ориентир пошлины за регистрацию ПО (ст. 333.30 НК РФ, rospatent.gov.ru)',
    },
  };

  function formatRub(n) {
    return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
  }

  document.querySelectorAll('[data-lx-calc]').forEach(function (root) {
    var key = root.getAttribute('data-lx-calc');
    var preset = PRESETS[key];
    if (!preset) return;

    var classesInput = root.querySelector('[data-lx-calc-classes]');
    var lateInput = root.querySelector('[data-lx-calc-late]');
    var outGov = root.querySelector('[data-lx-calc-out-gov]');
    var outTotal = root.querySelector('[data-lx-calc-out-total]');

    function calc() {
      var total = preset.base;
      if (key === 'prodlenie-tz' && classesInput) {
        var c = parseInt(classesInput.value, 10);
        if (!isNaN(c) && c > 5) {
          total += (c - 5) * preset.perClassOver5;
        }
        if (lateInput && lateInput.checked) {
          total += preset.lateFee;
        }
      }
      var text = formatRub(total);
      if (outGov) outGov.textContent = text;
      if (outTotal) outTotal.textContent = text;
    }

    root.addEventListener('input', calc);
    root.addEventListener('change', calc);
    calc();
  });
})();
