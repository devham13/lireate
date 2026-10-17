# LIREATE Office — Cloud Instructions

Язык работы: русский.

Сайт: **https://${PUBLIC_SITE_HOST}** — самописная PHP CMS на REG.RU, **не WordPress**.

## Главное правило

Для полной страницы услуги-лонгрида **нельзя** выполнять ролевую работу одним Cloud Agent.

Текущий Cloud Agent — **Директор-оркестратор**. `director` не вызывать как отдельный Task.

### Пайплайн страницы услуги (основной)

`seo-kolya || artyom → zhenya → artur → alina || boris → natasha → schema-markup → internal-linker → yura → indexator → qa → lenya`

- Тему услуги задаёт пользователь, automation prompt или `shared/lireate-services-queue.md`. **Кирилл (новости дня) в этом пайплайне не используется.**
- **google-table-manager** — **отключён** (нет Google Таблицы тем для LIREATE).
- **vk-publisher** — **отключён** (не в scope услуг).

Если Cloud API не принимает имена ролей как `Task` types — fallback через отдельные `generalPurpose` Task (см. `agents/director.md`).

Если недоступен даже `generalPurpose` Task:

`❌ БЛОКЕР: Cloud Agent не может запускать отдельные Task/subagents даже через generalPurpose. Не выполняю single-agent pipeline.`

## Дизайн и артефакт страницы

- Визуал и вёрстка — **из этого репозитория** (`shared/longread-page-design-system.md`, `agents/natasha`, `agents/alina`, `agents/boris`, `assets/`, `shared/longread-page-reveal.js`), **не** дизайн текущего сайта lireate.ru.
- Страница **самодостаточная**: свои `<style>`, `<script>`, локальные `assets/`; не зависит от шаблона CMS.
- Канонический путь в git: `pages/services/<slug>/` (`index.php` + опционально `assets/`, `publish.manifest.json`). См. `pages/services/README.md`.

## Публикация (критично для Cloud)

**Cursor Cloud не публикует на хостинг:** RCPC блокирует IP облака, FTP/SSH таймауты. Админку с капчей не обходить.

**Юра в Cloud** только:

1. Собирает каталог `pages/services/<slug>/`.
2. Вставляет schema и внутренние ссылки.
3. Обновляет `shared/published-pages.md` со статусом `ready_for_hosting` и ожидаемым URL.
4. Коммитит и пушит в `devham13/lireate`.
5. Пишет блок `=== ЮРА (ПУБЛИКАЦИЯ) ===` с `deployment: hosting_script`, **без** вымышленного live URL.

Реальная выкладка на прод: владелец или CRON на REG.RU — `scripts/lireate_hosting_publish_service.py` (см. `docs/lireate-services-hosting.ru.md`). Новости — отдельно через `lireate-news` / MySQL.

## Что считать ошибкой

- Parent Agent сам пишет лонгрид / hero / блок Бориса / вёрстку / schema / перелинковку вместо отдельных Task.
- Cloud Agent пытается SSH/FTP на lireate.ru из облака.
- Cloud Agent пишет «опубликовано» с live URL без факта запуска хостинг-скрипта.
- Короткая статья вместо лонгрида 8k–20k+ знаков.
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

В коммит: `pages/services/<slug>/`, правила, `shared/published-pages.md`, `shared/lireate-services-queue.md` при изменении.
