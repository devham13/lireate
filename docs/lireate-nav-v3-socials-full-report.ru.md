# Nav V3 — краткий отчёт для Юры

| | |
|--|--|
| PR | https://github.com/devham13/lireate/pull/127 |
| SHA | `862999f19c1c9db57d8d5526375686a4b6df1e56` |
| Скрины AFTER | `docs/screenshots/nav-v3/after/{home,team,service-rtz,...}/` |
| QA | `docs/screenshots/nav-v3/capture-qa.json` |

## Что исправлено (визуальный QA)

1. **Логотип sidebar:** `template/images/logo/logo.svg` (с live), фоновая отрисовка без mask-конфликта в `lireate-public-cms-chrome`.
2. **Subnav 1280/1440:** растяжение на ширину колонки, `clamp(16–19px)`, один `ln3-active` при `scrollY=0`; CTA «Оставить заявку» в `lh4-subnav-right`.
3. **Главная hero:** убран двойной `margin-left` (chrome + legacy preview), subnav `wrap` на полную ширину — H1 в 2 строки.
4. **Collapsed:** активный пункт subnav один (spy fix).
5. **service-rtz:** полный longread из `pages/services/registraciya-tovarnogo-znaka/index.php` + chrome, свои якоря в subnav.
6. **Mobile drawer:** логотип в шапке, соцсети внизу (scrollIntoView в capture), бургер скрыт при открытом меню.

## Проверки capture-qa (AFTER)

- `horizontalOverflow`: **0**
- `hasNavCss`: все AFTER **true**
- PNG width = viewport

**Без merge и без выкладки.**