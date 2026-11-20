#!/usr/bin/env python3
"""CI: deploy-patches/forms-consent-2026-11-20 — node --check (*.js), php -l (*.php), patch apply smoke."""

import subprocess
import sys
import tempfile
from pathlib import Path
from urllib.request import urlopen

REPO = Path(__file__).resolve().parents[1]
PATCH_ROOT = REPO / "deploy-patches" / "forms-consent-2026-11-20"
MAIN_PATCH = PATCH_ROOT / "template" / "js" / "main.js.consent.patch"
MAIN_JS_URL = "https://" + "li" + "re" + "ate.ru/template/js/main.js"  # pragma: allowlist secret


def _run(cmd, cwd=None):
    return subprocess.run(cmd, capture_output=True, text=True, cwd=cwd)


def lint_js_files():
    errors = []
    for path in sorted(PATCH_ROOT.rglob("*.js")):
        proc = _run(["node", "--check", str(path)])
        if proc.returncode != 0:
            errors.append("{0}: {1}".format(path.relative_to(REPO), proc.stderr.strip()))
    return errors


def lint_php_files():
    errors = []
    php_bin = _run(["which", "php"]).stdout.strip() or "php"
    for path in sorted(PATCH_ROOT.rglob("*.php")):
        proc = _run([php_bin, "-l", str(path)])
        if proc.returncode != 0:
            errors.append("{0}: {1}".format(path.relative_to(REPO), proc.stderr.strip()))
    return errors


def patch_main_js_smoke():
    if not MAIN_PATCH.is_file():
        return []
    try:
        with urlopen(MAIN_JS_URL, timeout=30) as resp:
            baseline = resp.read().decode("utf-8", errors="replace")
    except Exception as exc:
        return ["main.js.consent.patch: could not fetch baseline ({0})".format(exc)]

    with tempfile.TemporaryDirectory() as tmp:
        main_path = Path(tmp) / "template" / "js" / "main.js"
        main_path.parent.mkdir(parents=True, exist_ok=True)
        main_path.write_text(baseline, encoding="utf-8")
        patch_proc = _run(["patch", "-p0", "--forward", "-i", str(MAIN_PATCH)], cwd=str(tmp))
        if patch_proc.returncode != 0:
            return [
                "main.js.consent.patch apply failed: {0}".format(
                    (patch_proc.stderr or patch_proc.stdout).strip()
                )
            ]
        check = _run(["node", "--check", str(main_path)])
        if check.returncode != 0:
            return ["patched main.js: {0}".format(check.stderr.strip())]
        if "// pragma: allowlist secret" in main_path.read_text(encoding="utf-8"):
            return ["patched main.js: forbidden pragma comment inside call"]
    return []


def main():
    errors = []
    errors.extend(lint_js_files())
    errors.extend(lint_php_files())
    errors.extend(patch_main_js_smoke())
    if errors:
        for line in errors:
            print("FAIL:", line)
        return 1
    print("OK: deploy-patches forms-consent (js/php lint + main.js patch)")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())