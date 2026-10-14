// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import { legacyServiceRedirects } from './src/data/services.ts';
import { locales, site } from './src/config/site.ts';

export default defineConfig({
  site: site.url,
  trailingSlash: 'always',
  integrations: [
    sitemap({
      // her sayfa diğer dillerdeki karşılığıyla birlikte listelenir (hreflang)
      i18n: { defaultLocale: site.defaultLocale, locales: Object.fromEntries(locales.map((l) => [l, l])) },
      // eski adres yönlendirmeleri ve dil seçici kök sayfa haritaya girmez
      filter: (page) => locales.some((l) => page.startsWith(`${site.url}/${l}/`)),
    }),
  ],
  // Eski WordPress adresleri (/menu-a1/ gibi) yeni adreslere yönlenir; Google sıralaması korunur.
  redirects: legacyServiceRedirects(),
  prefetch: { prefetchAll: true, defaultStrategy: 'hover' },
});
