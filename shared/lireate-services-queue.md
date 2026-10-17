# Очередь страниц услуг LIREATE

Директор берёт **следующую непубликованную** строку (или тему из automation prompt). После commit Юры помечайте `slug` в `shared/published-pages.md`.

| Приоритет | Услуга / угол | slug (черновик) | Статус |
| --- | --- | --- | --- |
| P0 | Регистрация товарного знака в Роспатенте | registraciya-tovarnogo-znaka | planned |
| P0 | Проверка и анализ товарного знака перед подачей | proverka-tovarnogo-znaka | planned |
| P1 | Защита авторских прав и переговоры | zashchita-avtorskih-prav | planned |
| P1 | Патентование изобретения / полезной модели | patentovanie-izobreteniya | planned |
| P1 | Представительство в арбитражном суде по спорам ИС | arbitrazh-spory-is | planned |
| P2 | Договоры отчуждения и лицензии на ИС | dogovory-licenzii-is | planned |

Статусы: `planned` → `in_repo` (есть в git) → `ready_for_hosting` → `live` (после хостинг-скрипта).
