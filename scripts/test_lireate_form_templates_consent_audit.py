#!/usr/bin/env python3
"""Audit consent markup in git form templates (stage H). Skips /findtz/ (proverka slug)."""

import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]

SKIP_FINDTZ_SLUG = "proverka-tovarnogo-znaka"

FORM_SOURCES = [
    REPO / "pages/services/_shared/blocks/lireate-contact-section.inc.php",
    REPO / "pages/services/_shared/lireate-lead-consent-fields.inc.php",
    REPO / "template/page/home/lireate-home-page.inc.php",
    REPO / "pages/home/_shared/lireate-home-page.inc.php",
    REPO / "template/page/kontaktyi/lireate-contacts-page.inc.php",
    REPO / "pages/kontaktyi/_shared/lireate-contacts-page.inc.php",
]

PATCH_ROOT = REPO / "deploy-patches" / "forms-consent-2026-11-20"
PATCH_FILES = [
    PATCH_ROOT / "template/js/main.js.consent.patch",
    PATCH_ROOT / "template/js/main.js.consent-skip-managed.patch",
    PATCH_ROOT / "template/js/main.js.consent-submit-unstick.patch",
    PATCH_ROOT / "template/css/lireate-home-modal-lu.css",
]

CONSENT_PD = re.compile(
    r'name="consent_request"\s+value="1"\s+required|name="consent_request"\s+required\s+value="1"',
    re.I,
)
PRIVATE_DATA = re.compile(r'href="/private_data"')
POLICY = re.compile(r'href="/politika_konfidencialnosti"')
MARKETING_COMBINED = re.compile(r'class="lireate-consent-marketing-combined"')
MARKETING_OFF = re.compile(
    r'name="consent_marketing_data"[^>]+disabled|name="consent_ads"[^>]+disabled',
    re.I,
)
LX_CONSENT_TEXT = re.compile(r'class="lx-consent-text"')
MANAGED = re.compile(r'data-lireate-consent-managed="1"')


def audit_file(path):
    text = path.read_text(encoding="utf-8")
    rel = path.relative_to(REPO)
    errors = []
    if "mail.php" in text or 'name="tel"' in text or "lireate-lead-form" in text or "lh4-lead-form" in text:
        has_consent_inc = (
            "lireate-lead-consent-fields" in text
            or "lireate_contacts_consent_fields" in text
            or "lireate_home_consent_fields" in text
        )
        if not CONSENT_PD.search(text) and not has_consent_inc:
            errors.append("{0}: missing consent_request required checkbox".format(rel))
        consent_body = path.name.endswith("lireate-lead-consent-fields.inc.php") or (
            CONSENT_PD.search(text) and "lireate-lead-consent-fields" not in text
        )
        if consent_body:
            if not PRIVATE_DATA.search(text):
                errors.append("{0}: missing /private_data link".format(rel))
            if not POLICY.search(text):
                errors.append("{0}: missing /politika_konfidencialnosti link".format(rel))
    if path.name.endswith("lireate-lead-consent-fields.inc.php"):
        if not MARKETING_COMBINED.search(text):
            errors.append("{0}: missing combined marketing checkbox".format(rel))
        if not MARKETING_OFF.search(text):
            errors.append("{0}: marketing hiddens must default disabled/off".format(rel))
    if path.name.endswith("lireate-lead-consent-fields.inc.php") and not LX_CONSENT_TEXT.search(text):
        errors.append("{0}: checkbox text should use lx-consent-text (left column)".format(rel))
    if 'id="lireate-lead-form"' in text or 'id="lh4-lead-form"' in text:
        if not MANAGED.search(text):
            errors.append("{0}: managed forms need data-lireate-consent-managed=\"1\"".format(rel))
    if 'data-lx-mail-mock="1"' in text and "/tests/" not in str(rel):
        errors.append("{0}: data-lx-mail-mock only allowed in tests".format(rel))
    return errors


def audit_service_slugs():
    errors = []
    services = REPO / "pages" / "services"
    for slug_dir in sorted(services.iterdir()):
        if not slug_dir.is_dir() or slug_dir.name.startswith("_"):
            continue
        if slug_dir.name == SKIP_FINDTZ_SLUG:
            continue
        index = slug_dir / "index.php"
        if not index.is_file():
            continue
        text = index.read_text(encoding="utf-8")
        if "lireate-lead-scripts.inc.php" not in text:
            continue
        consent = slug_dir / "assets/lireate-shared/lireate-lead-consent-fields.inc.php"
        if not consent.is_file():
            errors.append("services/{0}: missing synced lireate-lead-consent-fields.inc.php".format(slug_dir.name))
    return errors


def main():
    errors = []
    for path in FORM_SOURCES:
        if not path.is_file():
            errors.append("missing template: {0}".format(path.relative_to(REPO)))
            continue
        errors.extend(audit_file(path))
    for path in PATCH_FILES:
        if not path.is_file():
            errors.append("missing deploy patch: {0}".format(path.relative_to(REPO)))
    errors.extend(audit_service_slugs())
    if errors:
        for line in errors:
            print("FAIL:", line)
        return 1
    print("OK: form templates consent audit ({0} sources, deploy-patches present)".format(len(FORM_SOURCES)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())