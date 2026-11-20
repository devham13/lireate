#!/usr/bin/env bash
set -euo pipefail
SEO_TOOLS_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$SEO_TOOLS_DIR"
command -v docker >/dev/null || { echo 'Docker is required on the VPS.' >&2; exit 1; }
docker compose version >/dev/null
if [[ "$(uname -m)" != 'x86_64' ]]; then
  echo 'The pinned SEOnaut image requires an amd64 Linux VPS.' >&2
  exit 1
fi
python3 init-env.py
docker compose config --quiet
docker compose up -d --wait db seonaut
echo 'SEOnaut started on the VPS loopback port configured in .env.'
echo 'Open it through an SSH tunnel or an authenticated reverse proxy.'
