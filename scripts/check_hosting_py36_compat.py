#!/usr/bin/env python3
"""Fail if hot-path hosting install/publish/rollback scripts use Python 3.7+ only syntax."""

import importlib.util
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TARGETS = [
    ROOT / "scripts" / "lireate_hosting_publish_service.py",
    ROOT / "scripts" / "lireate_hosting_service_rollback.py",
    ROOT / "scripts" / "lireate_hosting_service_rollback_lib.py",
    ROOT / "scripts" / "lireate_main_js_consent_skip_patch.py",
    ROOT / "scripts" / "lireate_home_light_v4_install.py",
    ROOT / "scripts" / "lireate_home_light_v4_install_lib.py",
    ROOT / "scripts" / "lireate_main_php_cache_bust.py",
    ROOT / "scripts" / "lireate_light_v4_hosting_rollout.py",
    ROOT / "scripts" / "lireate_hosting_performance_patch.py",
    ROOT / "scripts" / "lireate_cms_light_v4_overlay_install.py",
    ROOT / "scripts" / "lireate_hosting_pf_install.py",
    ROOT / "scripts" / "lireate_blog_install.py",
    ROOT / "scripts" / "lireate_blog_install_lib.py",
    ROOT / "scripts" / "lireate_blog_cgi_gate.py",
    ROOT / "scripts" / "lireate_hosting_services_lib.py",
    ROOT / "scripts" / "lireate_install_rollback_lib.py",
]

IMPORT_MODULES = [
    "lireate_hosting_publish_service",
    "lireate_hosting_service_rollback",
    "lireate_home_light_v4_install",
    "lireate_light_v4_hosting_rollout",
    "lireate_cms_light_v4_overlay_install",
    "lireate_hosting_pf_install",
    "lireate_blog_install",
    "lireate_blog_install_lib",
    "lireate_blog_cgi_gate",
    "lireate_hosting_services_lib",
]

FORBIDDEN = [
    (re.compile(r"from __future__ import annotations"), "future annotations"),
    (re.compile(r"\bmissing_ok\s*="), "Path.unlink(missing_ok=)"),
    (re.compile(r"\bcapture_output\s*="), "subprocess capture_output"),
    (re.compile(r"\bset\[str\]"), "set[str] annotation"),
    (re.compile(r"\blist\["), "list[] annotation"),
    (re.compile(r"\bdict\["), "dict[] annotation"),
    (re.compile(r"\btuple\["), "tuple[] annotation"),
    (re.compile(r"\s\|\s*None"), "X | None union"),
]


def main():
    errors = []
    for path in TARGETS:
        if not path.is_file():
            continue
        text = path.read_text(encoding="utf-8")
        for pattern, name in FORBIDDEN:
            if pattern.search(text):
                errors.append("{0}: forbidden {1}".format(path.name, name))
    scripts_dir = str(ROOT / "scripts")
    if scripts_dir not in sys.path:
        sys.path.insert(0, scripts_dir)
    for name in IMPORT_MODULES:
        try:
            importlib.import_module(name)
        except Exception as exc:
            errors.append("import {0}: {1}".format(name, exc))
    if errors:
        for line in errors:
            print("FAIL:", line)
        return 1
    print("OK: hosting hot-path scripts py36-compat scan ({0} files)".format(len(TARGETS)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())