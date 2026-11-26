<?php
/** Hamburger + #leftSideBar (полное меню через sidebar inc). */
$lpc_dir = dirname(__FILE__);
?>
<div class="lireate-public-cms-chrome-shell" data-lireate-public-cms-chrome="v1">
  <div id="veil" class="lireate-public-cms-veil" hidden aria-hidden="true"></div>
  <button type="button" class="hamburger is-closed lireate-public-cms-burger" id="lireatePublicCmsBurger" aria-label="Открыть меню сайта" aria-controls="leftSideBar" aria-expanded="false">
    <span class="hamb-top"></span>
    <span class="hamb-middle"></span>
    <span class="hamb-bottom"></span>
  </button>
<?php include $lpc_dir . '/lireate-public-cms-sidebar.inc.php'; ?>
</div>
<script>
(function () {
  function lireatePublicCmsAccordion() {
    var questions = document.querySelectorAll('#leftSideBar .js-question');
    var answers = document.querySelectorAll('#leftSideBar .js-answer');
    var arrows = document.querySelectorAll('#leftSideBar .leftSideArrow');
  var i, n;
    for (i = 0; i < questions.length; i++) {
      (function (idx) {
        questions[idx].onclick = function () {
          if (answers[idx] && answers[idx].style.height === answers[idx].scrollHeight + 'px') {
            answers[idx].style.height = '0px';
            if (arrows[idx]) { arrows[idx].style.transform = 'rotateZ(0deg)'; }
          } else {
            for (n = 0; n < questions.length; n++) {
              if (answers[n]) { answers[n].style.height = '0px'; }
              if (arrows[n]) { arrows[n].style.transform = 'rotateZ(0deg)'; }
            }
            if (answers[idx]) {
              answers[idx].style.height = answers[idx].scrollHeight + 'px';
              if (arrows[idx]) { arrows[idx].style.transform = 'rotateZ(90deg)'; }
            }
          }
        };
      })(i);
    }
  }
  function lireatePublicCmsMarkCurrent() {
    try {
      var h0 = location.hostname.replace(/^www\./, '');
      var p = location.pathname.replace(/\/+$/, '') || '/';
      var links = document.querySelectorAll('#leftSideNav a[href]');
      var i, l, hr, q;
      for (i = 0; i < links.length; i++) {
        l = links[i];
        hr = l.getAttribute('href') || '';
        if (/^(tel:|mailto:)/.test(hr)) { continue; }
        if (l.hostname && l.hostname.replace(/^www\./, '') !== h0) { continue; }
        q = (l.pathname || '').replace(/\/+$/, '') || '/';
        if (q === p) {
          l.className += ' lsn-current';
          var pr = l.parentNode;
          if (pr && /(^| )js-answer( |$)/.test(pr.className) && pr.previousElementSibling) {
            pr.previousElementSibling.className += ' lsn-has-current';
          }
        }
      }
    } catch (e) {}
  }
  function lireatePublicCmsBurger() {
    var btn = document.getElementById('lireatePublicCmsBurger');
    var veil = document.getElementById('veil');
    if (!btn) { return; }
    btn.addEventListener('click', function () {
      var open = document.body.classList.toggle('lireate-public-cms-menu-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.classList.toggle('is-open', open);
      btn.classList.toggle('is-closed', !open);
      if (veil) {
        veil.hidden = !open;
      }
      document.documentElement.style.overflow = open ? 'hidden' : '';
    });
    if (veil) {
      veil.addEventListener('click', function () {
        document.body.classList.remove('lireate-public-cms-menu-open');
        btn.setAttribute('aria-expanded', 'false');
        btn.classList.remove('is-open');
        btn.classList.add('is-closed');
        veil.hidden = true;
        document.documentElement.style.overflow = '';
      });
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      lireatePublicCmsAccordion();
      lireatePublicCmsMarkCurrent();
      lireatePublicCmsBurger();
    });
  } else {
    lireatePublicCmsAccordion();
    lireatePublicCmsMarkCurrent();
    lireatePublicCmsBurger();
  }
})();
</script>