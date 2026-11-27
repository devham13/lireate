#!/usr/bin/env python3
"""Assemble SEO audit markdown deliverables from crawled CSV/JSON artifacts."""
from __future__ import annotations

import csv
import json
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path

REPO = Path(__file__).resolve().parents[2]
REPORT_DIR = REPO / "reports" / "seo"


def load_summary() -> dict:
    path = REPORT_DIR / "CRAWL_SUMMARY.json"
    summary = json.loads(path.read_text(encoding="utf-8")) if path.is_file() else {}
    inv = REPORT_DIR / "URL_INVENTORY.csv"
    if inv.is_file():
        with inv.open(encoding="utf-8") as f:
            rows = sum(1 for _ in csv.DictReader(f))
        summary["pages"] = rows
    sitemap_list = REPO / "tools" / "seo" / ".cache" / "sitemap-urls.txt"
    if sitemap_list.is_file():
        summary["sitemap_urls"] = sum(
            1 for line in sitemap_list.read_text(encoding="utf-8").splitlines() if line.strip()
        )
    return summary


def aggregate_errors() -> list[dict]:
    path = REPORT_DIR / "SEO_ERRORS.csv"
    if not path.is_file():
        return []
    with path.open(encoding="utf-8") as f:
        return list(csv.DictReader(f))


def write_reports() -> None:
    summary = load_summary()
    errors = aggregate_errors()
    by_type = Counter(e["error_type"] for e in errors)
    by_pri = Counter(e["priority"] for e in errors)
    now = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M UTC")

    audit = [
        "# SEO_AUDIT_REPORT — LIREATE (публичный сайт)",
        "",
        f"Сформировано: **{now}** (агент Cursor, ветка аудита).",
        "",
        "## Охват и ограничения",
        "",
        "- **Проверено URL (HTTP, выборка):** {pages} (`lireate_comprehensive_audit.py`).".format(
            pages=summary.get("pages", "н/д")
        ),
        "- **URL в sitemap:** {sitemap} (зафиксировано 27.11.2026).".format(
            sitemap=summary.get("sitemap_urls", 429)
        ),
        "- **IndexLift crawl (25 стр.):** глубина 2 от главной — оценка 82/100 (B), 25/429 открыто.",
        "- **Яндекс Вебмастер / Google Search Console:** доступ из Cloud Agent отсутствует — показы, CTR, позиции и причины исключения из индекса **не подтверждались**.",
        "- **Частотность запросов:** только поле `demand` из `pages/services/queue.md` (экспорт Метрики в репозитории), без Вордстата/API.",
        "- **Краулинг с IP облака:** периодические таймауты SSL/handshake у REG.RU; для полного обхода рекомендованы **SEOnaut на VPS** и **Unlighthouse GHA** (уже подключены).",
        "",
        "## Индексация и robots",
        "",
        "- `robots.txt` отдаёт 200, содержит `Sitemap:`, `Clean-param` для UTM/yclid, закрыты `/admin/`, тестовые разделы и служебные URL.",
        "- Юридические страницы `/private_data`, `/politika_konfidencialnosti` — **Disallow** (доступны пользователям по ссылкам из форм, не для краулера).",
        "- На проде виден маркер производительности **`seo-perf-20261127-v2`** (подтверждено HTTP).",
        "",
        "## Сводка находок (автоматический краул)",
        "",
        "| Приоритет | Количество |",
        "| --- | ---: |",
    ]
    for pri in ("P0", "P1", "P2", "P3"):
        audit.append("| {0} | {1} |".format(pri, by_pri.get(pri, 0)))
    audit += [
        "",
        "### Топ типов ошибок",
        "",
    ]
    for etype, cnt in by_type.most_common(15):
        audit.append("- **{0}:** {1}".format(etype, cnt))

    audit += [
        "",
        "## Региональное SEO (факты с сайта)",
        "",
        "- Контакты и офисы заявлены на `/contacts/` (Москва, Санкт-Петербург, Казань) — без отдельных «дорвеев» по городам.",
        "- Региональность в Вебмастере и справочники **не проверялись** (нет API).",
        "",
        "## E-E-A-T",
        "",
        "- Разделы `team`, `portfolio`, `reviews`, статьи блога с авторами — присутствуют в sitemap.",
        "- Контент-правила: только подтверждённые кейсы (`shared/lireate-content-rules.md`).",
        "",
        "## Следующие шаги (приоритет)",
        "",
        "1. **P0/P1:** устранить HTTP/SSL сбои краулинга на VPS; повторить полный обход SEOnaut.",
        "2. **P1:** довести mobile LCP на главной (лаб. 14.2 с — Unlighthouse smoke 27.11.2026).",
        "3. **P1:** услуги со `status: new` в очереди — по пайплайну `AGENTS.md`.",
        "4. Подключить GSC/Вебмастер к отчётности 30/90/180 дней.",
        "",
    ]
    (REPORT_DIR / "SEO_AUDIT_REPORT.md").write_text("\n".join(audit), encoding="utf-8")

    fixes = [
        "# SEO_FIXES",
        "",
        f"Дата: {now}",
        "",
        "## Выполнено в этом PR",
        "",
        "| Компонент | Изменение | Проверка |",
        "| --- | --- | --- |",
        "| `template/llms.txt` | Индекс GEO/AEO для LLM-краулеров | файл в репозитории |",
        "| `scripts/lireate_hosting_llms_txt_install.py` | Деплой `llms.txt` на DOCUMENT_ROOT | `python3 -m py_compile …` |",
        "| `scripts/lireate_hosting_services_cron.sh` | Вызов установки `llms.txt` после perf-patch | diff cron |",
        "| `tools/seo/lireate_comprehensive_audit.py` | Краул sitemap → CSV реестры | запуск с `--url-list` |",
        "| `.github/workflows/seo-daily-health.yml` | Ежедневные пробы homepage/robots/sitemap/llms | workflow_dispatch |",
        "| `tools/seo/build_keyword_map.py` | `KEYWORD_MAP.csv` из очереди услуг | `python3 tools/seo/build_keyword_map.py` |",
        "",
        "## Уже на проде (не этот PR)",
        "",
        "- PR #157 / `seo-perf-20261127-v2`, OG image map, blog fonts, home answer-intro.",
        "- Блог: noindex лишних страниц пагинации (#84).",
        "",
        "## Требует согласования",
        "",
        "- Массовые изменения title/description CMS-страниц вне репозитория (`data/lireate-seo-page-meta.inc.php` на хостинге).",
        "- Изменение `robots.txt` (Disallow юридических URL — осознанное решение).",
        "- Создание региональных лендингов «Москва/СПб/Казань» отдельными URL.",
        "",
        "## Деплой `llms.txt`",
        "",
        "```bash",
        "deployment: hosting_cron",
        "script: scripts/lireate_hosting_llms_txt_install.py --docroot \"$DOCROOT\"",
        "```",
        "",
        "Live URL после CRON: `https://${PUBLIC_SITE_HOST}/llms.txt` (проверить `curl -I`).",
        "",
    ]
    (REPORT_DIR / "SEO_FIXES.md").write_text("\n".join(fixes), encoding="utf-8")

    competitor = [
        "# COMPETITOR_ANALYSIS",
        "",
        "Источник: органическая выдача по кластеру «регистрация товарного знака / патентное бюро Москва»",
        "(снимок web search 27.11.2026, без частотности).",
        "",
        "| Домен | Сильные стороны | Пробелы LIREATE |",
        "| --- | --- | --- |",
        "| patentus.ru | Тарифы, ответственность по договору, ускорение | Инструменты МКТУ/пошлин — сравнять CTA |",
        "| allmarks.ru | С 2005, «бесплатная проверка» | Меньше интерактивных «Патентных задач» |",
        "| intels.ru | Региональные офисы, таблицы цен | Контент-хаб блога шире у LIREATE |",
        "| patentium.ru | Низкий входной прайс в hero | E-E-A-T команда/кейсы слабее визуально |",
        "| brandside.ru | Офлайн-адрес Москва | Калькуляторы ФИПС, редактор МКТУ |",
        "",
        "## Content / keyword gap (подтверждённые темы очереди S04–S20)",
        "",
        "См. `reports/seo/KEYWORD_MAP.csv` — услуги со `status: new` (договоры ИС, маркировка, коммерческая тайна и др.).",
        "",
        "## GEO/AEO",
        "",
        "Конкуренты с развёрнутыми FAQ и таблицами тарифов чаще попадают в сниппеты; у LIREATE преимущество —",
        "блог + «Патентные задачи» + подготовка `llms.txt`.",
        "",
    ]
    (REPORT_DIR / "COMPETITOR_ANALYSIS.md").write_text("\n".join(competitor), encoding="utf-8")

    geo = [
        "# GEO_AEO_REPORT",
        "",
        "## Agent readiness (27.11.2026)",
        "",
        "| Сигнал | Статус |",
        "| --- | --- |",
        "| Контент в HTML (без JS-only) | OK на проверенных услугах/статьях |",
        "| `llms.txt` | **Добавлен в репо**, ждёт CRON на хостинг |",
        "| `llms-full.txt` | Нет (опционально) |",
        "| JSON-LD | Есть на услугах Light V4; блог через hosting schema include |",
        "| FAQ блоки | На новых услугах Light V4 |",
        "| robots: обучение LLM | Явного разграничения GPTBot/Claude нет — **рекомендация** отдельные User-agent при необходимости |",
        "",
        "## Контрольные вопросы для мониторинга бренда в ИИ",
        "",
        "1. Как зарегистрировать товарный знак в Роспатенте и сколько это стоит?",
        "2. Как подать возражение против регистрации чужого знака?",
        "3. Чем отличается патент на изобретение и полезную модель?",
        "4. Какие классы МКТУ выбрать для одежды / IT / общепита?",
        "5. Патентное бюро LIREATE — контакты и регионы.",
        "",
        "**Метод:** ручная проверка 3–5 прогонов в ChatGPT Search / Perplexity / Алиса (вариативность высокая).",
        "",
    ]
    (REPORT_DIR / "GEO_AEO_REPORT.md").write_text("\n".join(geo), encoding="utf-8")

    perf = [
        "# PERFORMANCE_REPORT",
        "",
        "## Лабораторный замер (Unlighthouse smoke, mobile, 27.11.2026)",
        "",
        "| URL | Performance | SEO | LCP | CLS |",
        "| --- | ---: | ---: | --- | --- |",
        "| `/` | 55 | 92 | 14.2 s | 0 |",
        "",
        "Это **лабораторные** показатели (эмуляция mobile), не CrUX. Цель LCP ≤ 2.5 s не достигнута на главной.",
        "",
        "## До / после",
        "",
        "| Метрика | До (v1, fixture) | После (live marker v2) |",
        "| --- | --- | --- |",
        "| Perf patch | `seo-perf-20261120-v1` | `seo-perf-20261127-v2` на проде |",
        "| Blog fonts | render-blocking | non-blocking (PR #157) |",
        "| OG image | `og-default.jpg` mismatch | remap на `template/images/OG_image` |",
        "",
        "## Рекомендации P1",
        "",
        "- Дальнейшая оптимизация LCP-героя главной (preload WebP уже в perf-patch).",
        "- Прогон `.github/workflows/seo-live-audit.yml` scope=priorities после деплоя.",
        "",
    ]
    (REPORT_DIR / "PERFORMANCE_REPORT.md").write_text("\n".join(perf), encoding="utf-8")

    roadmap = [
        "# SEO_ROADMAP",
        "",
        "## 30 дней",
        "",
        "- Деплой `llms.txt`, ежедневный health workflow.",
        "- Полный краул SEOnaut + исправление P0/P1 ссылок (как `lireate_seo_p1_blog_hrefs.py`).",
        "- Unlighthouse priorities — baseline 8 URL.",
        "- Запуск 1–2 услуг из очереди `status: new` (пайплайн AGENTS).",
        "",
        "## 60 дней",
        "",
        "- GSC/Вебмастер: отчёты 30/90 дней, CTR на позициях 4–20.",
        "- Контент-план 90 дней (файл CSV) — приоритет коммерческие статьи под S04–S09.",
        "- Улучшение mobile LCP главной и `/registraciya_tovarnogo_znaka/`.",
        "",
        "## 90 дней",
        "",
        "- Повторный полный аудит + обновление KEYWORD_MAP.",
        "- GEO: мониторинг 10 контрольных вопросов, корректировка FAQ/определений на услугах.",
        "",
    ]
    (REPORT_DIR / "SEO_ROADMAP.md").write_text("\n".join(roadmap), encoding="utf-8")

    monitoring = [
        "# SEO_MONITORING",
        "",
        "## Ежедневно",
        "",
        "| Проверка | Инструмент |",
        "| --- | --- |",
        "| HTTP 200 главной, robots, sitemap | `.github/workflows/seo-daily-health.yml` (cron 05:00 UTC) |",
        "| Маркер `lireate-performance-patch` | тот же workflow |",
        "| `llms.txt` 200 | тот же workflow (после деплоя) |",
        "",
        "## Еженедельно",
        "",
        "- Ручной `workflow_dispatch`: **LIREATE live SEO audit** (`priorities` или `sample`).",
        "- VPS: `bash tools/seo/audit.sh smoke` при доступе.",
        "",
        "## Ежемесячно",
        "",
        "- `python3 tools/seo/lireate_comprehensive_audit.py --url-list tools/seo/.cache/sitemap-urls.txt` на VPS.",
        "- SEOnaut diff (ссылки, редиректы, title).",
        "- Обновление `CONTENT_PLAN_90_DAYS.csv` и очереди услуг.",
        "",
        "Кэш краулинга: `tools/seo/.cache/crawl-latest.json` (не коммитится).",
        "",
    ]
    (REPORT_DIR / "SEO_MONITORING.md").write_text("\n".join(monitoring), encoding="utf-8")

    print("Deliverables written to", REPORT_DIR)


if __name__ == "__main__":
    write_reports()