#!/usr/bin/env python3
"""Build preview HTML: CMS left sidebar mock + rendered kontaktyi index.php."""

from __future__ import annotations

import re
import subprocess
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
KONTAKTYI = REPO / "pages" / "services" / "kontaktyi"
LEGACY = KONTAKTYI / "backup-old" / "cms-legacy-kontaktyi.html"
OUT = Path("/opt/cursor/artifacts/kontaktyi-preview-with-sidebar.html")


def render_index() -> str:
    env = {"HTTP_HOST": "preview.local", "LIREATE_YANDEX_METRIKA_ID": "0"}
    proc = subprocess.run(
        ["php", str(KONTAKTYI / "index.php")],
        capture_output=True,
        text=True,
        cwd=str(KONTAKTYI),
        env={**subprocess.os.environ, **env},
        check=True,
    )
    return proc.stdout


def extract_legacy_sidebar(html: str) -> tuple[str, str]:
    style_blocks = re.findall(r"<style[^>]*>(.*?)</style>", html, re.I | re.S)
    sidebar_css = "\n".join(style_blocks)
    m = re.search(r"(<section\s+id=\"leftSideBar\".*?</section>)", html, re.I | re.S)
    sidebar = m.group(1) if m else "<section id=\"leftSideBar\"><p>Контакты</p></section>"
    sidebar = sidebar.replace("PUBLIC_SITE_HOST", "https://preview.local")
    return sidebar_css, sidebar


def main() -> None:
    legacy = LEGACY.read_text(encoding="utf-8", errors="replace")
    sidebar_css, sidebar_html = extract_legacy_sidebar(legacy)
    page = render_index()
    head_links = re.findall(r'<link[^>]+href="assets/[^"]+"[^>]*>', page)
    head_links = [h.replace('href="assets/', 'href="../assets/') for h in head_links]
    main_m = re.search(r"(<main\b.*?</main>)", page, re.I | re.S)
    main_html = main_m.group(1) if main_m else page
    main_html = main_html.replace('href="assets/', 'href="../assets/')
    dock_m = re.search(r"(<aside class=\"lx-dock\".*?</aside>)", page, re.I | re.S)
    dock = dock_m.group(1) if dock_m else ""
    dock = dock.replace('href="assets/', 'href="../assets/')
    footer_m = re.search(r"(<footer class=\"lx-site-footer\".*?</footer>)", page, re.I | re.S)
    footer = footer_m.group(1) if footer_m else ""
    footer = footer.replace('href="assets/', 'href="../assets/')
    scripts = re.findall(r'<script[^>]+src="assets/[^"]+"[^>]*></script>', page)
    scripts = [s.replace('src="assets/', 'src="../assets/') for s in scripts]
    body_class = "lireate-svc-body"
    out = f"""<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview — Контакты LIREATE</title>
  {''.join(head_links)}
  <style>
  body {{ margin: 0; background: #0b1220; }}
  .lx-preview-shell {{ display: flex; min-height: 100vh; }}
  #leftSideBar {{ position: relative !important; left: 0 !important; width: 270px !important; flex: 0 0 270px; min-height: 100vh; }}
  .lx-preview-main {{ flex: 1; min-width: 0; position: relative; }}
  @media (max-width: 900px) {{
    .lx-preview-shell {{ flex-direction: column; }}
    #leftSideBar {{ width: 100% !important; flex-basis: auto; min-height: auto; }}
  }}
  {sidebar_css}
  </style>
</head>
<body class="{body_class}">
<div class="lx-preview-shell">
  {sidebar_html}
  <div class="lx-preview-main">
    <div class="lx-bg-glow" aria-hidden="true"></div>
    {main_html}
    {footer}
    {dock}
  </div>
</div>
{''.join(scripts)}
</body>
</html>
"""
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(out, encoding="utf-8")
    print(OUT)


if __name__ == "__main__":
    main()
