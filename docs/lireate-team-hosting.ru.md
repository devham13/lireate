# Деплой redesign «Команда Лирейт» (/team/)

Страница **не** standalone: в CMS заменяется legacy-блок `#teamFace` + `#teams`. Глобальный `#personsSlide` в `template/main.php` **не трогаем** — на `/team/` он скрывается CSS.

## Подготовка (office-repo)

```bash
python3 scripts/lireate_team_sync_template.py
python3 scripts/lireate_team_install.py --check --docroot "$LIREATE_DOCROOT"
```

## Ручная выкладка (если installer не находит шаблон)

С машины с доступом к DOCROOT сайта:

| Источник (git) | Назначение на хостинге |
|----------------|------------------------|
| `pages/team/_shared/*` | `DOCROOT/pages/team/_shared/` |
| те же файлы | `DOCROOT/template/page/team/` |
| `pages/services/_shared/lireate-metrika-goals.js` | `DOCROOT/template/page/team/lireate-metrika-goals.js` |

### Патч CMS PHP

Найти файл с `<section id="teamFace">` … `<section id="teams">` (часто `template/main.php` или `template/page/team/*.php`).

1. **Заменить** весь фрагмент от `<style>` перед `#teamFace` (если стили только для team) через оба `<section>` на:

```html
<section id="lireate-team">
<?php /* lireate-team-install:v1 */ ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/template/page/team/lireate-team-cms.inc.php'; ?>
</section>
```

2. Legacy сохранить в `template/page/team/lireate-team-legacy.inc.php` (installer делает автоматически).

3. В `<body class="cms-default…">` после сниппетов docs/contacts/partners добавить:

```php
<?php if($temp['page']=='team') echo ' lireate-team-body';?>
```

(или `python3 scripts/lireate_team_install.py --apply` — если шаблон найден).

**Не изменяет:** `data/db.php`, `template/parts/leftSideNav.php`, `topBar.php`, корневой `.htaccess`.

## Авто-деплой

```bash
export LIREATE_DOCROOT=/path/to/site/docroot
python3 scripts/lireate_team_sync_template.py
python3 scripts/lireate_team_install.py --apply --docroot "$LIREATE_DOCROOT"
```

## Откат

```bash
python3 scripts/lireate_team_install.py --rollback <BACKUP_ID> --docroot "$LIREATE_DOCROOT"
```

## Превью / QA

```bash
python3 scripts/lireate_team_fetch_live_snapshot.py
python3 -m http.server 8799 --directory /path/to/office-repo
python3 scripts/lireate_team_capture_screenshots.py
```

Скриншоты: `/opt/cursor/artifacts/screenshots/team-page-*.png`.

## Партнёры (тот же PR)

Перенос длинных имён партнёров: `pages/partners/_shared/lireate-partners.css` (классы `.lireate-partners-name`, `.lireate-partners-team-name`) → синхронизировать в `template/page/partners/`.
