/* LIREATE 2026-10-31: first-touch UTM/yclid (30 дней) + скрытые поля во все формы. Без персональных данных. */
(function () {
  'use strict';
  var KEY = 'lr_ft', DAYS = 30, P = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid'];
  function getCookie(n) {
    var m = document.cookie.match(new RegExp('(?:^|; )' + n.replace(/[.$?*|{}()\[\]\\\/+^]/g, '\\$&') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  }
  function load() {
    var raw = '';
    try { raw = window.localStorage.getItem(KEY) || ''; } catch (e) {}
    if (!raw) raw = getCookie(KEY);
    if (!raw) return null;
    try {
      var o = JSON.parse(raw);
      if (o && o.ts && (Date.now() - o.ts) < DAYS * 864e5) return o;
    } catch (e) {}
    return null;
  }
  function save(o) {
    var s = JSON.stringify(o);
    try { window.localStorage.setItem(KEY, s); } catch (e) {}
    document.cookie = KEY + '=' + encodeURIComponent(s) + '; path=/; max-age=' + (DAYS * 86400) + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
  }
  var q = {};
  try {
    var sp = new URLSearchParams(window.location.search);
    P.forEach(function (k) { var v = sp.get(k); if (v) q[k] = v.slice(0, 250); });
  } catch (e) {}
  var ft = load();
  var ref = document.referrer && document.referrer.indexOf(location.hostname) === -1 ? document.referrer.slice(0, 500) : '';
  if (!ft) {
    ft = { ts: Date.now(), p: q, ref: ref, land: (location.origin + location.pathname).slice(0, 300) };
    save(ft);
  } else if (Object.keys(q).length && !Object.keys(ft.p || {}).length) {
    /* первый визит был без меток (органика), рекламный — с метками: метки дописываем, время первого касания сохраняем */
    ft.p = q; if (ref) ft.ref = ref; ft.land = (location.origin + location.pathname).slice(0, 300); save(ft);
  }
  function put(form, name, val) {
    if (!val) return;
    var el = form.querySelector('input[name="' + name + '"]');
    if (!el) { el = document.createElement('input'); el.type = 'hidden'; el.name = name; form.appendChild(el); }
    if (!el.value) el.value = val;
  }
  function fill(form) {
    if (!form || form.tagName !== 'FORM') return;
    var o = load() || ft || { p: {} };
    P.forEach(function (k) { put(form, k, (o.p || {})[k]); });
    put(form, 'ym_uid', getCookie('_ym_uid'));
    put(form, 'lr_referrer', o.ref);
    put(form, 'lr_landing', o.land);
    /* audit07 B1: страница, с которой отправлена заявка (обновляется при каждом заполнении) */
    try {
      var pg = form.querySelector('input[name="lr_page"]');
      if (!pg) { pg = document.createElement('input'); pg.type = 'hidden'; pg.name = 'lr_page'; form.appendChild(pg); }
      pg.value = String(location.href).slice(0, 500);
    } catch (e) {}
  }
  window.lireateUtmFill = fill;
  function all() { Array.prototype.forEach.call(document.forms, fill); }
  document.addEventListener('submit', function (e) { fill(e.target); }, true);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', all); else all();
})();
