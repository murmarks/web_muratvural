// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import { legacyServiceRedirects } from './src/data/services.ts';

export default defineConfig({
  site: 'https://pebuc.site',
  trailingSlash: 'always',
  integrations: [sitemap()],
  // Eski WordPress adresleri (/menu-a1/ gibi) yeni adreslere yönlenir; Google sıralaması korunur.
  redirects: legacyServiceRedirects(),
  prefetch: { prefetchAll: true, defaultStrategy: 'hover' },
});
