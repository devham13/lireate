"""Patch live template/js/main.js so legacy consent injector skips PHP-managed forms."""

from __future__ import annotations

import re
import subprocess
from pathlib import Path

MARKER = "lireate-consent-skip-managed:v1"
ANCHOR = "consentForms.each(function(index) {\n        var form = $(this);"
SKIP_BLOCK = (
    "\n        /* {0} */\n"
    "        if (form.attr('data-lireate-consent-managed') === '1' || "
    "form.attr('data-lireate-skip-legacy-consent') === '1') {{\n"
    "            return;\n"
    "        }}\n"
    "        if (form.find('input[name=\"consent_request\"]').length) {{\n"
    "            return;\n"
    "        }}\n"
).format(MARKER)


def is_patched(text):
    return MARKER in text


def plan_patch(text):
    if is_patched(text):
        return text, False
    if ANCHOR not in text:
        return text, False
    return text.replace(ANCHOR, ANCHOR + SKIP_BLOCK, 1), True


def node_check(path, node_bin="node"):
    proc = subprocess.run(
        [node_bin, "--check", str(path)],
        capture_output=True,
        text=True,
    )
    if proc.returncode != 0:
        return (proc.stderr or proc.stdout or "node --check failed").strip()
    return None


def apply_patch(main_js_path, node_bin="node"):
    path = Path(main_js_path)
    if not path.is_file():
        return False, "missing main.js"
    original = path.read_text(encoding="utf-8", errors="replace")
    planned, changed = plan_patch(original)
    if not changed:
        if is_patched(original):
            return False, "already patched"
        return False, "anchor not found in main.js"
    staging = path.parent / ".lireate-main-js-staged.js"
    staging.write_text(planned, encoding="utf-8")
    err = node_check(staging, node_bin)
    if err:
        staging.unlink(missing_ok=True)
        return False, err
    staging.replace(path)
    return True, "patched main.js consent skip"