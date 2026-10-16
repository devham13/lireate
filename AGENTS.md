# Nero Network Office Page Cloud Instructions

Язык работы: русский.

## Главное правило

Для полной страницы-лонгрида **нельзя** выполнять ролевую работу одним Cloud Agent.

Текущий Cloud Agent работает как **Директор-оркестратор**. `director` — это не отдельный субагент пайплайна, поэтому **не вызывай отдельный Task или команду с именем director**.

Директор запускает роли:

`google-table-manager → kirill → seo-kolya || artyom → zhenya → artur → alina || boris → natasha → schema-markup → internal-linker → yura → google-table-manager → indexator → qa → lenya → vk-publisher`

`google-table-manager` запускается **дважды**: фаза `reserve` (до Кирилла) и фаза `publish` (после Юры, запись URL в таблицу).

Если Cloud API не принимает имена `kirill`, `seo-kolya`, `artyom` и т.д. как `Task` types, используй **fallback через отдельные `generalPurpose` Task**:

- каждый `generalPurpose` Task получает **короткий контракт роли**: имя роли, входной файл/фрагмент, точный маркер результата, запреты и путь к `agents/<role>.md`;
- если у роли есть skill, передай **путь** `skills/<skill>/SKILL.md` и только критичные выдержки для текущего шага; полный текст skill не копируй без необходимости;
- один `generalPurpose` Task = одна роль, один блок handoff/fragment;
- параллельные пары всё равно запускаются отдельными Task, а не выполняются parent-agent.

Если недоступен даже `generalPurpose` Task или среда вообще не даёт запускать отдельные Task, нужно **остановиться с блокером**:

`❌ БЛОКЕР: Cloud Agent не может запускать отдельные Task/subagents даже через generalPurpose. Не выполняю single-agent pipeline.`

## Что считать ошибкой

- Parent Cloud Agent сам пишет лонгрид вместо отдельного Task `zhenya` / `generalPurpose` с ролью Жени.
- Parent Cloud Agent сам делает hero вместо отдельного Task `alina` / `generalPurpose` с ролью Алины.
- Parent Cloud Agent сам делает блок статьи вместо отдельного Task `boris` / `generalPurpose` с ролью Бориса.
- Parent Cloud Agent сам верстает страницу вместо отдельного Task `natasha` / `generalPurpose` с ролью Наташи.
- Parent Cloud Agent сам вставляет рекламу вместо отдельного Task `artur` / `generalPurpose` с ролью Артура.
- Parent Cloud Agent сам генерирует JSON-LD вместо отдельного Task `schema-markup` / `generalPurpose` с ролью schema-markup.
- Parent Cloud Agent сам подбирает внутренние ссылки вместо отдельного Task `internal-linker` / `generalPurpose` с ролью internal-linker.
- Cloud Agent публикует без блока `=== ЮРА (ПУБЛИКАЦИЯ) ===`.
- Cloud Agent создаёт короткую статью вместо лонгрида 8k–20k+ знаков.

## Handoff

Данные между ролями идут через:

- `.cursor/nero-network-handoff.md`
- `.cursor/nero-network-fragments/`

Параллельные роли пишут только во фрагменты:

- `google-table-manager.md`
- `indexator.md`
- `kolya.md`
- `artyom.md`
- `alina.md`
- `boris.md`
- `schema-markup.md`
- `internal-linker.md`
- `qa.md`
- `lenya.md`
- `vk-publisher.md`

Директор переносит фрагменты в handoff и проверяет маркеры:

- `=== GOOGLE-TABLE-MANAGER ===`
- `=== КИРИЛЛ (НОВОСТЬ ДНЯ) ===`
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
- `=== VK-PUBLISHER ===`

## Публикация

Юра публикует только через SSH/SCP/SFTP/FTP как `page-{slug}.php` в активную тему `${WP_THEME_SLUG}`.
В Cursor Cloud сначала использовать SSH/SCP/SFTP; FTP использовать только если SSH/SCP/SFTP недоступны.
WordPress API / REST API / MCP blob flow для страниц с `<script>` и `<canvas>` запрещён.

**SSH_HOST / FTP_HOST:** используй **домен сайта** из `WP_SITE_URL` (целевой прод: `lireate.ru` на REG.RU), не IP панели хостинга — у IP часто закрыты порты 21/22. Доступы Beget / `meta-journal.ru` в env и в репозитории не использовать.

## Автономный режим (без подтверждений)

- **Не спрашивай** у пользователя подтверждение на: deploy, SSH/FTP, `pip`/`npm install`, QA в браузере/curl, SEO-аудит, повторную публикацию, правку env/шаблона после аудита.
- **Не останавливайся** на «могу продолжить?» / «разрешите выполнить?» — доводи пайплайн до **URL** или **блокера с причиной**.
- Если **Task/subagent прерван** (timeout, interrupt): директор **сам** завершает этап инструментами (`deploy.py`, curl, IndexLift, правка PHP) и при необходимости перепубликует; затем пишет фрагменты `qa.md` / `lenya.md`.
- После **Юры** запускай **google-table-manager** (фаза `publish`), затем **indexator**, затем **Макс (QA)**, затем **Лёня**, затем **vk-publisher** (последовательно или инструментами, если Task недоступен).
- **vk-publisher** публикует пост ВК только если в тексте есть `public_url` из блока Юры/QA; без ссылки на страницу `wall.post` не вызывать.
- Если **indexator** вернул блокер (noindex, robots, HTTP ≠ 200, критичный canonical) — QA не должен пропускать страницу.

Секреты брать только из Cloud Secrets / env vars. Не печатать секреты в ответах, PR body, handoff или логах.

## Git hygiene

`.cursor/nero-network-handoff.md` и `.cursor/nero-network-fragments/` — runtime-файлы пайплайна. Их можно создавать и читать во время автоматизации, но **нельзя коммитить** в Git/PR.

В коммит должны попадать только устойчивые артефакты:

- обновления правил/skills/agents;
- журналы `shared/published-pages.md`, `shared/session-handoff.md`, `shared/kirill-news-ledger.md`, `shared/vk-posts-ledger.md`;
- если страница опубликована, обязательно шаблон `wordpress-theme/page-{slug}.php` или другой воспроизводимый файл шаблона, принятый в этом репозитории.
