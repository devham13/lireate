#!/usr/bin/env bash
# Триггер GitHub workflow для Cloud/Cursor SEO-fix (repository_dispatch).
set -euo pipefail
REPO="${LIREATE_GITHUB_REPO:-devham13/lireate}"
EVENT="${LIREATE_SEO_FIX_EVENT:-lireate-seo-audit-fix}"
TOKEN="${GITHUB_TOKEN:-${LIREATE_GITHUB_TOKEN:-}}"
: "${SEO_SITE_URL:?SEO_SITE_URL missing}"
SITE="$SEO_SITE_URL"

if [[ -z "$TOKEN" ]]; then
  echo "SKIP: no GITHUB_TOKEN / LIREATE_GITHUB_TOKEN"
  exit 0
fi

payload=$(python3 - <<PY
import json, os
print(json.dumps({
  "event_type": os.environ["EVENT"],
  "client_payload": {
    "site_url": os.environ["SITE"],
    "source": "lireate-seo-vps",
  },
}))
PY
)

curl -fsS -X POST \
  -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer $TOKEN" \
  "https://api.github.com/repos/$REPO/dispatches" \
  -d "$payload"
echo "DISPATCH_OK:$EVENT"