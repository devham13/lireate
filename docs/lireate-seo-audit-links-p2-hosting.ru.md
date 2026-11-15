# SEO-аудит 15.11.2026 — очередь 04 (ссылки P2)

Манифест: `data/seo-audit-2026-11-15/link-replacements-p2.json` (43 правила с приоритетом P2 из листа «Ссылки»).

Применять **после** очереди 01 (PR #77, P1) на том же DOCROOT. Скрипт тот же: `lireate_seo_audit_links_install.py` с флагом `--queue p2`.

```bash
export LIREATE_DOCROOT=$HOME/www/public_html
python3 scripts/lireate_seo_audit_links_install.py --queue p2 --check
python3 scripts/lireate_seo_audit_links_install.py --queue p2 --apply
```

Office-repo (только если в репозитории остались legacy href из P2):

```bash
python3 scripts/lireate_seo_audit_links_install.py --scope office --queue p2 --office-sync --include-html
```

Не трогает `data/db.php`. Бэкап — в `~/lireate-autopub/backups/seo-audit-links/<timestamp>/`.