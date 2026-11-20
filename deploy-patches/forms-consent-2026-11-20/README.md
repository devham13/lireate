# Патч: формы согласий, контраст модалки, hero без подложки, кнопка Max

Дата: 2026-11-20. PHP 5.6, боевой корень: `~/www/public_html/`.

В git **нет** исходников части CMS-файлов (на проде отдаётся уже отрендеренный HTML). Ниже — **целиком готовые CSS** и **точечные правки** для PHP/JS. Перед заменой — бэкап оригинала на сервере.

## Версии в `template/main.php` (после выкладки)

Поднять query-string, чтобы сбросить кэш браузера/CDN:

| Было | Стало (пример) |
|------|----------------|
| `main.js?v=20261117a07` | `main.js?v=20261120fc1` |
| `lireate-home-hero.css?v=20261111hero2` | `lireate-home-hero.css?v=20261120fc1` |
| `lireate-home-modal-lu.css?v=20261111mod2` | `lireate-home-modal-lu.css?v=20261120fc1` |
| `lireate-home-dark.css?v=20261111design6b` | `lireate-home-dark.css?v=20261120fc1` |

## Замена целыми файлами (из этого каталога)

| Файл в патче | Путь на REG.RU |
|---|---|
| `template/css/lireate-home-hero.css` | `~/www/public_html/template/css/lireate-home-hero.css` |
| `template/css/lireate-home-modal-lu.css` | `~/www/public_html/template/css/lireate-home-modal-lu.css` |
| `template/css/lireate-home-dark-hero-flat.patch.css` | в **конец** `template/css/lireate-home-dark.css` **или** отдельный `<link>` после dark.css |

## `template/js/main.js` — только diff

```bash
cd ~/www/public_html
cp template/js/main.js template/js/main.js.bak-2026-11-20
patch -p0 < /path/to/repo/deploy-patches/forms-consent-2026-11-20/template/js/main.js.consent.patch
node --check template/js/main.js
```

Патч: `template/js/main.js.consent.patch` (две галочки, `syncMarketingCombined` / `bindMarketingCombined`, ветка `#double-form`). **Не** вставлять `// pragma` внутрь вызовов `window.ym(...)`.

## `template/page/home/lireate-home-hero.inc.php` (исходник на сервере)

1. **Удалить** целиком блок:

```html
<div class="lireate-hero-v4__bg" aria-hidden="true">
  <span class="lireate-hero-v4__orb lireate-hero-v4__orb--a"></span>
  ...
  <span class="lireate-hero-v4__grid"></span>
</div>
```

2. **После** ссылки «Написать в Telegram» (класс `lireate-hero-v4__btn--ghost`, `href` на Telegram — часто `<?php echo … ?>` или константа из `lireate-home-url.inc.php`) **вставить** такую же по классу кнопку:

```html
      <a class="lireate-hero-v4__btn lireate-hero-v4__btn--ghost" href="https://max.ru/channel_lireate" target="_blank" rel="noopener noreferrer" data-lx-ym-goal="perehod_v_messendzher">Написать в Max</a>
```

Остальной PHP (`<?php`, `include`, `echo` заголовков, `data-tomail`, href услуг) **не трогать**.

## `template/page/cms/lireate-cms-hero-facts.inc.php`

Те же шаги: удалить `.lireate-hero-v4__bg`, после Telegram — кнопка Max (как выше). CMS-кнопка «Интересно? Обращайтесь!» и пустой `<h1></h1>` с PHP-заполнением — без изменений.

## `everest/pages/main/appearForm.php`

**Заменить** два блока `form-agreement` (marketing + ads) на один:

```html
<div class="form-agreement">
  <input type="checkbox" id="popup-consent-marketing-ads" value="1">
  <label for="popup-consent-marketing-ads">Я согласен(-на) на <a href="/marketing-data-consent.html" target="_blank" rel="noopener" class="agreement-link">обработку контактных данных для рекламы</a> и <a href="/advertising-consent.html" target="_blank" rel="noopener" class="agreement-link">получение рекламы ЛИРЕЙТ</a> по телефону и, если указан, email.</label>
  <input type="hidden" name="consent_marketing_data" id="popup-hidden-marketing" value="" disabled="disabled">
  <input type="hidden" name="consent_ads" id="popup-hidden-ads" value="" disabled="disabled">
</div>
```

**Перед** закрывающим `</script>` внизу файла добавить:

```javascript
    var popupMarketingAds = document.getElementById('popup-consent-marketing-ads');
    var popupHiddenMarketing = document.getElementById('popup-hidden-marketing');
    var popupHiddenAds = document.getElementById('popup-hidden-ads');
    function syncPopupMarketingCombined() {
        if (!popupMarketingAds || !popupHiddenMarketing || !popupHiddenAds) return;
        if (popupMarketingAds.checked) {
            popupHiddenMarketing.disabled = false;
            popupHiddenAds.disabled = false;
            popupHiddenMarketing.value = '1';
            popupHiddenAds.value = '1';
        } else {
            popupHiddenMarketing.disabled = true;
            popupHiddenAds.disabled = true;
            popupHiddenMarketing.value = '';
            popupHiddenAds.value = '';
        }
    }
    if (popupMarketingAds) {
        popupMarketingAds.addEventListener('change', syncPopupMarketingCombined);
    }
```

Блок `consent_request` и PHP-обвязку формы **не менять**.

## Не трогать

`mail.php`, `consent-gate.php`, `inc/lireate-amo-lead.php`, корневой `index.php`, `.htaccess`.

## Проверка согласий (без amo)

1. Модалка на главной → DevTools → Network.
2. Только первая галочка → нет `consent_marketing_data` / `consent_ads`.
3. Вторая включена → оба `=1`.
4. `#double-form`: hidden в скрытой форме синхронизируются так же.

## Страницы услуг (git → CRON)

См. PR: `pages/services/<slug>/`, hero-max → `lireate_service_max_channel_url()`.