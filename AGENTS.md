# LIREATE Office — Cloud Instructions

Язык работы: русский.

Сайт: **https://${PUBLIC_SITE_HOST}** — самописная PHP CMS на REG.RU, **не WordPress**.

## Главное правило

Для полной страницы услуги-лонгрида **нельзя** выполнять ролевую работу одним Cloud Agent.

Текущий Cloud Agent — **Директор-оркестратор**. `director` не вызывать как отдельный Task.

### Пайплайн страницы услуги (основной)

`seo-kolya || artyom → zhenya → artur → alina || boris → natasha → schema-markup → internal-linker → yura → indexator → qa → lenya`

- Тему услуги задаёт пользователь, automation prompt или **первая** строка `pages/services/queue.md` со `status: new` без каталога `pages/services/<slug>/` (см. `python3 scripts/lireate_service_queue.py`). **Кирилл (новости дня) в этом пайплайне не используется.**
- **google-table-manager** — **отключён** (нет Google Таблицы тем для LIREATE).
- **vk-publisher** — **отключён** (не в scope услуг).

Если Cloud API не принимает имена ролей как `Task` types — fallback через отдельные `generalPurpose` Task (см. `agents/director.md`).

Если недоступен даже `generalPurpose` Task:

`❌ БЛОКЕР: Cloud Agent не может запускать отдельные Task/subagents даже через generalPurpose. Не выполняю single-agent pipeline.`

## Дизайн и артефакт страницы

- **Все новые страницы услуг** — только стандарт LIREATE: `docs/lireate-service-design.ru.md`, общие ассеты `pages/services/_shared/`, шаблон `pages/services/_template/`, skill `skills/lireate-service-page/SKILL.md`. Эталон: `pages/services/registraciya-tovarnogo-znaka-v-kitae/`.
- Визуал: тёмный navy, плавающая nav-pill, hero + стеклянная панель, карточки `lx-*` — **не** дизайн текущего шаблона CMS и **не** legacy WordPress-longread (другой стек, не для услуг).
- Страница **самодостаточная**: `assets/lireate-shared/*` (копия из `_shared`) + локальные `assets/` при необходимости; не зависит от CSS CMS.
- Канонический путь: `pages/services/<slug>/` (`index.php`, `publish.manifest.json`, `.htaccess` при необходимости). См. `pages/services/README.md`.
- SEO: `shared/lireate-service-seo-checklist.md`; перед коммитом — `scripts/lireate_service_sync_shared.py` и `scripts/lireate_service_lint.py --slug …`.
- Запросы и приоритеты: `pages/services/queue.md` (demand из Метрики); при наличии — `data/seo-report.json` в **devham13/lireate-news**.

### Автоматизация service-pages (один прогон = одна услуга)

1. `python3 scripts/lireate_service_queue.py` → `Sxx` + `slug` или `NO_NEW_SERVICE` (тогда **стоп**: без страницы, без PR).
2. Ровно **одна** новая услуга: копия `pages/services/_template/` → `pages/services/<slug>/`, ассеты из `_shared` (`lireate_service_sync_shared.py`).
3. `python3 scripts/lireate_service_lint.py --slug <slug>` — обязателен pass.
4. В `pages/services/queue.md` у этой записи `status: done` **после merge в `main`**; **не** править уже существующие каталоги услуг и **не** трогать `data/db.php`.
5. PR в `main`: Cloud Agent **сам вливает** PR, когда lint и проверки зелёные (владелец разрешил автопубликацию услуг без ручного approve).
6. В течение ~30 минут REG.RU CRON (`scripts/lireate_hosting_services_cron.sh`) публикует новый slug; `published` в очереди/state на хостинге — не задача Cloud.

## Публикация (критично для Cloud)

**Cursor Cloud не публикует на хостинг:** RCPC блокирует IP облака, FTP/SSH таймауты. Админку с капчей не обходить.

**Юра в Cloud** только:

1. Собирает каталог `pages/services/<slug>/`.
2. Вставляет schema и внутренние ссылки.
3. Обновляет `shared/published-pages.md` со статусом `ready_for_hosting` и ожидаемым URL.
4. Коммитит, пушит, доводит PR до merge в `main`.
5. Пишет блок `=== ЮРА (ПУБЛИКАЦИЯ) ===` с `deployment: hosting_cron`, **без** вымышленного live URL.

Реальная выкладка на прод: CRON на REG.RU — `scripts/lireate_hosting_services_cron.sh` → `lireate_hosting_publish_service.py` и при `LIREATE_ALLOW_MENU_EDIT=yes` — `lireate_hosting_menu_add.py` (см. `docs/lireate-services-hosting.ru.md`). Новости — отдельно через `lireate-news` / MySQL.

## Что считать ошибкой

- Parent Agent сам пишет лонгрид / hero / блок Бориса / вёрстку / schema / перелинковку вместо отдельных Task.
- Cloud Agent пытается SSH/FTP на lireate.ru из облака.
- Cloud Agent пишет «опубликовано» с live URL без факта запуска хостинг-скрипта.
- Короткая статья вместо лонгрида 8k–20k+ знаков.
- Услуга без `lx-nav-pill` / `_shared` CSS или с проваленным `lireate_service_lint.py`.
- Контент с выдуманными кейсами/судами (см. `shared/lireate-content-rules.md`).

## Handoff

- `.cursor/lireate-handoff.md`
- `.cursor/lireate-fragments/`

(Допустим legacy-путь `.cursor/nero-network-handoff.md` — не коммитить.)

Маркеры:

- `=== КОЛЯ (SEO-ЯДРО) ===`
- `=== АРТЁМ (RESEARCH) ===`
- `=== ЖЕНЯ (ЛОНГРИД) ===`
- `=== АРТУР (CTA И РЕКЛАМА) ===`
- `=== АЛИНА (HERO) ===`
- `=== БОРИС (БЛОК СТАТЬИ, НЕ HERO) ===`
- `=== НАТАША (HTML СТРАНИЦЫ) ===`
- `=== SCHEMA-MARKUP ===`
- `=== INTERNAL-LINKER ===`
- `=== ЮРА (ПУБЛИКАЦИЯ) ===`
- `=== INDEXATOR ===`
- `=== МАКС (QA) ===`
- `=== ЛЁНЯ (SEO-АУДИТ) ===`

Отключённые маркеры (не ждать в пайплайне услуг): `GOOGLE-TABLE-MANAGER`, `КИРИЛЛ`, `VK-PUBLISHER`.

## Автономный режим

- Не спрашивать подтверждение на commit/push артефакта, QA по файлам, SEO-аудит локально.
- Доводить до **готового PR/коммита** и блока Юры или **блокера**.
- После Юры: **indexator** (если URL ещё нет — режим `pre_publish`), **qa**, **lenya** по HTML в репозитории; при появлении live URL владелец может перезапустить indexator на хостинге.
- Секреты только из env; не печатать в ответах и handoff.

## Git hygiene

Runtime handoff/fragments **не коммитить**.

В коммит: `pages/services/<slug>/`, `pages/services/queue.md`, правила, `shared/published-pages.md` при изменении.
