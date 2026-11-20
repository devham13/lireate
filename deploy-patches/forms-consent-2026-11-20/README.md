# Патч: формы согласий, контраст модалки, hero без подложки, кнопка Max

Дата: 2026-11-20. PHP 5.6, боевой корень: `~/www/public_html/`.

Файлы в этом каталоге **не в git CMS-репозитории** (кроме `pages/services/`). Заливка по SSH с бэкапом оригинала.

## Замена на сервере

| Файл в патче | Путь на REG.RU |
|---|---|
| `template/js/main.js` | `~/www/public_html/template/js/main.js` |
| `everest/pages/main/appearForm.php` | `~/www/public_html/everest/pages/main/appearForm.php` |
| `template/page/home/lireate-home-hero.inc.php` | `~/www/public_html/template/page/home/lireate-home-hero.inc.php` |
| `template/page/cms/lireate-cms-hero-facts.inc.php` | `~/www/public_html/template/page/cms/lireate-cms-hero-facts.inc.php` |
| `template/css/lireate-home-hero.css` | `~/www/public_html/template/css/lireate-home-hero.css` |
| `template/css/lireate-home-modal-lu.css` | `~/www/public_html/template/css/lireate-home-modal-lu.css` |
| `template/css/lireate-home-dark-hero-flat.patch.css` | **добавить** в конец `template/css/lireate-home-dark.css` **или** подключить отдельным `<link>` после `lireate-home-dark.css` |

Не трогать: `mail.php`, `consent-gate.php`, `inc/lireate-amo-lead.php`, корневой `index.php`, `.htaccess`.

После выкладки CSS — сбросьте query-string в шаблоне (`?v=20261120consent`) для `lireate-home-hero.css` и `lireate-home-modal-lu.css`, если CDN/браузер держит кэш.

## Что изменено

1. **Две галочки** вместо трёх: обязательный `consent_request`; необязательная объединённая — при отметке в POST уходят `consent_marketing_data=1` и `consent_ads=1` (для `double-form` — hidden в скрытой форме).
2. **Модалка** `.modal-mail`: заголовок, плейсхолдеры, иконки, текст согласий — контраст WCAG AA.
3. **Hero v4**: убран блок `.lireate-hero-v4__bg` (орбы/сетка), фон секции прозрачный — контент на общем фоне страницы; кнопка «Написать в Max» → `https://max.ru/channel_lireate`.
4. **CMS hero facts**: то же (подложка + Max).

## Страницы услуг (git → хостинг CRON)

Слаги с кнопкой Max в hero (репозиторий `pages/services/<slug>/`):

- `registraciya-tovarnogo-znaka` (pilot-v2: chip MAX уже был, hero Max → канал)
- `registraciya-tovarnogo-znaka-v-kitae`
- `prodlenie-tovarnogo-znaka`
- `zashchita-avtorskih-prav`
- `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat`
- `vozrazhenie-protiv-registracii-tovarnogo-znaka`
- `proverka-tovarnogo-znaka` (CMS findtz)
- `spory-po-ispolzovaniju-tovarnogo-znaka`

Старые CMS-услуги на сервере (`dogovory` и др.) используют общий CMS hero include — правка `lireate-cms-hero-facts.inc.php`.

## Проверка согласий (без отправки в amo)

1. Открыть главную, вызвать модалку «Бесплатная консультация».
2. DevTools → Network, включить «Preserve log».
3. Отметить только обязательную галочку → Submit → в payload **нет** `consent_marketing_data` / `consent_ads`.
4. Добавить вторую (объединённую) галочку → снова Submit (можно «Stop» до ответа) → в `Form Data` **оба** поля `=1`.
5. Для страницы с `double-form`: то же в hidden-форме (поля появляются в serialize скрытого `#double-form`).

## Скриншоты

В PR: `artifacts/` — главная, CMS «Патентование изобретений», услуга `registraciya-tovarnogo-znaka-v-kitae`, модалка до/после (демо с патч-CSS).