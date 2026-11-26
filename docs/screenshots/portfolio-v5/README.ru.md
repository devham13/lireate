# Portfolio V5 — превью на живом HTML + CSS/JS PR

Скриншоты: инъекция `lireate-portfolio-v5.css/js` на прод через Puppeteer (`scripts/capture_portfolio_v5_prod_screenshots.mjs`).

Повтор:

```bash
export LIREATE_PUBLIC_SITE_HOST="${LIREATE_PUBLIC_SITE_HOST:-}"  # prod host, e.g. public site
cd scripts && node capture_portfolio_v5_prod_screenshots.mjs
```

Контраст 14 кейсов (Playwright): `python3 -m unittest scripts.test_portfolio_v5_case_contrast.PortfolioV5ContrastLive -v`