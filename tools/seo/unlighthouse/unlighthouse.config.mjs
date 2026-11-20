import { fileURLToPath } from 'node:url';

const site = process.env.SEO_SITE_URL || 'https://lireate.ru';
const root = fileURLToPath(new URL('.', import.meta.url));
const browser = process.env.PUPPETEER_EXECUTABLE_PATH || process.env.CHROME_PATH;

export default {
  site,
  root,
  outputPath: process.env.SEO_REPORT_DIR || `${root}reports/site`,
  cache: false,
  scanner: {
    maxRoutes: 400,
    samples: 1,
    device: 'mobile',
    throttle: true,
    robotsTxt: true,
    sitemap: true,
    crawler: true,
    dynamicSampling: false,
    exclude: ['/admin/**', '/private_data', '/politika_konfidencialnosti', /\?/],
  },
  puppeteerClusterOptions: { maxConcurrency: 1 },
  puppeteerOptions: {
    ...(browser ? { executablePath: browser } : {}),
    ...(process.env.SEO_CHROME_NO_SANDBOX === '1'
      ? { args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'] }
      : {}),
  },
  ci: { buildStatic: true, reporter: 'jsonExpanded' },
};
