import config from './unlighthouse.config.mjs';

export default {
  ...config,
  urls: ['/'],
  outputPath: process.env.SEO_REPORT_DIR || `${config.root}reports/smoke`,
  scanner: { ...config.scanner, sitemap: false, crawler: false, maxRoutes: 1 },
};
