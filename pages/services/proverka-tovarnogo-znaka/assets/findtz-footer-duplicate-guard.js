/* /findtz/: remove only a duplicate CMS legal footer *after* the service footer.
   The official footer.lx-site-footer retains all legal links and organisation details.
   No-op on normal pages with only one footer. */
(function () {
  'use strict';
  function run() {
    var primary = document.querySelector('body.lireate-findtz-topnav-center > footer.lx-site-footer');
    if (!primary || !document.body) { return; }
    var node = primary.nextElementSibling;
    while (node) {
      var next = node.nextElementSibling;
      // Skip floating contact dock / app widgets, scripts and site controls.
      if (node.tagName !== 'SCRIPT' && !node.matches('.lx-dock, .lx-mobile-bar, .lireate-public-cms-burger, [aria-label="Быстрый контакт"]')) {
        var links = node.querySelectorAll('a[href]');
        if (links.length === 2) {
          var foundPrivacy = false;
          var foundConsent = false;
          for (var i = 0; i < links.length; i++) {
            var pathname = links[i].pathname.replace(/\/$/, '');
            if (pathname === '/politika_konfidencialnosti') { foundPrivacy = true; }
            if (pathname === '/private_data') { foundConsent = true; }
          }
          if (foundPrivacy && foundConsent) {
            // Do not remove any content other than the duplicated pair of legal links.
            var text = (node.textContent || '').replace(/\s+/g, ' ').trim();
            if (text.length < 200 && !node.querySelector('form, input, button, iframe, main, article')) {
              node.parentNode.removeChild(node);
            }
          }
        }
      }
      node = next;
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
}());
