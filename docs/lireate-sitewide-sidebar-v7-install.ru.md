# LIREATE — левое меню на всех публичных страницах (Sitewide Sidebar V7)

Подготовлено 26.11.2026. Ветка для ревью. На REG.RU ещё НЕ публиковалось.

## Проблема и архитектура

На реальной главной, /patent, /mktu, /news, /contacts и /portfolio/bentax элемент `#leftSideBar` уже есть. На /findtz и /patentnye-zadachi его нет: эти страницы рисуют автономные PHP-документы, а не хром основной CMS. Ещё часть страниц может использовать собственные шаблоны вне GitHub.

Решение: один общий menu fragment + CSS + JS загрузчик. На любой странице:
- если уже есть `#leftSideBar`, ничего не дублировать; сохранить стандартные CMS обработчики и мобильное меню;
- если меню отсутствует, асинхронно загрузить public same-origin HTML из `/template/lireate/lireate-sidebar-v7.html` (fallback `/template/lireate/lireate-sidebar-v7-fragment.php`), вставить один `#leftSideBar`, включить desktop layout, burger/veil на мобильном и доступный аккордеон;
- на desktop меню всегда слева и занимает 270px; на mobile выдвигается по кнопке; поддерживает Escape, мышь, клавиатуру, активную ссылку, сворачивание, reduced-motion;
- полная векторная эмблема LIREATE на белом фоне, ссылки всех услуг, портфолио, блога, МКТУ, патентных задач, компании; Telegram/WhatsApp/MAX/VK/YouTube, два телефона и реальная ссылка /contacts/ вместо неработающей изолированной CMS-модалки.

## Что загружено в GitHub

- `template/lireate/lireate-sidebar-v7-content.inc.php` — единый публичный фрагмент меню, скопирован из канонического `pages/_shared/lireate-public-cms-sidebar.inc.php`.
- `template/lireate/lireate-sidebar-v7.html` — статический HTML для быстрого same-origin fetch.
- `template/lireate/lireate-sidebar-v7-fragment.php` — PHP fallback; только public HTML, без пользовательских данных.
- `template/js/lireate-sitewide-sidebar-v7.js` — проверка наличия меню, fallback загрузки, a11y и состояния.
- `template/css/lireate-sitewide-sidebar-v7.css` — светлый десктоп/mobile layout, без изменений существующих CSS на CMS-путях.
- `template/images/logo/logo-light-v7.svg` — исходный LIREATE, адаптированный для белого фона.
- 21 `pages/services/*/index.php` с подключением CSS/JS в head, без изменения логики услуг.
- `scripts/lireate_sitewide_sidebar_v7_install.py` — **белый список** (без обхода DOCROOT): `template/main.php`, все опубликованные услуги по `publish.manifest.json` (без `_template` / `SLUG-ЗАМЕНИТЬ`), плюс legacy `dogovory/`, `registraciya_programm_evm/`, `findtz` и 13 инструментов `patentnye-zadachi/*` (включая `poshliny-prodlenie`). Жёстко исключены: `everest`, `reg-tov-znak*`, `lireateCom`, `copy`, `sms`, `captcha`, `engine`, `mail_parse`, `lireate-analytics`, заглушка `uslugi/index.php`, корневой `index.php`, `.htaccess`, `mail.php`, `consent-gate.php`, `inc/lireate-amo-lead.php`.
- `scripts/test_lireate_sitewide_sidebar_v7_install.py` — офлайн регрессия; `scripts/lireate_sitewide_sidebar_v7_audit.mjs` — полноценная браузерная проверка desktop+mobile по sitemap и manifest.

## Установка через Grok/Cursor, НЕ вручную поверх main

1. Проверь `AGENTS.md`; открой эту ветку в отдельной worktree, не переключай checkout, используемый REG.RU CRON. Проверь status PR 123/128/130/131/132: в них тоже встречаются страницы услуг/патентных задач; объединить без отката последних изменений.
2. По SSH получи фактический `DOCROOT`; изучи host `template/main.php`, рабочий PHP для `/findtz/`, `/patentnye-zadachi/` и двух-трёх подстраниц. **Не угадывай пути**.
3. Выполни `python3 scripts/test_lireate_sitewide_sidebar_v7_install.py` и `node --check template/js/lireate-sitewide-sidebar-v7.js`.
4. Dry-run без записи:
   `python3 scripts/lireate_sitewide_sidebar_v7_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/sidebar-v7"`
5. Просмотри полный список DRY_RUN и SKIP: в выборке **не должно быть** путей `everest/pages/*`, `reg-tov-znak/pages/*`, `reg-tov-znak1(test)/*` и прочих отдельных лендингов. Меняются только whitelist-файлы и новые V7-ассеты из ветки. Совместимость с **PR #132** (`lireate-qa-v6-20261126`, встроенный `#leftSideBar` на 13 patent tools): установщик вставляет блок v7 **перед** `</head>` только при отсутствии маркера v7 (идемпотентно, без сверки полного хеша файла); откат `--rollback` **вырезает** только блок `lireate-sitewide-sidebar-v7-20261126`, не возвращая файл целиком — последующие вставки (#132, perf-cron) сохраняются.
6. Убедись, что публично доступны `/template/lireate/lireate-sidebar-v7.html`, резервный PHP endpoint, CSS, JS, SVG. Если 403/404/CSP: исправь маршрутизацию или подключи PHP sidebar непосредственно к автономному template; не отключай защиту CSP глобально.
7. С согласованием установки: `python3 scripts/lireate_sitewide_sidebar_v7_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/sidebar-v7" --apply --php-bin php`. Перед записью head-файлов проверяется якорь `</head>` и отсутствие маркера v7; внешняя резервная копия и `php -l`; при ошибке откат вырезает v7-блок точечно.
8. Сразу после — браузерный аудит (из корня репозитория):
   `BASE=[REDACTED] CHROME=/usr/bin/chromium LSV7_LIMIT=700 node scripts/lireate_sitewide_sidebar_v7_audit.mjs --concurrency 4`
   Учитывает `.lxq-mobile-nav-toggle` (PR #132), пропуск `SLUG-ЗАМЕНИТЬ`. На 1440 и 390px: один `#leftSideBar`, burger/toggle, overflow, JS; отчёт `lireate-sidebar-v7-audit.json`.
9. Дополнительная ручная проверка: на главной и существующих страницах меню НЕ дублируется; `/findtz/`, `/patentnye-zadachi/` и каждый инструмент имеют полное меню; мобильное меню закрывается по Esc/подложке; категории раскрываются, логотип/пять соцсетей и телефоны кликабельны.
10. Убедись, что следующий CRON (30 минут) не затрёт изменения. Включить это в основной deploy pipeline или перенести в согласованный main только после тестирования.
11. Если ошибки — `--rollback /путь/к/каталогу-бэкапа` (с тем же `--docroot`).

## Важные ограничения

- Не копировать статический шаблон поверх действующих PHP страниц.
- Не менять SEO, h1/meta/canonical, тексты, изображения, калькуляторы, формы, согласия, аналитику, базу данных и существующий top menu.
- Не вставлять второе боковое меню на страницы с реальным `#leftSideBar`.
- Не превращать админку или API в публичные страницы с навигацией. Проверь отчёт сканирования перед `--apply`.
- Отдельные шаблоны без HTML head/body в index.php или маршруты вне sitemap/manifest требуют ручного включения через Cursor; не гарантируй полный охват до браузерного аудита.
- Не выполнять merge PR в main автоматически без разрешения владельца.

Отчёт Grok/Cursor: список всех проверенных URL, сколько страниц уже имело меню, сколько получили новое, какие пропущены и почему, mobile/desktop скриншоты, логи тестов, путь резервной копии и статус CRON.