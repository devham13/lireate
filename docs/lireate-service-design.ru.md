# Дизайн-система страниц услуг LIREATE

Обязательный стандарт для **всех** новых страниц услуг на `lireate.ru`. Эталон: `pages/services/registraciya-tovarnogo-znaka-v-kitae/`.

## Исходники

| Путь | Назначение |
|------|------------|
| `pages/services/_shared/lireate-service-dark.css` | Общие стили (префикс классов `lx-*`) |
| `pages/services/_shared/lireate-reveal.js` | Появление секций при скролле |
| `pages/services/_shared/lireate-contact.js` | Иконки мессенджеров, форма заявки |
| `pages/services/_template/` | Копировать при новой услуге |
| `shared/lireate-service-seo-checklist.md` | SEO-чек-лист |
| `scripts/lireate_service_lint.py` | Автопроверка перед коммитом |

После правки `_shared` выполните:

```bash
python3 scripts/lireate_service_sync_shared.py
```

На хостинге общие файлы подмешивает `lireate_hosting_publish_service.py` в `assets/lireate-shared/`.

## Палитра и фон

- Фон страницы: `#0b1020` (navy), радиальные свечения сине-фиолетовые (`.lx-bg-glow`).
- Текст основной: `#94a3b8`, заголовки: `#f1f5f9` / `#f8fafc`.
- Акцент лейблов: `#60a5fa`, вторичный фиолетовый: `#a78bfa`.
- Кнопка primary: градиент `#3b82f6` → `#8b5cf6` → `#6366f1`, скругление pill.

## Типографика

- Шрифт: **Inter** (Google Fonts), fallback system-ui.
- Eyebrow / section label: 11px, uppercase, letter-spacing 0.14em, weight 800.
- H1 hero: clamp ~2–3.15rem, weight 800, letter-spacing −0.03em.
- H2 секций: clamp ~1.5–2.1rem, weight 800.
- Body: 16px, line-height 1.65–1.75.

## Отступы и сетка

- Контейнер `.lireate-svc-page`: max-width 1120px, padding 0 20px, нижний отступ 80px.
- Секция `.lx-section`: padding-top 56px.
- Hero: двухколоночная сетка (1.05fr / 0.95fr), gap 40px; ≤960px — одна колонка.
- Карточки этапов `.lx-card-grid`: 3 колонки → 1 на мобиле.

## Компоненты

1. **Плавающее меню** — `.lx-nav-shell` + `.lx-nav-pill` (белая «пилюля», логотип LIREATE, якоря, CTA «Консультация»). На узком экране скрыть `.lx-nav-links`.
2. **Hero** — eyebrow, один H1, лид, теги `.lx-tags`, CTA primary + ghost, мини-статистика `.lx-hero-mini` (финальные тексты, без «ломающей» анимации счётчиков).
3. **Стеклянная панель** — `.lx-glass`: KPI `.lx-kpi-row`, лента `.lx-feed`, схема `.lx-route` / `.lx-route-steps` (HTML/CSS, не canvas с налезающим текстом).
4. **Секции** — `.lx-section-label` + H2, абзацы, при необходимости `.lx-card-grid` / `.lx-checklist`.
5. **FAQ** — `<details class="lx-faq">` + дублирование в JSON-LD `FAQPage`.
6. **Форма** — `#lireate-lead-form`: только **Имя**, **Телефон**, чекбокс согласия со ссылками на `/private_data` (обработка ПДн) и `/politika_konfidencialnosti` (политика конфиденциальности), обе в новой вкладке; кнопка **«Получить консультацию»**. Под кнопкой — `.lx-form-alt` («или напишите нам» + компактные Telegram и MAX). Атрибут `data-lead-topic` задаёт префикс сообщения в Telegram. **Не** использовать `/privacy/`, `/policy` и другие выдуманные URL политики.
7. **Мессенджеры** — `#link-telegram`, `#link-whatsapp`, `#link-max`; на hero — `#hero-telegram`, `#hero-max` с `data-lx-icon`. Ссылки по умолчанию: Telegram `https://t.me/lireate_patent`, WhatsApp `https://wa.me/79600484957`, MAX `https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI`, телефон `tel:+79600484957`. Опционально `data-lireate-*` на `<html>` (подставляет CMS).
8. **Dock** — `.lx-dock` справа снизу: заявка (иконка брифа), Telegram и MAX (inline SVG через `lireate-contact.js`, без текстовых TG/M).
9. **Перелинковка** — блок `#related` с `.lx-related-links`.

## Адаптив

- Breakpoints в CSS: 960px (hero/contact, скрытие меню), 520px (мессенджеры, KPI, схема 2 колонки).
- Статистика и подписи — грамотный русский, готовые строки в HTML.

## Подключение на странице

```html
<link rel="stylesheet" href="assets/lireate-shared/lireate-service-dark.css">
...
<body class="lireate-svc-body">
<main class="lireate-svc-page ...">
<script src="assets/lireate-shared/lireate-reveal.js"></script>
<script src="assets/lireate-shared/lireate-contact.js"></script>
```

Иконки Telegram (самолётик) и MAX (логотип с max.ru/favicon.svg) — inline SVG в `lireate-contact.js`, без внешних CDN.

## Контент и SEO

- Лонгрид 8k–20k+ знаков, без выдуманных дел (`shared/lireate-content-rules.md`).
- Запросы: очередь услуг, бриф пользователя, при наличии — `data/seo-report.json` из репозитория **lireate-news** (бот Метрики).
- Перед коммитом: `python3 scripts/lireate_service_lint.py --slug <slug>`.

## Интерактив и маскот

- Скрипты: `lireate-motion-init.js` (prefers-reduced-motion), `lireate-mascot.js` (вход, wave, point CTA, walk по `#program`, wave у формы), `lireate-hero-panel.js` (KPI + анимация route-card), `lireate-steps-walk.js`, карусель/квиз/таймлайн/compare/calculator/reviews/mobile-bar — по карте `pages/services/_template/BLOCKS.ru.md`.
- Отзывы: `blocks/lireate-reviews.inc.php`, данные `reviews.json`; демо-карточка только в `_template`.

## Реквизиты в футере

Телефон, адрес (Зеленодольск), email `info@librend.ru`, мессенджеры — как на `/contacts/` и в политике. **ИНН/ОГРН/полное юр. наименование на сайте не найдены** — в футер не добавляются до появления на lireate.ru.
