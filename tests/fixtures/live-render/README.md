# live-render: статический HTML настоящего рендера lireate.ru (BEFORE / AFTER)

Снято 25.11.2026 ~20:45 МСК со стенда проверяющего: живой снимок `~/www/public_html`
(после смены пароля БД в 20:06 МСК; `data/db.php` и файлы с mysqli — заглушки),
PHP 5.6.40 (`php -S` + router), публичные таблицы CMS в SQLite через mysqli-shim.

* `before/<page>.html` — живой сайт как есть (флаг `template/page/home/lireate-home-v2.on` включён).
* `after/<page>.html` — тот же снимок после установщиков ветки на коммите 4db10e5/5cd546e:
  `lireate_cms_light_v4_overlay_install.py --apply` → `lireate_hosting_pf_install.py --apply` →
  `lireate_blog_install.py --apply` → `lireate_home_light_v4_install.py --apply` → rollout.

| page | URL |
|---|---|
| home | `/` |
| article | `/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/` |
| reviews | `/reviews` |
| team | `/team/` |
| portfolio | `/portfolio` |
| poshliny | `/patentnye-zadachi/poshliny-registraciya/` |
| prodlenie | `/prodlenie_tovarnogo_znaka/` |

## Ссылки на ресурсы

* **AFTER, CSS/JS из ветки** — относительные пути к файлам репозитория (`../../../../template/css/...`,
  `../../../../pages/blog/_shared/...`, `../../../../pages/services/_shared/...`). Правка CSS в ветке видна
  при следующем запуске скрипта, пересобирать фикстуру не нужно. Какой файл править для какого URL —
  `asset-map.json` → `after.<url>.edit`; `identical_copies` — зеркала того же содержимого
  (синхронизируются установщиками / `lireate_service_sync_shared.py`, их не править руками).
* **Живые CSS/JS, которых нет в ветке** (bootstrap, main.css, lireate-cms-page-dark.css,
  lireate-home-dark.css, бандлы…) — копии в `live-assets/` (одинаковы до/после) и
  `live-assets-before/` (версия живого сайта, которую установщики заменили). `url()` внутри
  переписаны на `https://lireate.ru/...`. Это часть «входа» — их не править: если тёмная тема
  перебивает Light V4, это надо лечить в CSS ветки (или в патче main.php), а не здесь.
* Картинки, шрифты, ссылки страниц — абсолютные `https://lireate.ru/...`.
* BEFORE ссылается только на `live-assets*` — он заморожен и от правок ветки не меняется.

## Скриншоты и замеры

```bash
pip install playwright && python3 -m playwright install chromium   # один раз
scripts/run_live_render_fixture_screenshots.sh                      # before+after, 1440 и 390
scripts/run_live_render_fixture_screenshots.sh --sides after --pages home,article --fail-on-contrast
```

Результат: `$RUNNER_TEMP|/tmp/live-render-shots/{side}-{page}-{1440|390}[-full].png` и `metrics.json`:
`low_contrast_count` и `low_contrast[]` (текст < 4.5:1 по вычисленным цветам, фон — первый
непрозрачный предок), `grid` (перекрытия/ширины карточек `/reviews`, `/portfolio`), `h1`, `bodyBg`,
`legacyHome` / `lightV4Home` (видимость старого `#lireate-home` и Light V4 на главной), `ym`, `noindex`.
Метрика, Roistat, VK, callbackkiller и прочие сторонние запросы блокируются. Картинки грузятся
с `https://lireate.ru`; если сайт недоступен из сети раннера — `LIREATE_FIXTURE_PROXY=...` или
`LIREATE_FIXTURE_OFFLINE=1` (картинок не будет, раскладка и контраст текста считаются).

Замечание: CMS отдаёт для статьи HTTP-статус 404 при полностью отрендеренной странице — HTML полный.

## Санитизация

* `fuck_spamers`, `mail_token` (скрытые поля форм) и `window.lireateMailSpamGuard` → `"FIXTURE"`.
* Пароля/логина БД, токенов, ключей amo, ClientID, e-mail из forEmail нет (единственный e-mail —
  публичный `info@lireate.ru`). Текст BEFORE сверен со страницами https://lireate.ru: лишних
  слов и чисел нет. Публичные идентификаторы, которые и так в коде живых страниц (счётчик
  Метрики, site-verification, код виджета), оставлены как есть.

## Когда нужно пересобрать AFTER (правки PHP / main.php / установщиков)

Правки только CSS/JS из `asset-map.json` пересборки не требуют. Если для фикса нужно менять то,
что попадает в HTML на сервере, — `template/main.php` (классы `body`, обёртка
`#lireate-home-light-v4-wrap`, вывод старого `#lireate-home` при выключенном v2), PHP-шаблоны
статьи/отзывов/портфолио — AFTER пересобирается только на стенде с живым снимком:

1. Вход: копия живого `~/www/public_html` (снимок после 20:06 МСК 25.11.2026) с заглушкой
   `data/db.php`; флаг `template/page/home/lireate-home-v2.on` на месте. Сам `template/main.php`
   и `data/db.php` в репозиторий не кладутся.
2. Установщики ветки по порядку на копии (`LIREATE_DOCROOT=<копия>`, `HOME=<песочница>`):
   ```bash
   python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$LIREATE_DOCROOT" --apply
   python3 scripts/lireate_hosting_pf_install.py        --docroot "$LIREATE_DOCROOT" --apply
   python3 scripts/lireate_blog_install.py --apply       # docroot берётся из LIREATE_DOCROOT/.env
   python3 scripts/lireate_home_light_v4_install.py     --docroot "$LIREATE_DOCROOT" --apply  # патч main.php главной
   ```
   Блокер 1 (главная) живёт в патче main.php, который делает `lireate_home_light_v4_install.py`:
   правьте этот установщик (и/или `template/css/lireate-home-light-v4.css`), а не main.php руками.
   После коммита **круг 14**: установщик дополнительно оборачивает `echo ' lireate-cms-page-dark'`
   / `lireate-home-dark` условием `$temp['page']!='home'` (маркер `lireate-home-light-v4-install:suppress-home-dark`)
   и убирает `home` из PHP-массивов тёмных страниц. **AFTER `home.html` в репозитории не пересобран** —
   для проверки главной используйте CSS/JS ветки на текущем AFTER; полный свежий AFTER home —
   только после пересборки на стенде.
3. Рендер: PHP 5.6 `php -S 127.0.0.1:19191 -t <копия> router.php` с SQLite-копией публичных таблиц.
4. `python3 scripts/build_live_render_fixture.py --before-root <живой снимок> --after-root <копия> --sides after`
   (BEFORE не трогать). Скрипт сам заменяет токены форм на `FIXTURE` и обновляет `asset-map.json`.

Стенд (снимок, SQLite, shim) есть только у проверяющего: закоммитьте изменения установщика —
проверяющий пересоберёт AFTER и закоммитит новый HTML.
