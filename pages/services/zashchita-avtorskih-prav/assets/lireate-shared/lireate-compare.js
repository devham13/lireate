(function () {
  'use strict';
  document.querySelectorAll('[data-lx-compare]').forEach(function (root) {
    var range = root.querySelector('.lx-compare-range');
    var after = root.querySelector('.lx-compare-after');
    if (!range || !after) return;
    function set() {
      var v = range.value;
      after.style.clipPath = 'inset(0 ' + (100 - v) + '% 0 0)';
      root.style.setProperty('--lx-compare', v + '%');
    }
    range.addEventListener('input', set);
    set();
  });
})();
