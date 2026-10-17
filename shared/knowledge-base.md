# Банк знаний LIREATE Office

## Бренд

- **LIREATE** — юридическая компания, https://${PUBLIC_SITE_HOST}
- Услуги: товарные знаки, ИС, авторское право, патенты, арбитраж
- Контент-правила: `shared/lireate-content-rules.md`

## Технология публикации

- Cloud Agent коммитит `pages/services/<slug>/`
- Хостинг REG.RU: `scripts/lireate_hosting_publish_service.py` (`docs/lireate-services-hosting.ru.md`)
- Новости сайта — отдельный репозиторий `lireate-news`, MySQL, не этот пайплайн

## Дизайн страниц

- Тёмный премиальный longread из репозитория (hero Алины, блок Бориса, вёрстка Наташи)
- Не копировать визуал текущей CMS lireate.ru

## SEO

- Яндекс / Google РФ
- Canonical: `https://${PUBLIC_SITE_HOST}/<url_path>/`
