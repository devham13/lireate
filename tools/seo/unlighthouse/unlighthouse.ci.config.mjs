import config from './unlighthouse.config.mjs';

const scope = process.env.SEO_AUDIT_SCOPE || 'priorities';
if (!['homepage', 'priorities', 'sample'].includes(scope)) {
  throw new Error('SEO_AUDIT_SCOPE must be homepage, priorities or sample');
}

const priorities = [
  '/',
  '/registraciya_tovarnogo_znaka/',
  '/patent',
  '/patent_prom_obr',
  '/registraciya-brenda-odezhdy-mktu/',
  '/kak-zapatentovat-nazvanie-i-logotip/',
  '/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/',
  '/rospatent-fips-proverit-tovarnyy-znak-onlayn-besplatno/',
];

export default {
  ...config,
  ...(scope !== 'sample' ? { urls: scope === 'homepage' ? ['/'] : priorities } : {}),
  outputPath: process.env.SEO_REPORT_DIR || `${config.root}reports/ci`,
  scanner: {
    ...config.scanner,
    sitemap: scope === 'sample',
    crawler: scope === 'sample',
    maxRoutes: scope === 'sample' ? 25 : scope === 'homepage' ? 1 : priorities.length,
  },
};
