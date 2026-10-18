# Пункт бокового меню «Продление товарного знака»

## Где живёт меню

На продакшене (публичный хост из `LIREATE_PUBLIC_SITE_URL` / `PUBLIC_SITE_HOST`) блок **Услуги → Товарный знак** рендерится **шаблоном CMS** (левая колонка `#sidebar-wrapper`, классы `leftSideNaviButton`, `leftSideNaviInSection`). В репозитории **devham13/lireate** исходников этого шаблона **нет**; правка — в админке сайта или в PHP-шаблоне на хостинге (`public_html/local/templates/…`), **не** через `data/db.php` в git.

Текущая разметка (фрагмент с главной, октябрь 2026):

```html
<div class="leftSideTitle">Услуги</div>
<div class="leftSideNaviButton js-question">
    Товарный знак <div class="leftSideArrow"></div>
</div>
<div class="js-answer">
    <a href="/findtz" class="leftSideNaviInSection">Проверка товарного знака</a>
    <a href="/registraciya_tovarnogo_znaka" class="leftSideNaviInSection">Регистрация товарного знака</a>
</div>
```

## Ручное добавление (рекомендуется)

1. Войти в админку сайта (раздел шаблонов / структуры / «левое меню» — название зависит от вашей сборки CMS).
2. Найти блок подзаголовка **Товарный знак** (два существующих пункта).
3. **После** ссылки «Регистрация товарного знака» вставить:

```html
<a href="/prodlenie_tovarnogo_znaka/" class="leftSideNaviInSection">Продление товарного знака</a>
```

4. Сохранить шаблон, очистить кэш CMS (если включён).
5. Проверить на главной: раскрыть «Товарный знак» — три пункта, новый ведёт на `/prodlenie_tovarnogo_znaka/` (HTTP 200 после публикации страницы услуги).

## Идемпотентный фрагмент для шаблона на хостинге

Если правите файл шаблона по SSH, добавьте строку только если её ещё нет:

```bash
# DRY-RUN: показать, есть ли уже ссылка
grep -n 'prodlenie_tovarnogo_znaka' /path/to/sidebar-template.php || true

# После ручной вставки HTML-пункта повторить grep — должна быть одна строка.
```

Автоматическая подстановка через `sed` без просмотра файла **не рекомендуется**: у филиалов (msk.lireate.ru и др.) меню может отличаться.

## Связь с публикацией страницы

Пункт меню имеет смысл включать **после** деплоя `pages/services/prodlenie-tovarnogo-znaka/`:

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
export LIREATE_ENV_FILE="$LIREATE_AUTOPUB_HOME/.env"
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter" 2>/dev/null || echo python3)"
"$PY" "$LIREATE_AUTOPUB_HOME/office-repo/scripts/lireate_hosting_publish_service.py" \
  --slug prodlenie-tovarnogo-znaka \
  --apply
```

(Предварительно: `LIREATE_ALLOW_PUBLISH=yes`, актуальный `git pull` в `office-repo`.)
