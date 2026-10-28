(function () {
  'use strict';
  var root = document.querySelector('.lireate-docs-page');
  if (!root) {
    return;
  }
  root.querySelectorAll('[data-lx-ym-goal]').forEach(function (el) {
    el.addEventListener('click', function () {
      var goal = el.getAttribute('data-lx-ym-goal');
      if (goal && typeof window.ym === 'function') {
        var id = document.documentElement.getAttribute('data-lireate-metrika-id');
        if (id) {
          window.ym(Number(id), 'reachGoal', goal);
        }
      }
    });
  });
})();
