#!/usr/bin/env python3
"""PHP 5.6 lint helpers (used by lireate_blog_php56_gate.sh)."""

import argparse
import os
import subprocess
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_blog_install_lib as lib  # noqa: E402


def _php_bin():
    return os.environ.get("LIREATE_PHP_BIN") or lib.resolve_site_php_bin()


def cmd_scan():
    violations = lib.scan_php56_forbidden_sources(REPO_ROOT)
    if violations:
        for line in violations:
            print("FAIL:", line)
        return 1
    print("OK: no forbidden PHP 7+ constructs in deploy bundles")
    return 0


def cmd_lint():
    php_bin = _php_bin()
    ver = lib.php_version_string(php_bin)
    print("gate_php_version: {0}".format(ver or "n/a"))
    if ver and not ver.startswith("5.6."):
        print("FAIL: expected PHP 5.6.x, got {0}".format(ver))
        return 1
    code = lib.lint_deployed_php_with_bin(php_bin, REPO_ROOT)
    if code == 0:
        print("OK: php -l passed")
    return code


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--scan-only", action="store_true")
    parser.add_argument("--lint-only", action="store_true")
    args = parser.parse_args()
    if args.scan_only:
        return cmd_scan()
    if args.lint_only:
        return cmd_lint()
    return cmd_scan() or cmd_lint()


if __name__ == "__main__":
    raise SystemExit(main())
