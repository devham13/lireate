# LIREATE SEO VPS (`/opt/lireate-seo`)

Продолжение установки на **Ubuntu 24.04 amd64** (2 vCPU / 4 ГБ), когда уже работают Docker, MySQL и SEOnaut.

## Быстрый старт (продолжение)

```bash
sudo bash /opt/lireate-seo/office-repo/tools/seo/vps/install-continue.sh
```

Скрипт:

- обновляет `office-repo` (GitHub `devham13/lireate`);
- **не** пересоздаёт пароли в `tools/seo/.env`, если файл уже есть;
- поднимает **кабинет** Caddy на `127.0.0.1:19080` (Basic Auth);
- не трогает уже запущенные тома MySQL, если Compose project совпадает;
- при запуске от root — пишет `cron` для Unlighthouse / SiteOne / IndexLift / GitHub dispatch.

Полный аудит вручную:

```bash
cd /opt/lireate-seo/office-repo/tools/seo
bash vps/run-audit-all.sh all
```

## Компоненты

| Сервис | Назначение |
|--------|------------|
| SEOnaut + MySQL | Краул (уже был) |
| Unlighthouse | Lighthouse mobile, отчёты в `unlighthouse/reports/` |
| SiteOne Crawler | Технический JSON + HTML отчёт |
| IndexLift | Google + Yandex (skill в репозитории) |
| Caddy cabinet | Единая веб-страница + раздача отчётов + proxy `/seonaut/` |

## SSH (безопасность)

После **рабочего входа по ключу** на VPS:

```bash
sudo bash /opt/lireate-seo/office-repo/tools/seo/vps/ssh/apply-hardening.sh
```

Для Cloud Agent на VPS `89.223.121.172` добавьте в `~/.ssh/authorized_keys` публичный ключ из секрета `LIREATE_SSH_KEY`, затем:

```bash
python3 /opt/lireate-seo/office-repo/tools/seo/vps/remote-apply.py
```

## ИИ-агент через GitHub

Workflow [`.github/workflows/seo-fix-agent.yml`](../../.github/workflows/seo-fix-agent.yml) слушает `repository_dispatch` (`lireate-seo-audit-fix`).

На VPS задайте `GITHUB_TOKEN` или `LIREATE_GITHUB_TOKEN` (fine-grained: `actions:write`, `issues:write`) и cron вызовет `vps/agent/dispatch-github-fix.sh`.

## Доступ к UI

- SSH-туннель: `ssh -L 19080:127.0.0.1:19080 -L 19000:127.0.0.1:19000 USER@VPS`
- Кабинет: `http://127.0.0.1:19080/` (логин из вывода `install-continue.sh`)

Не открывайте SEOnaut и кабинет в интернет без TLS и сильной аутентификации.