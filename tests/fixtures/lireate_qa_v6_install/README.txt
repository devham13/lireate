Offline packed fixtures for scripts/test_lireate_qa_v6_install.py (no git on install host).

Load tests/fixtures/lireate_qa_v6_install/blobs.b85.json — base85+gzip payloads:
- contacts-page.inc, partners-page.inc, service-footer.inc (PR #131 / prod blobs)
- contacts-a4ca4e8.inc, partners-a4ca4e8.inc, blog-list-a4ca4e8.inc (host after QA V6 a4ca4e8)
- asset-deploy-* (ASSETS as on host after a4ca4e8)
- tool-deploy-<sha> (13 patent tools at a4ca4e8; strip perf+v7 to verify)
- tool-<sha> (pre-QA baselines), shared-blog-list-prod.inc, blog CMS fragments