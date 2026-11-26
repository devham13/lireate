# LIREATE Portfolio Light V5 — каталог + 14 кейсов

## Объём

15 URL: `/portfolio` (также `/portfolio/`) и:
`/portfolio/svoya-kultura/`, `/portfolio/bentax/`, `/portfolio/avtopravo/`, `/portfolio/kingsname/`,
`/portfolio/doza/`, `/portfolio/happy-mind/`, `/portfolio/elisa-hotels/`, `/portfolio/strigoon/`,
`/portfolio/avenue-deluxe/`, `/portfolio/emiz/`, `/portfolio/nerud-grupp/`, `/portfolio/estetic/`,
`/portfolio/berti/`, `/portfolio/vf/`.

## Дизайн

Общий язык главной LIREATE: светлый фон, #1f212c, #e32020, #195acb, нежно-голубые поверхности,
крупные заголовки, карточки радиусом 16–25px. 390/768/1280/1440/1920px.

- Каталог: первый экран со **сохранёнными** числовыми показателями из оригинальной CMS.
  Сетка 14 реальных кейсов с логотипами товарных знаков **по существующим путям сайта**, поиск по
  названию/городу на клиенте, ниже весь исходный каталог регистраций по регионам. Существующие
  переключатели городов/регионов, ссылки на свидетельства и onclick handlers остаются.
- Кейсы: сохраняется **единственный существующий H1** и SEO-описание (не создаём второй H1).
  Оригинальный `.case-hero` превращается в двухколоночный первый экран: заголовок слева, справа
  логотип товарного знака в подписанной карточке **как карточка специалиста на главной**.
  Сохраняются исходные свидетельства ФИПС, даты, города, иногда два свидетельства на один кейс
  (Bentax и Shtainberg Chemy), видео и внешние ссылки.
- Секция консультации с тремя каналами: Telegram, MAX, WhatsApp. URL основаны на открытых
  действующих ссылках с lireate.ru, не вымышлены. Кнопка страницы кейса в Telegram дополняет
  существующие CTA, не удаляя их. Цели Метрики в разметке сохранены через data-lx-ym-goal.
- Мобильная верстка; reduced-motion и visible focus.

## Источники

Проверены открытые URL кейсов 26.11.2026: логотипы, города, свидетельства, структура
`.case-hero`, `.trademark-mark`, `.video-review`, `.register-tz`.
Исходная бизнес-логика находится на REG.RU в `data/lireate-cases-lib.php`; этого
файла в GitHub нет. Поэтому **не подменять библиотеку и данные**. Решение — CSS/JS
enhancement, действующий контент создаёт всё та же серверная библиотека.

## Файлы PR

- `template/css/lireate-portfolio-v5.css` — дизайн каталога, кейсов и кнопок.
- `template/js/lireate-portfolio-v5.js` — поиск и визуальная компоновка существующего HTML.
- `scripts/lireate_portfolio_v5_install.py` — dry-run, безопасный install, backup, rollback.
- `scripts/test_lireate_portfolio_v5_install.py` — офлайн-регрессия.

## Как внедрить через Grok / Cursor с SSH на REG.RU

1. Изучи `AGENTS.md`, PR и действующий `template/main.php` и
   `data/lireate-cases-lib.php` **только для чтения**. На проде (26.11.2026) все 15 URL
   идут через общий `template/main.php`: каталог `$temp['page']==='portfolio'`,
   кейсы `$temp['page']` вида `portfolio/<slug>` (см. `audit07 C4` в fixture
   `tests/fixtures/live-render/live-assets/template/main.php`). Разметка кейсов —
   `article.lx-case` / `.lx-case-hero` из `lireate_cases_render()` на хостинге;
   установщик вставляет **условный** head (только `/portfolio` и 14 slug), не на весь сайт.
   Если на хостинге отличается — **не apply**; сначала доработать точку подключения в PR.
2. PR-бранч скачать в **отдельную worktree/папку**, не трогая рабочий checkout CRON.
3. Запустить:
   `python3 scripts/test_lireate_portfolio_v5_install.py`
   Дополнительно: `node --check template/js/lireate-portfolio-v5.js`,
   проверить CSS в браузере.
4. Выполнить **dry-run**:
   `python3 scripts/lireate_portfolio_v5_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/portfolio-v5"`
   В плане допускаются **только** `template/main.php` (фрагмент `lireate-portfolio-v5-20261126`)
   и два новых ассета CSS/JS.
5. После явного подтверждения публикации:
   `python3 scripts/lireate_portfolio_v5_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/portfolio-v5" --apply --php-bin php`
   (если PHP CLI не в PATH, указать путь к рабочему PHP).
6. Запустить браузерный smoke-test каждого из 15 URL, включая /portfolio без слеша,
   детали/сертификаты (в частности Bentax — **две** записи), видео, якоря формы,
   телефонные ссылки, мессенджеры, внешние ссылки, cookies/consent, цели Метрики.
   Проверить ширины 390, 768, 1280, 1440, 1920, горизонтальный overflow и CSP.
7. Проверить, что старый HTML не дублируется, H1 ровно один, метаданные/canonical
   не изменились, изображения товарных знаков не заменены и не сломаны.
8. Убедиться, что CRON не перезаписывает ассеты или ссылку в main.php. Если перезаписывает —
   устранить конфликт в процессе деплоя без отключения автоматизации.
9. При критической ошибке — `--rollback <путь-к-выданному-каталогу-бэкапа>` с тем же `--docroot`.
   Backup находится **вне** DOCROOT и содержит исходные файлы.

## Важные ограничения

- Изменения **не опубликованы** на lireate.ru до работы установщика.
- Не менять `data/db.php`, `data/lireate-cases-lib.php`, записи свидетельств, SEO,
  внешние видео, действующие формы, согласие на обработку данных, меню или аналитику.
- Не запускать merge в main без явного согласования.
- Скриншоты *после* нужно делать только с настоящего сайта после публикации, а не
  выдавать местный макет за продакшен.
