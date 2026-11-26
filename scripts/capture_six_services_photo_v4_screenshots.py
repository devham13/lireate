#!/usr/bin/env python3
"""Screenshots for six CMS service pages with photo-v4 overlay (1440×900, 390×900)."""

import os
import shutil
import subprocess
import tempfile
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/six-services-v4"
SHARED_CSS = ROOT / "pages/services/_shared/lireate-service-dark.css"
PHOTO_CSS = ROOT / "template/css/lireate-six-services-photo-v4.css"
PHOTO_JS = ROOT / "template/js/lireate-six-services-photo-v4.js"
IMAGES = ROOT / "template/images/services-v4"
PORT = int(os.environ.get("LIREATE_SIX_PHOTO_V4_PORT", "19140"))

PAGES = {
    "findtz": ("Проверка товарного знака", "findtz"),
    "registraciya_tovarnogo_znaka": ("Регистрация товарного знака", "registraciya"),
    "prodlenie_tovarnogo_znaka": ("Продление товарного знака", "prodlenie"),
    "vozrazhenie_protiv_registracii_tovarnogo_znaka": ("Возражение против регистрации ТЗ", "vozrazhenie"),
    "zashchita_avtorskih_prav": ("Защита авторских прав", "copyright"),
    "dogovory": ("Договоры в сфере интеллектуальной собственности", "dogovory"),
}

DOGOVORY_GLASS = """
  <div class="lx-glass lx-glass-anim" aria-label="Краткий маршрут">
    <div class="lx-panel-head"><span>Договоры · маршрут</span><span class="lx-live">LIREATE</span></div>
    <div class="lx-kpi-row">
      <div class="lx-kpi"><b class="lx-stat-value">лицензия</b><small>исключительная или обычная</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">п. 3.10</b><small>госрегистрация лицензии</small></div>
      <div class="lx-kpi"><b class="lx-stat-value">п. 3.11</b><small>отчуждение права</small></div>
    </div>
    <div class="lx-route">
      <span class="lx-route-label">Этапы</span>
      <ul class="lx-route-steps">
        <li><span class="lx-route-node lx-route-lit">бриф</span></li>
        <li><span class="lx-route-node lx-route-lit">проект</span></li>
        <li><span class="lx-route-node">согласование</span></li>
        <li><span class="lx-route-node">подписание</span></li>
        <li><span class="lx-route-node">регистрация</span></li>
      </ul>
    </div>
    <ul class="lx-feed">
      <li>Анализ объекта и сторон</li>
      <li>Проект договора под вашу сделку</li>
    </ul>
  </div>
"""


def page_html(title: str, slug_key: str, route: str) -> str:
    glass = DOGOVORY_GLASS if route == "dogovory" else ""
    return f"""<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title} — LIREATE</title>
<link rel="stylesheet" href="/shared/lireate-service-dark.css">
<link rel="stylesheet" href="/template/css/lireate-six-services-photo-v4.css?v=20261126c">
<script defer src="/template/js/lireate-six-services-photo-v4.js?v=20261126c"></script>
</head>
<body class="lireate-svc-body">
<div class="lx-nav-shell"><header class="lx-nav-pill"><a class="lx-logo" href="/">LIREATE</a></header></div>
<main class="lireate-svc-page">
<nav class="lx-breadcrumbs lx-breadcrumbs--in-page" aria-label="Хлебные крошки">
  <ol><li><a href="/">Главная</a></li><li>Услуги</li><li aria-current="page">{title}</li></ol>
</nav>
<section class="lx-hero" id="top">
  <div class="lx-hero-mascot-slot"></div>
  <div>
    <p class="lx-eyebrow">LIREATE · {slug_key}</p>
    <h1>{title}</h1>
    <p class="lx-hero-lead">Сопровождение LIREATE: консультация, подготовка документов, подача в ведомство.</p>
    <div class="lx-hero-cta"><a class="lx-btn lx-btn-primary" href="#contact">Оставить заявку</a></div>
    <div class="lx-hero-mini"><div class="lx-mini-stat"><strong>2015</strong><span>с опыта</span></div></div>
  </div>
  {glass}
</section>
</main>
</body>
</html>
"""


def prepare_docroot(base: Path) -> None:
    (base / "shared").mkdir(parents=True, exist_ok=True)
    shutil.copy2(SHARED_CSS, base / "shared/lireate-service-dark.css")
    for route in PAGES:
        (base / route).mkdir(parents=True, exist_ok=True)
        title, _ = PAGES[route]
        (base / route / "index.html").write_text(page_html(title, route, route), encoding="utf-8")
    tpl = base / "template"
    shutil.copytree(ROOT / "template/css", tpl / "css", dirs_exist_ok=True)
    shutil.copytree(ROOT / "template/js", tpl / "js", dirs_exist_ok=True)
    shutil.copytree(IMAGES, tpl / "images/services-v4", dirs_exist_ok=True)


def main() -> int:
    OUT.mkdir(parents=True, exist_ok=True)
    docroot = Path(tempfile.mkdtemp(prefix="six-photo-v4-"))
    prepare_docroot(docroot)
    proc = subprocess.Popen(
        ["php", "-S", "127.0.0.1:{0}".format(PORT), "-t", str(docroot)],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.4)
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        proc.terminate()
        print("BLOCKER: playwright missing")
        return 2
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page()
            for route in PAGES:
                for tag, w, h in (("1440", 1440, 900), ("390", 390, 900)):
                    url = "http://127.0.0.1:{0}/{1}/".format(PORT, route)
                    page.set_viewport_size({"width": w, "height": h})
                    page.goto(url, wait_until="networkidle", timeout=60000)
                    page.wait_for_selector(".lx-photo-v4-expert-card img", timeout=15000)
                    out = OUT / "{0}-{1}.png".format(route, tag)
                    page.screenshot(path=str(out), full_page=False)
                    print("wrote", out)
            # contact sheet: five photos on light bg
            sheet = OUT / "specialists-on-light.png"
            page.set_viewport_size({"width": 1440, "height": 900})
            rows = []
            for name in sorted(IMAGES.glob("specialist-*.webp")):
                rows.append(
                    '<figure><img src="/template/images/services-v4/{0}" alt=""><figcaption>{0}</figcaption></figure>'.format(
                        name.name
                    )
                )
            html = (
                "<!DOCTYPE html><html><head><style>body{{margin:0;background:#fff;font-family:Manrope,sans-serif}}"
                "main{{padding:24px;display:grid;grid-template-columns:repeat(5,1fr);gap:16px}}"
                "figure{{margin:0}}img{{width:100%;aspect-ratio:683/1024;object-fit:cover;object-position:center top;border-radius:16px;border:1px solid #dae6f6}}"
                "figcaption{{font-size:10px;color:#52637b;margin-top:6px}}</style></head>"
                "<body><main>{0}</main></body></html>".format("".join(rows))
            )
            (docroot / "sheet.html").write_text(html, encoding="utf-8")
            page.goto("http://127.0.0.1:{0}/sheet.html".format(PORT), wait_until="networkidle")
            page.screenshot(path=str(sheet), full_page=True)
            print("wrote", sheet)
            browser.close()
    finally:
        proc.terminate()
        proc.wait(timeout=5)
        shutil.rmtree(docroot, ignore_errors=True)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())