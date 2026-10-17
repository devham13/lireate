---
description: Полная цепочка страницы услуги LIREATE → commit в pages/services/
---

Пайплайн: **Коля||Артём → Женя → Артур → Алина||Борис → Наташа → schema-markup → internal-linker → Юра → indexator → qa → lenya**.

Директор — текущий Cloud Agent. Task(`director`) не вызывать.

**Перед стартом:** сброс `.cursor/lireate-handoff.md` (`# LIREATE — новая сессия`) и очистка `.cursor/lireate-fragments/`.

1. Тема из сообщения пользователя или `shared/lireate-services-queue.md`.
2. **seo-kolya + artyom** параллельно → фрагменты `kolya.md`, `artyom.md`.
3. **zhenya** — лонгрид 8k–20k+, `shared/lireate-content-rules.md`.
4. **artur** — CTA из env, без скрытой рекламы.
5. **alina + boris** параллельно.
6. **natasha** — самодостаточная вёрстка (не WordPress).
7. **schema-markup**, **internal-linker**.
8. **yura** — `pages/services/<slug>/`, commit/push, **без FTP**.
9. **indexator** (pre_publish), **qa**, **lenya**.

Передай: название услуги, slug (опционально), «без commit» (только если явно).
