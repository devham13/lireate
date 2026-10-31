#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-partners"
PORT="${LIREATE_CONTACTS_PREVIEW_PORT:-8892}"
rm -rf "$DOCROOT"
mkdir -p "$DOCROOT/template/page" "$DOCROOT/pages/partners/_shared" "$DOCROOT/pages/blog/_shared" "$DOCROOT/pages/services/_shared" "$DOCROOT/template/lireate"
python3 "$ROOT/scripts/lireate_partners_fetch_live_snapshot.py"
ln -sfn "$ROOT/pages/partners/_preview" "$DOCROOT/partners_preview"
ln -sfn "$ROOT/template/page/partners" "$DOCROOT/template/page/partners"
ln -sfn "$ROOT/template/page/news" "$DOCROOT/template/page/news"
ln -sfn "$ROOT/pages/partners/_shared" "$DOCROOT/pages/partners/_shared"
for f in lireate-blog-load.php lireate-blog-lib.php lireate-blog-site-footer.inc.php lireate-blog-footer.css lireate-service-messenger-include.inc.php; do
  ln -sfn "$ROOT/pages/blog/_shared/$f" "$DOCROOT/pages/blog/_shared/$f" 2>/dev/null || true
done
ln -sfn "$ROOT/pages/services/_shared/lireate-messenger-urls.php" "$DOCROOT/pages/services/_shared/lireate-messenger-urls.php"
ln -sfn "$ROOT/pages/services/_shared/lireate-service-footer.inc.php" "$DOCROOT/template/lireate/lireate-service-footer.inc.php"
echo "Live CMS preview: http://127.0.0.1:${PORT}/partners_preview/live-cms-shell.php"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"
