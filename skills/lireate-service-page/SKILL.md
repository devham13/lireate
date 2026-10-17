---
name: lireate-service-page
description: Страница услуги lireate.ru — тёмный стандарт LIREATE (_shared CSS), SEO-чек-лист, линт, шаблон _template.
---

# Страница услуги LIREATE

## Когда применять

Любая новая или обновлённая услуга в `pages/services/<slug>/` для **lireate.ru** (не новости MySQL).

## Обязательные файлы

1. Скопировать `pages/services/_template/` → `pages/services/<slug>/`.
2. Заполнить `publish.manifest.json` (`slug`, `url_path`, title/description).
3. Вёрстка **только** по `docs/lireate-service-design.ru.md` и эталону `registraciya-tovarnogo-znaka-v-kitae`.
4. Подключить `assets/lireate-shared/lireate-service-dark.css` и `lireate-reveal.js` (после `lireate_service_sync_shared.py`).

## Роли пайплайна

Коля (SEO-ядро), Женя (лонгрид под реальные запросы), Наташа (сборка в классах `lx-*`), schema-markup (дополняет JSON-LD в head), internal-linker (блок `#related`), Юра (коммит, без деплоя из Cloud).

## SEO

Следовать `shared/lireate-service-seo-checklist.md`. Прогон:

```bash
python3 scripts/lireate_service_sync_shared.py --slug <slug>
python3 scripts/lireate_service_lint.py --slug <slug>
```

## Аналитика

Если в **devham13/lireate-news** есть `data/seo-report.json` — учитывать приоритетные запросы и формулировки (не копировать слепо, сверять с юридическими ограничениями).

## Запрещено

- Дизайн текущего шаблона CMS или legacy WordPress-longread как основа услуги.
- Canvas-схемы с налезающими подписями в hero (только HTML `.lx-route`).
- Анимация счётчиков, меняющая грамматику числительных.
- Публикация с Cloud на хостинг.
