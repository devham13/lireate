# LIREATE — QA V6: интерфейсные исправления 26.11.2026

Ветки и PR содержат исходники, а не публикацию на REG.RU.

## Задачи

- /contacts/: убрать блок карты и скрипт Яндекс.Карт; дать 5 одинаковых бело-голубых карточек Telegram, MAX, WhatsApp, VK, YouTube с существующими ссылками и официальными SVG; сохранить формы, телефоны, адрес, согласия, аналитику.
- /partners: одинаковые компактные карточки: ФИО, город и фото в закрытом состоянии; раскрыть подробную деятельность, адрес, e-mail и телефоны через доступные нативные details/summary. Не обрезать контент.
- /news: вернуть фактические обложки статей, восстановив кандидаты из полной/краткой статьи и метаданных, создать img-элементы, поддержать legacy picture-обложки и восстановить оригинальный /upload/iblock/..., если файл /upload/resize_cache/ отсутствует. Если исходного изображения нет — только редакционный текстовый fallback, не выдумывать фото.
- /mktu: светлая стилистика LIREATE V4/V5 на существующем контенте; сохранить фильтры и классификатор.
- /patentnye-zadachi/ и 12 подстраниц: новый светлый стиль, общее левое меню, полный логотип, телефоны, соцсети, адаптивное мобильное меню. Все 13 standalone index.php подключены в репозитории; инструменты, SEO и формы остаются в исходном коде.
- /patent и /patent_prom_obr: скрыть только действительно пустой неинтерактивный блок перед футером; не трогать содержательные секции/формы.
- Футер: Реквизиты теперь ссылка /docs, не раскрывающаяся панель. Изменены шаблоны и fallback в JS.

## Почему пропадают миниатюры статей

На странице /news присутствует в том числе старая разметка .lireate-blog-list-card__img с адресами /upload/resize_cache/iblock/... . Проверить реальный HTTP-код, локальное наличие resize и оригинального /upload/iblock файла. Если оригинал существует, восстановление из него вместо пустой миниатюры. Если нет — найти файл в CMS, не придумывать контент.

## Исходники

- template/css/lireate-qa-v6.css — стили с ограничением по маршрутам/компонентам.
- template/js/lireate-qa-v6.js — управление sidebar, cover fallback, замена details футера, безопасное определение пустого блока.
- template/lireate/lireate-patent-tasks-sidebar-v6.inc.php — исходное меню CMS (с реальными href) для standalone.
- template/images/logo/logo-light-v5.svg — светлая копия оригинального векторного логотипа.
- Парные исходники pages/*/_shared и template/page/* для /contacts, /partners и /news.
- Все 13 pages/services/patentnye-zadachi*/index.php — CSS/JS и include меню.
- Изменены 4 канонических шаблона футера и добавлено исправление для старых вариантов через JS.
- scripts/lireate_qa_v6_install.py и scripts/test_lireate_qa_v6_install.py — dry-run/rollback и регрессионные тесты.

## Внедрение Grok/Cursor

1. Изучи AGENTS.md, docs/lireate-design-system-v4.ru.md, данный документ и 5 пользовательских скриншотов; проверь состояния PR 123/128/130/131. Подготовь worktree ветки PR, не трогая CRON checkout.
2. Сначала выясни действительный DOCROOT на REG.RU, точки подключения PHP-CMS и какие 13 standalone index.php соответствуют опубликованным адресам.
3. Тест: python3 scripts/test_lireate_qa_v6_install.py; node --check template/js/lireate-qa-v6.js; php -l для всех изменённых PHP. Открой превью на ширинах 320, 390, 768, 1440 и 1920.
4. Dry-run CMS без записи:
    python3 scripts/lireate_qa_v6_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/qa-v6"
5. Отдельно dry-run на 13 published tools после проверки путей:
    python3 scripts/lireate_qa_v6_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/qa-v6" --with-tools
5b. Если на проде контент идёт из pages/*/_shared (после PR #132 без shared), dry-run с:
    python3 scripts/lireate_qa_v6_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/qa-v6" --with-shared
    Совместим с параллельной вставкой PR #133 (блок lireate-sitewide-sidebar-v7-20261126 перед &lt;/head&gt; сохраняется при записи).
6. Если BLOCKER live PHP has newer/different modifications, НЕ обходить: адаптируй правки под свежую CMS, заново проверь. Не отключай guard и не перезаписывай данные.
7. Только после проверки и согласования применяй --apply с выбранным режимом и --php-bin; backup находится ВНЕ DOCROOT. При ошибках — --rollback с указанным каталогом.
8. QA: все 13 страниц патентных задач, /mktu, /contacts, /partners, /news, /patent, /patent_prom_obr, форма, карта удалена, 5 соцсетей, телефоны, меню, редактор МКТУ, расчёты, новые обложки по URL HTTP 200, JS console, CSP, мобильные экраны, SEO/H1/canonical, Yandex Metrika.
9. Убедись, что CRON не перезаписывает обновление. Дай скриншоты до/после, отчёт всех проверок и путь бэкапа.

## Ограничения

Никаких изменений data/db.php, URL, текста статей и кейсов, логики калькуляторов, согласий или событий аналитики. Без отдельного одобрения не объединять PR в main и не публиковать на REG.RU.
Важная оговорка: если медиафайлов вообще нет на сервере, нельзя честно считать восстановление миниатюр завершённым; зафиксируй проблему и предложи восстановление исходного файла.
Если реальная пустая секция патентной страницы не является прямым пустым дочерним узлом main, устранить конкретный элемент после DOM инспекции, не скрывая значимый контент.