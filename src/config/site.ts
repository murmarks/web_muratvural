/**
 * Sitenin geliştirici ayarları. Ziyaretçi bunları göremez; yalnızca dil düğmesiyle dili değiştirebilir.
 * Yeni dil eklemek: `locales` listesine kodu ekleyin, `src/i18n/<kod>.json` dosyasını ve
 * `src/content/services/<kod>/` metinlerini oluşturun. Tip denetimi (`npm run check`) eksik çevirileri gösterir.
 */
export const locales = ['en', 'tr'] as const;
export type Locale = (typeof locales)[number];

export const site = {
  url: 'https://pebuc.site',
  /** Hiçbir kural uymazsa açılan dil. */
  defaultLocale: 'en' as Locale,
  /**
   * Ziyaretçi henüz dil seçmediyse sitenin hangi dilde açılacağı:
   * - 'fixed': herkes `defaultLocale` ile açar.
   * - 'regional': aşağıdaki `regional` kurallarına göre (Türkiye'den gelen Türkçe, diğerleri İngilizce).
   * Statik sitede ülke, tarayıcının saat dilimi ve dilinden tahmin edilir.
   */
  languageMode: 'regional' as 'fixed' | 'regional',
  regional: {
    tr: { timeZones: ['Europe/Istanbul'], languages: ['tr'] },
  } as Partial<Record<Locale, { timeZones: string[]; languages: string[] }>>,
  /**
   * Görünen marka. 'personal' = Dr. Murat Vural. 'pebuc' eski PeBuC adını ve logosunu geri getirir;
   * ikisi de kodda durur, hangisinin görüneceği buradan seçilir.
   */
  brand: 'personal' as 'personal' | 'pebuc',
  contact: {
    email: 'contact@muratvural.com',
    whatsapp: '+84934000150',
    whatsappLabel: '+84 934 000 150',
    timeZone: 'UTC+07:00',
  },
};

export const isLocale = (x: unknown): x is Locale => (locales as readonly unknown[]).includes(x);
