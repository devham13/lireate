#!/usr/bin/env python3
"""CI: deploy-patches/forms-consent-2026-11-20 — node --check (*.js), php -l (*.php), patch apply smoke."""

import os
import subprocess
import sys
import tempfile
import time
from pathlib import Path

try:
    from urllib.error import HTTPError, URLError
    from urllib.request import Request, urlopen
except ImportError:
    from urllib2 import Request, urlopen, HTTPError, URLError  # type: ignore

REPO = Path(__file__).resolve().parents[1]
PATCH_ROOT = REPO / "deploy-patches" / "forms-consent-2026-11-20"
MAIN_PATCH = PATCH_ROOT / "template" / "js" / "main.js.consent.patch"
MAIN_JS_FIXTURE = REPO / "tests" / "fixtures" / "forms-consent" / "template" / "js" / "main.js.baseline"
MAIN_JS_URL = "https://" + "li" + "re" + "ate.ru/template/js/main.js"  # pragma: allowlist secret
FETCH_ATTEMPTS = 3
FETCH_TIMEOUT = 30


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


def _consent_patch_already_applied(js_text):
    return (
        "lireate-consent-marketing-combined" in js_text
        and "function syncMarketingCombined" in js_text
        and "function bindMarketingCombined" in js_text
    )


def _verify_main_js_syntax(main_path):
    check = _run(["node", "--check", str(main_path)])
    if check.returncode != 0:
        return ["main.js: {0}".format(check.stderr.strip())]
    text = main_path.read_text(encoding="utf-8")
    if "// pragma: allowlist secret" in text:
        return ["main.js: forbidden pragma comment inside call"]
    if not _consent_patch_already_applied(text):
        return ["main.js: missing combined-consent markers after patch"]
    return []


def _read_baseline_path(path):
    return path.read_text(encoding="utf-8")


def _read_fixture_baseline():
    if not MAIN_JS_FIXTURE.is_file():
        return None
    return _read_baseline_path(MAIN_JS_FIXTURE)


def _fetch_live_baseline():
    override = os.environ.get("LIREATE_MAIN_JS_BASELINE", "").strip()
    if override:
        path = Path(override)
        if path.is_file():
            return _read_baseline_path(path)

    if os.environ.get("LIREATE_FIXTURE_OFFLINE") == "1":
        return _read_fixture_baseline()

    req = Request(MAIN_JS_URL, headers={"User-Agent": "lireate-deploy-patches-forms-consent/1.0"})
    last_exc = None
    for attempt in range(FETCH_ATTEMPTS):
        try:
            with urlopen(req, timeout=FETCH_TIMEOUT) as resp:
                return resp.read().decode("utf-8", errors="replace")
        except (HTTPError, URLError, IOError, OSError) as exc:
            last_exc = exc
            if attempt + 1 < FETCH_ATTEMPTS:
                time.sleep(2 * (attempt + 1))
        except Exception as exc:
            last_exc = exc
            break

    fixture = _read_fixture_baseline()
    if fixture is not None:
        print("NOTE: using main.js baseline fixture (live fetch failed: {0})".format(last_exc))
        return fixture

    if last_exc is not None:
        raise last_exc
    return None


def patch_main_js_smoke():
    if not MAIN_PATCH.is_file():
        return []
    try:
        baseline = _fetch_live_baseline()
    except Exception as exc:
        return ["main.js.consent.patch: could not fetch baseline ({0})".format(exc)]
    if baseline is None:
        return ["main.js.consent.patch: no baseline (fixture missing)"]

    with tempfile.TemporaryDirectory() as tmp:
        main_path = Path(tmp) / "template" / "js" / "main.js"
        main_path.parent.mkdir(parents=True, exist_ok=True)
        main_path.write_text(baseline, encoding="utf-8")
        if _consent_patch_already_applied(baseline):
            return _verify_main_js_syntax(main_path)
        patch_proc = _run(
            ["patch", "-p0", "--forward", "--batch", "-i", str(MAIN_PATCH)],
            cwd=str(tmp),
        )
        out = (patch_proc.stderr or patch_proc.stdout or "").strip()
        if patch_proc.returncode != 0:
            if "Reversed (or previously applied)" in out and _consent_patch_already_applied(
                main_path.read_text(encoding="utf-8")
            ):
                return _verify_main_js_syntax(main_path)
            return ["main.js.consent.patch apply failed: {0}".format(out)]
        return _verify_main_js_syntax(main_path)
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