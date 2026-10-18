# LIREATE Office: Cloud Automation

Автоматизация готовит **новые страницы услуг** в репозитории `devham13/lireate`; выкладка на https://${PUBLIC_SITE_HOST} — скриптом на хостинге REG.RU.

## Пайплайн

`Коля||Артём → Женя → Артур → Алина||Борис → Наташа → schema-markup → internal-linker → Юра → indexator → Макс → Лёня`

**Не используются:** google-table-manager, kirill (новости), vk-publisher.

## Ограничение Cloud

Из Cursor Cloud **нет** доступа к lireate.ru (RCPC, FTP). Юра **не деплоит**; только commit `pages/services/<slug>/`. Инструкция владельцу: `docs/lireate-services-hosting.ru.md`.

## Канонические файлы

- Handoff: `<PROJECT_ROOT>/.cursor/lireate-handoff.md`
- Фрагменты: `<PROJECT_ROOT>/.cursor/lireate-fragments/`
- Очередь услуг: `<PROJECT_ROOT>/shared/lireate-services-queue.md`
- Журнал: `<PROJECT_ROOT>/shared/published-pages.md`
- Правила контента: `<PROJECT_ROOT>/shared/lireate-content-rules.md`
- Хостинг-скрипт: `<PROJECT_ROOT>/scripts/lireate_hosting_publish_service.py`

## Secrets / env (Cloud)

Минимум для контента и CTA:

- `SITE_BRAND=LIREATE`
- `PUBLIC_SITE_URL=https://${PUBLIC_SITE_HOST}`
- `PUBLIC_SITE_HOST=lireate.ru`
- `PRIMARY_CTA_LABEL`, `PRIMARY_CTA_URL` (форма/контакты на lireate.ru)
- `SECONDARY_CTA_*` — опционально

**Не нужны** для Cloud-пайплайна услуг: `WP_*`, `FTP_*`, `SSH_*`, `GOOGLE_SHEET_*`, VK-токены.

## Worker

Обычный Cloud Agent достаточен: WebSearch, файлы, commit. Self-hosted worker нужен только если требуется Wordstat MCP или браузерный QA с доступом к live-сайту.

## Automation prompt (шаблон)

```text
Репозиторий LIREATE Office (devham13/lireate). Новая страница услуги юрфирмы.

Тема услуги: <название> (товарные знаки / ИС / авторское право / патенты / арбитраж).

Директор-оркестратор: роли только через Task / generalPurpose. Не выполняй роли сам.

0. Сбрось .cursor/lireate-handoff.md одной строкой "# LIREATE — новая сессия" и очисти .cursor/lireate-fragments/.
1. seo-kolya || artyom → zhenya → artur → alina || boris → natasha → schema-markup → internal-linker → yura.
2. Контент: shared/lireate-content-rules.md. Лонгрид 8k–20k+ знаков.
3. Дизайн из репозитория, самодостаточная страница в pages/services/<slug>/.
4. Юра: commit/push, статус ready_for_hosting, НЕ SSH/FTP. deployment: hosting_script.
5. indexator → qa → lenya по файлам в repo; live URL проверяет владелец после хостинг-скрипта.

Финал: slug, путь pages/services/, ожидаемый URL https://${PUBLIC_SITE_HOST}/uslugi/<slug>/, команда для Shell на хостинге (dry-run и --apply) или блокер.
```

## После merge PR

Владелец на REG.RU: `git pull` в `$HOME/lireate-autopub/office-repo`, dry-run, затем `--apply` с `LIREATE_ALLOW_PUBLISH=yes`.
