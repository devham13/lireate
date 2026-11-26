# LIREATE Light V4 — шесть страниц с фото сотрудников

Дизайн для текущих URL: `/findtz/`, `/registraciya_tovarnogo_znaka/`, `/prodlenie_tovarnogo_znaka/`, `/vozrazhenie_protiv_registracii_tovarnogo_znaka/`, `/zashchita_avtorskih_prav/`, `/dogovory/`.

## Состав

- `template/css/lireate-six-services-photo-v4.css` — переработка типографики, белой палитры, карточек, этапов и hero-блока в стиле главной; мобильные брейкпоинты.
- `template/js/lireate-six-services-photo-v4.js` — вставляет карточку специалиста в точном формате главной: фото, специализация, подпись, ссылки «Команда Лирейт» и «О компании», второй блок «Понятный первый шаг», а также переносит сохранённый справочный блок `lx-glass` под hero. Строгое сопоставление шести URL, повторный запуск безопасен.
- `template/images/services-v4/*.webp` — пять непрозрачных RGB WebP 800×1200 (без альфа-канала): фото специалиста **с фоном**, кадрирование под карточку hero (`object-fit: cover`, `center top`), как на главной. **Лица и одежда не меняются.** Исходные JPEG владельца с полноразмерным фоном в git не попадали (см. `template/images/services-v4/SOURCES.ru.md`).
- `scripts/lireate_six_services_photo_v4_install.py` — dry-run, применение, backup, PHP lint, rollback (защита от перезаписи изменённых файлов).

## Назначение фотографий

| URL | Файл |
|---|---|
| `/findtz/` | `specialist-certificate.webp` — со свидетельством |
| `/registraciya_tovarnogo_znaka/` | `specialist-registraciya.webp` — светлый костюм и папка |
| `/prodlenie_tovarnogo_znaka/` | `specialist-prolongation.webp` — леопардовая блузка |
| `/vozrazhenie_protiv_registracii_tovarnogo_znaka/` | `specialist-opposition.webp` — чёрный пиджак |
| `/zashchita_avtorskih_prav/` | `specialist-copyright.webp` — зелёный топ |
| `/dogovory/` | `specialist-registraciya.webp` — повтор делового фото |

## Неизменные области

Ни один исходный H1, title, description, canonical, FAQ, schema.org, калькулятор, форма, кнопка заявки, ссылка, аналитика, согласие на обработку персональных данных, JS исходного сервиса или меню не переписывается. Оригинальный контент остаётся в DOM, исходный `lx-glass` не уничтожается, а переносится под первый экран. Если JavaScript выключен, отображается исходная страница.

Не использовать `lireate_patent_light_v4_install.py` вместо этого скрипта: он относится к другим двум страницам (`/patent`, `/patent_prom_obr`).

## Внедрение через Grok/Cursor после одобрения

1. Открыть PR, сделать отдельный checkout ветки. Прочитать `AGENTS.md` и этот документ.
2. Через SSH REG.RU установить переменную `LIREATE_DOCROOT` по реальному пути, например `$HOME/www/public_html` (проверить!).
3. **Только сухая проверка:**

   `python3 scripts/lireate_six_services_photo_v4_install.py --docroot "$LIREATE_DOCROOT" --backup-root "$HOME/lireate-autopub/backups/six-pages-photo-v4"`

4. Проверить список файлов: `template/main.php` (если есть), шесть существующих index.php (если есть), CSS/JS и пять WebP. Скрипт не меняет `data/db.php`, `.htaccess`, cron и другие файлы.
5. При согласованной публикации использовать те же аргументы с `--apply`. Установщик проверяет PHP и возвращает путь backup.
6. Обязательно протестировать **живые** `/findtz/`, `/registraciya_tovarnogo_znaka/`, `/prodlenie_tovarnogo_znaka/`, `/vozrazhenie_protiv_registracii_tovarnogo_znaka/`, `/zashchita_avtorskih_prav/`, `/dogovory/` на desktop и mobile. Проверить src пяти изображений (HTTP 200), заполнение форм, все CTA, параметры ссылки, Метрику, калькуляторы, FAQ, sticky header, меню и производительность.
7. Проверить, что CRON не перезапишет правку. **PR в main самостоятельно не вливать.**
8. Откат: `--rollback <backup_dir>` и те же `--docroot`/`--backup-root`. Если после установки какой-то файл менялся, скрипт не откатывает его без `--force-rollback` (только после ручной проверки).

**Важно:** код в PR не опубликован на REG.RU. Заключение о внедрении можно дать только после проверки реальных URL.


## Обновление 26.11.2026: карточка специалиста

Первый экран справа теперь содержит **карточку в рамке как на главной**:
светлая фотозона, бейдж специализации, подпись, текст, ссылки на «Команда Лирейт» и «О компании». Ниже — самостоятельный блок «Понятный первый шаг» с красной полоской. Это не голое изображение.

**Важное ограничение достоверности:** имена специалистов по одним фотографиям не определяем. В первой версии на всех карточках используется нейтральная подпись «Специалист Лирейт». Не подменять её произвольным именем или должностью. Если владелец подтвердит соответствие фотографий карточкам сотрудников, внести фактические имя и роль через явную конфигурацию CMS. **Фон не вырезается:** карточка обрезает кадр CSS-ом (лицо и грудь в рамке), без rembg/прозрачности.

Демонстрационные HTML-страницы — отдельный результат для визуального QA. На боевом сайте никакие действия не выполнялись. Перед установкой проверить кадрирование в карточке на desktop/mobile; при появлении исходных JPEG с фоном от владельца — заменить WebP через `scripts/build_services_v4_opaque_webp.py` (или положить готовые RGB WebP в `template/images/services-v4/`).
