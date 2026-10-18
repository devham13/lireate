# Общие ассеты страниц услуг LIREATE

Канонические файлы дизайн-системы услуг. **Не публикуются отдельно** — копируются в каждую услугу:

- локально: `scripts/lireate_service_sync_shared.py`
- на хостинге: `scripts/lireate_hosting_publish_service.py` → `assets/lireate-shared/`

Документация: `docs/lireate-service-design.ru.md`.

Не копировать `README.md` в услуги — только `*.css` и `*.js` (см. sync-скрипт).

## Чекбокс согласия (канонический HTML)

Только ссылки на живые страницы сайта: `/private_data` (согласие на обработку ПДн) и `/politika_konfidencialnosti` (политика конфиденциальности). Не использовать `/privacy/`, `/policy` и вымышленные URL.

```html
<label class="lx-consent"><input type="checkbox" id="lf-consent" required> Согласен на <a href="/private_data" target="_blank" rel="noopener noreferrer">обработку персональных данных</a> и с <a href="/politika_konfidencialnosti" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>.</label>
```

## Лента отзывов

- Данные: `reviews.json` (`items`: `text` | `video`; скрины чатов — массив `chat_screenshots` с `image`).
- Разметка: `blocks/lireate-reviews.inc.php`, `lireate-reviews.js` (scroll-snap, стрелки, lightbox).
- На услуге: `$lx_review_filter_services = ['slug'];` + include блока и скрипт.
- Демо «Пример оформления» — только `_template` (`$lx_reviews_demo_only = true`).
- VideoObject JSON-LD — только при известном `embed_url` (YouTube или VK `video_ext.php`).
