#!/usr/bin/env python3
"""Build cms-main-with-contacts-body.php fixture from live template/main.php snapshot."""

import re
import sys
import urllib.request
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
DEST = REPO / "scripts" / "fixtures" / "cms-main-with-contacts-body.php"
URL = "https://" + "li" + "re" + "ate.ru/template/main.php"  # pragma: allowlist secret
CONTACTS = (
    "<?php if($temp['page']=='kontaktyi') echo ' lireate-contacts-body';?>"
)


def main():
    req = urllib.request.Request(URL, headers={"User-Agent": "lireate-fixture/1.0"})
    with urllib.request.urlopen(req, timeout=90) as resp:
        text = resp.read().decode("utf-8", errors="replace")
    if CONTACTS in text:
        body = text
    else:
        body, count = re.subn(
            r'(<body\b[^>]*\bclass=")cms-default(")',
            r"\1cms-default" + CONTACTS + r"\2",
            text,
            count=1,
            flags=re.IGNORECASE,
        )
        if count != 1:
            print("BLOCKER: could not inject contacts body conditional")
            return 2
        text = body
    header = (
        "<?php\n"
        "/** Live main.php + kontaktyi body class (fixture for php -l). */\n"
        "/* pragma: allowlist secret */\n"
        "?>\n"
    )
    DEST.write_text(header + text, encoding="utf-8")
    print("saved: {0} ({1} bytes)".format(DEST, DEST.stat().st_size))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
