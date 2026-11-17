#!/usr/bin/env python3
"""CI: guard against PR #77-style broken href (typographic quotes, unclosed href=\"…)."""
import re
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]

GUARD_FILES = (
    "pages/team/_shared/lireate-team-page.inc.php",
    "template/page/team/lireate-team-page.inc.php",
    "template/page/news/lireate-blog-list.inc.php",
    "template/lireate/lireate-service-footer.inc.php",
)

SERVICE_GLOB = "pages/services/**/index.php"


def _issues_in_line(line):
    issues = []
    if TYPO := re.search(r"href\s*=\s*[\u2018\u2019]", line):
        issues.append("typographic quote in href=")
    if "<?php" in line and "href=" in line:
        return issues
    if re.search(r"<a\b", line, re.I) and 'href="' in line:
        if re.search(r'href="[^"]*>', line) and not re.search(r'href="[^"]*"', line):
            issues.append('unclosed href=" before >')
        if re.search(r'href="[^"\n]*$', line) and '"' not in line.split('href="', 1)[1]:
            issues.append("line ends with unclosed href=\"")
    return issues


class HrefIntegrityTests(unittest.TestCase):
    def test_guard_files(self):
        bad = []
        for rel in GUARD_FILES:
            path = REPO / rel
            self.assertTrue(path.is_file(), rel)
            for i, line in enumerate(path.read_text(encoding="utf-8").splitlines(), 1):
                for msg in _issues_in_line(line):
                    bad.append("{0}:{1}: {2}".format(rel, i, msg))
        if bad:
            self.fail("\n".join(bad))

    def test_service_contacts_not_kontaktyi(self):
        """Legacy /kontaktyi redirects 301 → /contacts/ on prod; services must link /contacts/."""
        paths = sorted(REPO.glob(SERVICE_GLOB))
        offenders = []
        for path in paths:
            slug_dir = path.parent.name
            if slug_dir in ("_shared", "_template") or "backup-old" in path.parts:
                continue
            text = path.read_text(encoding="utf-8", errors="replace")
            if 'href="/kontaktyi' in text or "href='/kontaktyi" in text:
                offenders.append(str(path.relative_to(REPO)))
        if offenders:
            self.fail("still /kontaktyi in: " + ", ".join(offenders))


if __name__ == "__main__":
    raise SystemExit(unittest.main())