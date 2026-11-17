(function () {
  'use strict';
  var reduced = document.documentElement.classList.contains('lx-no-motion');

  document.querySelectorAll('[data-lx-quiz]').forEach(function (root) {
    var steps = root.querySelectorAll('.lx-quiz-step');
    var verdict = root.querySelector('.lx-quiz-verdict');
    var mascot = root.querySelector('.lx-quiz-mascot');
    var idx = 0;
    var score = 0;

    function showStep(n) {
      steps.forEach(function (s, i) {
        s.hidden = i !== n;
      });
    }

    root.querySelectorAll('[data-quiz-answer]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var weight = parseInt(btn.getAttribute('data-quiz-weight') || '0', 10);
        score += weight;
        idx += 1;
        if (idx < steps.length) {
          showStep(idx);
          return;
        }
        var need = score >= parseInt(root.getAttribute('data-lx-quiz-threshold') || '2', 10);
        if (verdict) {
          verdict.hidden = false;
          verdict.querySelector('[data-quiz-result]').textContent = need
            ? root.getAttribute('data-lx-quiz-yes') || 'Да, имеет смысл обсудить с юристом.'
            : root.getAttribute('data-lx-quiz-no') || 'Возможно, достаточно самопроверки; при сомнениях — консультация.';
          var cta = verdict.querySelector('a[href="#contact"]');
          if (cta && need) cta.classList.add('lx-btn-primary');
        }
        steps.forEach(function (s) {
          s.hidden = true;
        });
        if (mascot && !reduced) mascot.classList.add('lx-quiz-mascot--cheer');
      });
    });

    if (steps.length) showStep(0);
  });
})();
