# Техническое SEO ${PUBLIC_SITE_HOST} — что править на хостинге (не в devham13/lireate)

Страницы CMS, кейсы и корневой `robots.txt` **не генерируются** из каталога `pages/services/` этого репозитория. После merge PR с услугой и шаблонами — правки ниже вносятся на REG.RU (PHP CMS + MySQL), при необходимости — в **devham13/lireate-news** для ленты `/news`.

## 1. Одинаковый title «Регистрация товарного знака» (18 URL)

Примеры из Вебмастера: `/pdf_book_1`, `/registraciya_tovarnogo_znaka`, `/tester`, `/findtz/` (live title совпадает).

**Где:** шаблоны и записи CMS в DOCROOT (`/local/templates/lireate/`, разделы услуг, лендинги).

**Что сделать:** для каждого URL — уникальный `<title>` (до ~60 символов) и `meta description` 140–160 символов; H1 не дублировать title слово в слово. Для `/findtz/` — title вроде «Проверка товарного знака в Роспатенте — LIREATE».

## 2. Meta description «Описание кейса» (44 кейса `/30`, `/44`, `/11` …)

**Где:** тип кейса в CMS (поле description / шаблон карточки кейса).

**Что сделать:** заполнить уникальное описание по сути кейса или сгенерировать из заголовка + отрасли; убрать заглушку из шаблона.

## 3. Пагинация `/news?num=…&category=…`

**Где:** скрипт списка новостей CMS и/или **lireate-news** (MySQL).

**Что сделать:**

- `<title>`: «Новости LIREATE — страница N» + при наличии — «· Категория»;
- `<link rel="canonical">` на `/news/` для `num=1` или на чистый URL категории без дублей параметров;
- при необходимости `rel="nofollow"` на пагинацию внутри ленты — по политике индексации.

Шаблон статьи из репозитория: `template/page/news/lireate-blog-cms.inc.php` (canonical передаётся через `$lireate_news_canonical`).

## 4. Canonical на разделах

Вебмастер: нет canonical на `/news?num=…`, `/docs/`, `/goods/11/`, `/process_registracii_tovarnogo_znaka`, дубли с `?a`, `__`, `2023`, `&search_source=dzen`.

**Где:** общий `header.php` CMS + шаблоны разделов.

**Что сделать:** вывод canonical на «чистый» URL без трекинг-параметров; 301 с дублей на канон; для товаров `/goods/{id}/` — постоянный URL без лишних query.

## 5. robots.txt

Текущие ошибки Яндекса часто из строк `Disallow: ?mode=test` и `Allow:*` у Twitterbot.

**Предложение в репозитории:** `docs/proposals/robots.lireate.ru.txt` — залить в DOCROOT после ревью Юрия.

## 6. Поддомены в поиске

Проверить в DNS/хостинге: `www`, тестовые поддомены, зеркала. Настроить 301 на канонический хост `${PUBLIC_SITE_HOST}`, в Вебмастере — «главное зеркало»; при необходимости отдельный `robots.txt` на поддомене с `Disallow: /`.

## 7. Новая услуга из этого PR

После merge: CRON `scripts/lireate_hosting_services_cron.sh` → URL  
`/dorabotka_logotipa_i_nazvaniya_dlya_registracii_tovarnogo_znaka/`  
(см. `publish.manifest.json`). Пункт меню — после «Проверка товарного знака», если включён `LIREATE_ALLOW_MENU_EDIT`.
