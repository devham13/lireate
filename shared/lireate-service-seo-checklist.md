# SEO-чек-лист страницы услуги LIREATE

Обязателен для **каждой** страницы в `pages/services/<slug>/`. Проверка: `scripts/lireate_service_lint.py`.

## `<head>`

- [ ] Ровно один **H1** в `<body>` (не в head).
- [ ] **`<title>`** — до **60** символов, бренд LIREATE в конце при необходимости.
- [ ] **`meta description`** — **140–160** символов, без keyword stuffing.
- [ ] **`link rel="canonical"`** — абсолютный URL, совпадает с `publish.manifest.json` → `url_path`.
- [ ] **Open Graph:** `og:title`, `og:description`, `og:url`, `og:type` (+ желательно `og:image`).
- [ ] **Twitter:** `twitter:card`, `twitter:title`, `twitter:description`.
- [ ] **Нет** `noindex` / `nofollow` в meta robots.

## JSON-LD (`application/ld+json`)

Один блок `@graph` или эквивалент с типами:

- [ ] **Service** — название, url, description, provider.
- [ ] **LegalService** и/или **Organization** — LIREATE.
- [ ] **BreadcrumbList** — Главная → Услуги → текущая услуга.
- [ ] **FAQPage** — вопросы совпадают с видимым FAQ на странице.

## Структура контента

- [ ] Иерархия **H2** по секциям, **H3** в карточках этапов; не пропускать уровни (H2 после H1).
- [ ] У всех **`<img>`** есть осмысленный **`alt`**.
- [ ] Минимум **2 внутренние ссылки** (`/uslugi/…`, `/news/…`, `/contacts/…`).
- [ ] Блок **мессенджеров** и форма заявки; телефон через `/contacts/` или `tel:`.
- [ ] Дизайн: `assets/lireate-shared/lireate-service-dark.css`, классы `lireate-svc-body`, `lx-nav-pill`, hero + glass panel.

## Перед публикацией

```bash
python3 scripts/lireate_service_sync_shared.py --slug <slug>
python3 scripts/lireate_service_lint.py --slug <slug>
```
