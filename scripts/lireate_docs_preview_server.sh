#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-docs"
PORT="${LIREATE_DOCS_PREVIEW_PORT:-8892}"
rm -rf "$DOCROOT"
mkdir -p "$DOCROOT/template/page" "$DOCROOT/pages/docs/_shared" "$DOCROOT/pages/blog/_shared" "$DOCROOT/pages/services/_shared"
python3 "$ROOT/scripts/lireate_docs_fetch_live_snapshot.py"
python3 "$ROOT/scripts/lireate_docs_sync_template.py"
ln -sfn "$ROOT/pages/docs/_preview" "$DOCROOT/docs_preview"
ln -sfn "$ROOT/template/page/docs" "$DOCROOT/template/page/docs"
ln -sfn "$ROOT/template/page/news" "$DOCROOT/template/page/news"
ln -sfn "$ROOT/pages/docs/_shared" "$DOCROOT/pages/docs/_shared"
for f in lireate-blog-load.php lireate-blog-lib.php lireate-blog-site-footer.inc.php lireate-blog-footer.css; do
  ln -sfn "$ROOT/pages/blog/_shared/$f" "$DOCROOT/pages/blog/_shared/$f" 2>/dev/null || true
done
echo "Live CMS preview: http://127.0.0.1:${PORT}/docs_preview/live-cms-shell.php"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"
