/**
 * Arayüz metinleri ve dil yardımcıları. Her dilin metinleri kendi JSON dosyasında durur;
 * İngilizce dosya anahtarların tam listesidir, diğer diller aynı anahtarları taşımak zorundadır.
 */
import { locales, site, type Locale } from '../config/site';
import en from './en.json';
import tr from './tr.json';

export type UIKey = keyof typeof en;
const dict: Record<Locale, Record<UIKey, string>> = { en, tr };

/** `t(lang)('key')` → o dildeki metin. */
export const useT = (lang: Locale) => (key: UIKey) => dict[lang][key] ?? dict[site.defaultLocale][key] ?? key;

/** Dil kodlarının kendi dilindeki adları (düğmede kısaltma, ekran okuyucuda tam ad). */
export const localeNames: Record<Locale, string> = { en: 'English', tr: 'Türkçe' };

/** Aynı sayfanın başka bir dildeki adresi: /en/services/x/ → /tr/services/x/ */
export function switchLocalePath(pathname: string, to: Locale) {
  const parts = pathname.split('/');
  if ((locales as readonly string[]).includes(parts[1])) parts[1] = to;
  else parts.splice(1, 0, to);
  return parts.join('/');
}

export const localePath = (lang: Locale, path = '') => `/${lang}/${path.replace(/^\/+/, '')}`;

export { locales, type Locale };
