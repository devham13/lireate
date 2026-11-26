/* Accessible names for legacy CMS controls; no changes to their handlers. */
(function () {
  'use strict';
  function name(selector, text) {
    document.querySelectorAll(selector).forEach(function (node) {
      if (!node.getAttribute('aria-label') && !node.textContent.trim()) {
        node.setAttribute('aria-label', text);
      }
    });
  }
  name('#whatsapp_icon', 'Написать в WhatsApp');
  name('#telegram_icon', 'Написать в Telegram');
  name('a.topBarLogo', 'Лирейт — главная страница');
  if (!document.querySelector('main, [role="main"]')) {
    var content = document.querySelector('.lireate-blog-page, #lireate-home, #articleBox, #textBox, body > .container.content');
    if (content) content.setAttribute('role', 'main');
  }
  var max = document.querySelector('#max_icon img');
  if (max) {
    max.setAttribute('width', '63');
    max.setAttribute('height', '63');
  }
}());
