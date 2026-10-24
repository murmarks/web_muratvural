/**
 * Sitenin geliştirici ayarları. Ziyaretçi bunları göremez; yalnızca dil düğmesiyle dili değiştirebilir.
 * Yeni dil eklemek: `locales` listesine kodu ekleyin, `src/i18n/<kod>.json` dosyasını ve
 * `src/content/services/<kod>/` metinlerini oluşturun. Tip denetimi (`npm run check`) eksik çevirileri gösterir.
 */
export const locales = ['en', 'tr'] as const;
export type Locale = (typeof locales)[number];

export const site = {
  url: 'https://muratvural.com',
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
  /**
   * Ana sayfadaki "Sahada" fotoğraflarının efekti (bilgisayar ekranında). 'tul' seçildi (2026-10-18);
   * 'isik', 'panjur', 'odak' denenmiş diğer seçenekler; 'none' eski sade gösterim.
   * Derlerken FX ortam değişkeni bunu geçersiz kılar (önizleme için).
   */
  fieldEffect: 'tul' as 'tul' | 'isik' | 'panjur' | 'odak' | 'none',
  /**
   * Sitenin teması: 'dark' koyu tasarım (canlıda bu). 'light' açık zemin: 2026-10-20'de denendi, sonraya bırakıldı (2026-10-24).
   * İkisi de kodda durur (src/styles/global.css). Derlerken THEME ortam değişkeni bunu geçersiz kılar (önizleme için).
   */
  theme: 'dark' as Theme,
  contact: {
    email: 'contact@muratvural.com',
    whatsapp: '+84934000150',
    whatsappLabel: '+84 934 000 150',
    timeZone: 'UTC+07:00',
  },
};

export type Theme = 'light' | 'dark';
/** Derlemede kullanılan tema: THEME ortam değişkeni varsa o, yoksa site.theme. */
export const theme: Theme = process.env.THEME === 'dark' || process.env.THEME === 'light' ? process.env.THEME : site.theme;
/** Tarayıcı çubuğunun rengi, sayfa zemini ile aynı. */
export const themeColor = theme === 'light' ? '#f5f2ec' : '#0b0b0c';

export const isLocale = (x: unknown): x is Locale => (locales as readonly unknown[]).includes(x);
