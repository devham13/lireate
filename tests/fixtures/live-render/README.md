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
   **Круг 15 — патч `main.php` (`scripts/lireate_home_light_v4_install.py` + `lireate_cms_main_body_class.py`):**
   - на `home` не выводится `lireate-cms-page-dark` / `lireate-home-dark` (тернарий с `$_lx_home_v2` → только v2 или пусто); строка **`lireate-home-dark` остаётся в исходнике** в комментарии для cron;
   - удаление `'home'` из PHP-массивов тёмных страниц — **только** внутри блока с маркером `lireate_design_cms_dark_pages_v2`, без глобального regex.
   - главная: `#lh4-lead-form` с `data-lireate-consent-managed="1"`; в `live-assets/template/js/main.js` (и `deploy-patches/.../main.js.consent-skip-managed.patch` на прод) legacy-инжектор согласий пропускает форму с этим атрибутом или с уже имеющимся `input[name=consent_request]`.
   **AFTER пересобран владельцем на `6554440` (круг 15)** — все 7 страниц с патчем main.php; `run_live_render_fixture_screenshots.sh --sides after --fail-on-contrast` → `low_contrast_count=0` на всех.

### Consent на опубликованных услугах (раскладка `publish_service`)

После `lireate_hosting_publish_service.py` каталог услуги лежит в `DOCROOT/<url_path>/` (например `/prodlenie_tovarnogo_znaka/`); общие include — в `<slug>/assets/lireate-shared/`, локальные блоки — в `<slug>/blocks/`. **Не** опираться на `DOCROOT/_shared/…` — файла там нет (home-install кладёт consent только в `pages/services/_shared/` в репозитории / `assets/lireate-shared/` на хостинге).

`blocks/lireate-contact-section.inc.php` подключает consent через `lireate-lead-consent-resolve.inc.php` (копируется `lireate_service_sync_shared.py` в корень slug и в `assets/lireate-shared/`). Patent catalog: `require` блоков из `__DIR__ . '/blocks/'`, не из `dirname(__DIR__)`.

**CI:** `python3 scripts/lireate_service_publish_ci_render.py` (workflow `php56-compat`) — для каждого slug с `publish.manifest.json` (включая `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat`, всего 21): симуляция publish → `php index.php` → нет Fatal/Warning, есть `</html>`.

### Откат опубликованных услуг (у `rollout` нет `--rollback`)

Восстановление — **только из бэкапов** `publish_service` и performance-patch, вручную:

```bash
export LIREATE_DOCROOT=~/www/public_html
SLUG=prodlenie-tovarnogo-znaka
STAMP=20261125T183000Z   # из лога publish --apply
# сначала performance (если был rollout --apply), затем publish:
/opt/python/python-3.8.8/bin/python scripts/lireate_hosting_service_rollback.py \
  --docroot "$LIREATE_DOCROOT" --slug "$SLUG" --rollback "$STAMP" \
  --perf-backup ~/lireate-autopub/backups/performance/<UTCSTAMP>
/opt/python/python-3.8.8/bin/python scripts/lireate_hosting_service_rollback.py \
  --docroot "$LIREATE_DOCROOT" --slug "$SLUG" --rollback "$STAMP"
```

`rollback-manifest.json` в бэкапе перечисляет все записанные файлы; откат **восстанавливает** перезаписанные из бэкапа и **удаляет** файлы, появившиеся при первой публикации.

**Rollout:** 20 slug (без `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat`). **CI render:** 21 slug с `publish.manifest.json`.

Повторный выклад с `--update` возможен после правки slug в репозитории; старый каталог без marker — только полный publish с бэкапом существующего дерева.

**После выкладки Light V4 не запускать** `lireate_design_hosting_apply.sh` (если есть на хостинге) — CSS/услуги уже через overlay/PF/home/rollout.
3. Рендер: PHP 5.6 `php -S 127.0.0.1:19191 -t <копия> router.php` с SQLite-копией публичных таблиц.
4. `python3 scripts/build_live_render_fixture.py --before-root <живой снимок> --after-root <копия> --sides after`
   (BEFORE не трогать). Скрипт сам заменяет токены форм на `FIXTURE` и обновляет `asset-map.json`.

Стенд (снимок, SQLite, shim) есть только у проверяющего: закоммитьте изменения установщика —
проверяющий пересоберёт AFTER и закоммитит новый HTML.
