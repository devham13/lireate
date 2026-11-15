# SEO-аудит 15.11.2026 — PR2, метаданные P1

Манифест: `data/seo-audit-2026-11-15/p1-metadata.json` (лист «Страницы», приоритет P1 с заполненными Title/Description/H1).

Пути CMS в манифесте **без завершающего слэша** — как live: `/patent`, `/mktu`, `/kontaktyi`, `/news`, `/uslugi`.

## Уже в репозитории (страницы услуг LIREATE)

После merge обновляются `pages/services/<slug>/index.php` (кроме `/registraciya_tovarnogo_znaka/` и `/findtz/` — отдельный PR после **#76**):

- `/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/`
- `/prodlenie_tovarnogo_znaka/`
- `/registraciya_tovarnogo_znaka_v_kitae/`
- `/vozrazhenie_protiv_registracii_tovarnogo_znaka/`
- `/zashchita_avtorskih_prav/`

Публикация новых/изменённых longread-услуг — только **`lireate_hosting_publish_service.py` без `--update`** (режим update перезаписывает live и затирает правки вне репозитория). Существующие slug на хостинге править вручную в DOCROOT или дождаться согласованного деплоя.

## Хостинг CMS (главная, /uslugi, /mktu, /patent, статьи, legacy-лендинги)

Правки Title, Description, H1 выполняются в PHP-шаблонах DOCROOT (не в `data/db.php`). Для массовой замены ссылок — PR1 (`lireate_seo_audit_links_install.py`).

### Блог `/news` — canonical пагинации (очередь 04)

В `lireate-blog-list-data.php`:

- `lireate_blog_list_canonical_path()` — путь self-canonical для текущей страницы списка (`/news` или `/news?view=news&page=N`).

В шаблоне `<head>` для `/news` подставьте:

```php
<?php
if (function_exists('lireate_blog_list_canonical_path')) {
    $canonPath = lireate_blog_list_canonical_path();
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    if ($host !== '') {
        echo '<link rel="canonical" href="https://' . htmlspecialchars($host, ENT_QUOTES, 'UTF-8') . htmlspecialchars($canonPath, ENT_QUOTES, 'UTF-8') . '">';
    }
}
?>
```

Либо используйте `$GLOBALS['lireate_blog_list_canonical_path']`, который выставляет `lireate-blog-list-cms.inc.php` до рендера списка.