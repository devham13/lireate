#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-contacts"
PORT="${LIREATE_CONTACTS_PREVIEW_PORT:-8891}"
rm -rf "$DOCROOT"
mkdir -p "$DOCROOT/template/page" "$DOCROOT/pages/kontaktyi/_shared" "$DOCROOT/pages/blog/_shared" "$DOCROOT/pages/services/_shared" "$DOCROOT/template/lireate"
ln -sfn "$ROOT/pages/kontaktyi/_preview" "$DOCROOT/kontaktyi_preview"
ln -sfn "$ROOT/template/page/kontaktyi" "$DOCROOT/template/page/kontaktyi"
ln -sfn "$ROOT/pages/kontaktyi/_shared" "$DOCROOT/pages/kontaktyi/_shared"
for f in lireate-blog-load.php lireate-blog-lib.php lireate-blog-site-footer.inc.php lireate-blog-footer.css lireate-service-messenger-include.inc.php; do
  ln -sfn "$ROOT/pages/blog/_shared/$f" "$DOCROOT/pages/blog/_shared/$f" 2>/dev/null || true
done
ln -sfn "$ROOT/pages/services/_shared/lireate-messenger-urls.php" "$DOCROOT/pages/services/_shared/lireate-messenger-urls.php"
ln -sfn "$ROOT/pages/services/_shared/lireate-service-messenger-include.inc.php" "$DOCROOT/pages/services/_shared/lireate-service-messenger-include.inc.php"
ln -sfn "$ROOT/template/lireate/lireate-service-footer.inc.php" "$DOCROOT/template/lireate/lireate-service-footer.inc.php" 2>/dev/null || ln -sfn "$ROOT/pages/services/_shared/lireate-service-footer.inc.php" "$DOCROOT/template/lireate/lireate-service-footer.inc.php"
echo "Contacts CMS preview: http://127.0.0.1:${PORT}/kontaktyi_preview/cms-shell.php"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"
