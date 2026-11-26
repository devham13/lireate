#!/usr/bin/env python3
"""Nav V3 production-like captures (боевой PHP, не _preview nav-v3-router)."""
from __future__ import annotations

import json
import subprocess
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/nav-v3/production"
ROUTER = ROOT / "pages/_preview/nav-v3-production-router.php"
PORT = 19094


def start_server():
    cmd = ["php", "-S", "127.0.0.1:%d" % PORT, "-t", str(ROOT), str(ROUTER)]
    proc = subprocess.Popen(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    time.sleep(0.8)
    return proc


def main():
  from playwright.sync_api import sync_playwright

  OUT.mkdir(parents=True, exist_ok=True)
  proc = start_server()
  shots = []
  try:
    with sync_playwright() as p:
      browser = p.chromium.launch(args=["--no-sandbox"])
      pages = [
          ("home", "/", [1440, 390], ["", "-menu-open", "-side-collapsed"]),
          ("service-rtz", "/registraciya_tovarnogo_znaka/", [1440, 390], [""]),
          ("findtz", "/findtz/", [1440, 390], [""]),
          ("prodlenie", "/prodlenie_tovarnogo_znaka/", [1440, 390], [""]),
          ("mktu", "/mktu/", [1440, 390], [""]),
          ("news", "/news/", [1440, 390], [""]),
          ("team", "/team/", [1440, 390], [""]),
          ("contacts", "/contacts/", [1440, 390], [""]),
      ]
      for slug, path, widths, suffixes in pages:
        slug_dir = OUT / slug
        slug_dir.mkdir(parents=True, exist_ok=True)
        for w in widths:
          for suffix in suffixes:
            page = browser.new_page(
                viewport={"width": w, "height": 900},
                is_mobile=w <= 768,
                has_touch=w <= 768,
            )
            url = "http://127.0.0.1:%d%s" % (PORT, path)
            page.goto(url, wait_until="networkidle", timeout=120000)
            page.wait_for_timeout(600)
            if suffix == "-menu-open" and w <= 768:
              page.click("#lireatePublicCmsBurger", timeout=5000)
              page.wait_for_timeout(400)
            if suffix == "-side-collapsed" and w >= 1151:
              page.click("#collapseSide", timeout=5000)
              page.wait_for_timeout(350)
            out = slug_dir / ("%d%s.png" % (w, suffix))
            page.screenshot(path=str(out), full_page=False)
            shots.append(str(out.relative_to(ROOT)))
            page.close()
      browser.close()
  finally:
    proc.terminate()
  (OUT / "manifest.json").write_text(
      json.dumps({"shots": shots}, ensure_ascii=False, indent=2) + "\n",
      encoding="utf-8",
  )
  print("WROTE", OUT)


if __name__ == "__main__":
  main()