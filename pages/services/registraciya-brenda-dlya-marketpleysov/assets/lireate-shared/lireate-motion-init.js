(function () {
  'use strict';
  var mq = window.matchMedia('(prefers-reduced-motion: reduce)');
  function apply() {
    if (mq.matches) {
      document.documentElement.classList.add('lx-no-motion');
    } else {
      document.documentElement.classList.remove('lx-no-motion');
    }
  }
  apply();
  if (mq.addEventListener) {
    mq.addEventListener('change', apply);
  } else if (mq.addListener) {
    mq.addListener(apply);
  }
})();
