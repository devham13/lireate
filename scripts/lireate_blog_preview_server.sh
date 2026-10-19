#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DOCROOT="$ROOT/.dev-docroot-blog"
PORT="${LIREATE_BLOG_PREVIEW_PORT:-8890}"
mkdir -p "$DOCROOT/template/page"
ln -sfn "$ROOT/pages/blog/_preview" "$DOCROOT/blog_preview"
ln -sfn "$ROOT/template/page/news" "$DOCROOT/template/page/news"
mkdir -p "$DOCROOT/template/images/blog"
ln -sfn "$ROOT/template/images/blog/author" "$DOCROOT/template/images/blog/author"
mkdir -p "$DOCROOT/data"
ln -sfn "$ROOT/data/blog-articles-index.json" "$DOCROOT/data/blog-articles-index.json"
ln -sfn "$ROOT/pages/services/_shared/lireate-service-footer.inc.php" "$DOCROOT/footer.inc.php"
# layout resolves footer via services path
mkdir -p "$DOCROOT/pages/services/_shared"
ln -sfn "$ROOT/pages/services/_shared/lireate-service-footer.inc.php" "$DOCROOT/pages/services/_shared/lireate-service-footer.inc.php"
mkdir -p "$DOCROOT/pages/services/_shared" "$DOCROOT/pages/blog/_shared"
ln -sfn "$ROOT/pages/services/_shared/lireate-service-dark.css" "$DOCROOT/pages/services/_shared/lireate-service-dark.css"
for f in lireate-blog-config.php lireate-blog-lib.php lireate-blog-layout.inc.php lireate-blog-article.css lireate-blog-article.js; do
  ln -sfn "$ROOT/pages/blog/_shared/$f" "$DOCROOT/pages/blog/_shared/$f"
done
echo "Blog preview: http://127.0.0.1:${PORT}/blog_preview/"
exec php -S "127.0.0.1:${PORT}" -t "$DOCROOT"
