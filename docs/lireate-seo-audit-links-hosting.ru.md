# SEO-аудит 15.11.2026 — очередь 01 (ссылки и навигация)

Манифест: `data/seo-audit-2026-11-15/link-replacements.json` (лист «Ссылки» + `/reg-tz-main` → `/registraciya_tovarnogo_znaka/` без редиректа старого URL).

Скрипт: `scripts/lireate_seo_audit_links_install.py` (+ `lireate_seo_audit_links_lib.py`).

## Проверка (office-repo или DOCROOT)

```bash
python3 scripts/lireate_seo_audit_links_install.py --scope office --check --include-html
export LIREATE_DOCROOT=/path/to/public_html
python3 scripts/lireate_seo_audit_links_install.py --check
```

## Деплой на REG.RU (не Cloud Agent)

```bash
export LIREATE_DOCROOT=$HOME/www/public_html
python3 scripts/lireate_seo_audit_links_install.py --check
python3 scripts/lireate_seo_audit_links_install.py --apply
```

- Обходит все `*.php` в DOCROOT.
- **Не изменяет:** `data/db.php`, корневой `.htaccess`.
- Перед записью — бэкап в `~/lireate-autopub/backups/seo-audit-links/<timestamp>/`.

После `--apply` имеет смысл переобходить в Вебмастере ключевые URL из P1 (главная, `/mktu`, услуги, статьи из манифеста).

## Office-repo (страницы услуг)

```bash
python3 scripts/lireate_seo_audit_links_install.py --office-sync --include-html
```

Обновляет href в `pages/services/*`, `_shared`, `scripts/fixtures`, backup HTML — отдельно от деплоя услуг.