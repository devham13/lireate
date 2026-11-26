# Источники фото services-v4

## Файлы в установке (только WebP)

Установщик `scripts/lireate_six_services_photo_v4_install.py` копирует **только** пять `specialist-*.webp` в корне каталога, без `originals/`.

## Три фото с фоном (обновление 26.11.2026)

Оригиналы владельца (JPEG с полным фоном) конвертированы в:

- `specialist-copyright.webp`
- `specialist-opposition.webp`
- `specialist-registraciya.webp`

Скрипт: `scripts/build_services_v4_from_originals.py` (читает временно положенные `originals/*.jpg`, в git **не хранятся**).

Параметры: RGB WebP 800×1200, качество ~82, без rembg и без подложки.

## Certificate (findtz)

Пересборка из `originals/certificate-original.jpg` (JPEG с тёмным фоном, без вырезания): `scripts/build_certificate_webp.py` → `specialist-certificate.webp` 800×1200 RGB WebP; оригинал после сборки из репозитория удаляется.

## Prolongation (prodlenie)

Пересборка из `originals/prolongation-original.jpg` (с фоном): `scripts/build_prolongation_webp.py` → `specialist-prolongation.webp`; JPEG после сборки удаляется.

## `/team`

Портреты `/template/images/team/person/{n}.jpg` — другие кадры, не заменяют пять постановок услуг.