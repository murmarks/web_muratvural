# PeBuC: WordPress → Astro tasarım ve içerik yol haritası

Son güncelleme: 2026-10-14

## 1. Bugünkü durum

| Dal | Durum |
|---|---|
| `wordpress` | Eski kurulumun birebir arşivi (`master` ile aynı commit). Tema, görseller ve sayfa yapısı buradan okunur. |
| `source` | Astro 7 iskeleti, hizmet ağacı, akordiyon hizmet haritası, eski adres yönlendirmeleri, yayın akışı. |
| `main` | GitHub Actions'ın ürettiği derlenmiş site. Her `source` push'unda yeniden yazılır. |

Tasarım prototipleri (A Atlas, B Dosya, C Panel) ayrı bir sayfada yayınlandı; seçim bekleniyor.

## 2. Eski sitede korunacak şeyler

Bunlar tasarımdan bağımsız olarak her yönde korunur:

1. **Ana sayfa hero'su ve ilk görseli.** Dönen görsellerin ilki çok uluslu ekip fotoğrafı (`team-1-scaled-1.webp`, siyah-beyaz, Dr. Murat Vural ön sırada renkli). Yeni sitede de ilk görsel bu olacak ve ayrıca vurgulanacak (halka/işaret + isim etiketi).
2. **Gerçek iş fotoğrafları.** `wp-content/uploads` içindeki saha fotoğrafları (Kosova, Moldova, Azerbaycan, Gürcistan, Hırvatistan, Vietnam eğitimleri ve toplantıları) stok görsellerin önüne konur. Çerçeveli sürümlerin sarı çerçevesi kırpılır, altyazıdaki şehir ve eğitim adı metne taşınır.
3. **Sol paneldeki hizmet haritası.** Her hizmet sayfasında görünür; bulunulan dal kendiliğinden açılır, bulunulan sayfa işaretlenir. Yeni sitede ek olarak: konum çubuğu (breadcrumb), mobilde açılır panel, isteğe bağlı arama.
4. **Mesajlar.** "Enhancing Business Performance through Strategy and Insight", "What we can do for you", "Request a free assessment", kurucu hikâyesi, "Competitors are getting stronger…" çağrısı.
5. **Eski adresler.** `/menu-a1/` gibi tüm hizmet adresleri ve blog yazılarının eski adresleri yeni adreslere yönlenir.

## 3. Eduma teması → Astro bileşenleri

| Eski (Eduma + Elementor + eklentiler) | Yeni bileşen (`src/components/`) | Not |
|---|---|---|
| Üst menü, logo, "Free assessment" butonu | `SiteHeader.astro`, `LanguageSwitch.astro` | Mobilde açılır menü. Dil düğmesi her sayfada. |
| Revolution Slider (ana sayfa) | `HeroSlideshow.astro` | CSS geçişleri + küçük bir JS adası. İlk görsel hemen, diğerleri sonra yüklenir. |
| Ana sayfa Elementor bölümleri | `HomeIntro`, `ServiceAreas`, `FounderTeaser`, `AssessmentCta` | Seçilen tasarıma göre şekillenir. |
| Sol menü (akordiyon) | `ServiceNav.astro` ✓, `Breadcrumbs.astro` ✓ | `source` dalında hazır, JS'siz çalışıyor. |
| Hizmet sayfaları (`menu-xx`) | `ServiceLayout.astro` ✓ + `ServiceGallery.astro` | Görseller dosya adından eşleşir: `a1_1`, `a1_2` → 1.1 sayfası. |
| Blog yazıları | `blog` koleksiyonu, `PostCard.astro`, `/blog/[slug]`, RSS | 14 yazı. Eski Türkçe eğitim haberi adresleri yönlendirilir. |
| Kurucu sayfası (`dr-murat-vural`) | `/founder/` sayfası | Uzun metin Markdown'da. |
| İletişim (WPForms / Contact Form 7) | `ContactForm.astro` | Statik sitede form için Web3Forms veya Formspree. E-posta, WhatsApp ve saat dilimi korunur. |
| Randevu (Amelia) | Cal.com / Calendly bağlantısı | Karar bekliyor (bkz. 8). |
| Etkinlikler (`ev_*.webp`) | İsteğe bağlı `events` koleksiyonu | Karar bekliyor. |
| LearnPress, WooCommerce, giriş/kayıt sayfaları | Taşınmaz | Ürünler tema demosu (lorem ipsum). |
| Alt bilgi | `SiteFooter.astro` | İletişim, hizmet alanları, dil. |
| Roboto / Roboto Slab | Seçilen tasarımın yazı tipleri, siteyle birlikte barındırılır | Google Fonts'a dış istek olmaz. |

## 4. `source` dalında Astro yapısı

```
src/
  config/site.ts          dil listesi, varsayılan dil, dil modu, iletişim bilgileri
  i18n/
    en.json  tr.json      arayüz metinleri (menü, butonlar, etiketler)
    utils.ts              t(), sayfanın diğer dildeki adresi, hreflang
  content.config.ts       koleksiyon şemaları
  content/
    services/en/…  services/tr/…   her hizmet bir Markdown dosyası
    blog/en/…      blog/tr/…
    pages/en/…     pages/tr/…      kurucu, şartlar ve koşullar
  data/services.ts ✓      hizmet ağacı (numara, başlık, adres, eski WP kısa adı)
  assets/photos/…         orijinal fotoğraflar; Astro AVIF/WebP + srcset üretir
  components/ layouts/ pages/[lang]/…
public/                   favicon, robots.txt, llms.txt
```

### İki dil ve sonradan dil ekleme

- Astro'nun dahili `i18n` yönlendirmesi: `/en/...` ve `/tr/...`. Dil listesi tek yerde (`site.ts`); düğme, adresler, sitemap ve `hreflang` bu listeden üretilir. Yeni dil eklemek: listeye kodu eklemek + `i18n/<kod>.json` + `content/*/<kod>/` dosyaları.
- Varsayılan dili geliştirici seçer:
  - `languageMode: 'fixed'` → herkes `defaultLocale` ile açar.
  - `languageMode: 'regional'` → Türkiye'den gelenler Türkçe, diğerleri İngilizce. Statik sitede tahmin tarayıcı dili ve saat dilimiyle (`Europe/Istanbul`) yapılır. Gerçek IP tabanlı ülke tespiti için site Cloudflare üzerinden yayınlanmalı; o zaman kural sunucu tarafına taşınır.
- Ziyaretçi dili değiştirirse seçimi tarayıcıda saklanır ve sonraki ziyaretlerde önceliklidir.
- Türkçe çeviri hazırlanır, site sahibi kontrol eder.

### Performans hedefleri

- Sayfa başına JavaScript 30 KB altında; animasyonlar CSS ile, `prefers-reduced-motion` desteği.
- İlk görsel (`team`) önceden yüklenir, diğer görseller tembel yüklenir. Hedef: Lighthouse performans 95+, LCP < 2 sn.
- Uploads klasöründeki 84 MB görsel yerine yalnızca kullanılan orijinaller alınır; WordPress'in küçük kopyaları atılır.

## 5. İçeriği taşıma

Veritabanı depoda yok ve canlı site (pebuc.site) bu çalışma ortamından erişilemiyor (ağ politikası). Yazı ve sayfa metinleri için şunlardan biri gerekiyor:

1. **WordPress dışa aktarma XML'i** (Yönetim paneli → Araçlar → Dışa Aktar → Tüm içerik) projeye yüklenir. Önerilen yol.
2. Ya da Hostinger / UpdraftPlus veritabanı yedeği.

Bir betik XML'i okuyup her yazıyı ve hizmet sayfasını Markdown'a çevirir; Elementor'un fazladan HTML'i temizlenir, görsel bağlantıları `assets/photos` içindeki dosyalara çevrilir.

## 6. Yayın akışı

- `source` dalına push → `.github/workflows/deploy.yml` → `npm ci`, `astro check`, `astro build` → `dist/` içeriği `main` dalına tek commit olarak yazılır.
- Hosting `main` dalından yayınlar. Seçenekler: GitHub Pages (alan adı için `public/CNAME`), Hostinger (Actions'a FTP/SSH adımı eklenir) veya Cloudflare Pages (bölgesel dil için en uygun).

## 7. Sıradaki adımlar

1. **Tasarım seçimi** (A, B, C ya da karışım).
2. Tasarım belirteçleri, yazı tipleri, `SiteHeader`, `SiteFooter`, dil düğmesi ve `i18n` yönlendirmesi.
3. Ana sayfa: hero (ilk görsel ekip fotoğrafı), hizmet alanları, kurucu, çağrı bölümü.
4. Hizmet sayfaları: 43 sayfa, galeri, sol harita, konum çubuğu.
5. İçerik aktarımı (XML gelince), blog ve kurucu sayfası.
6. Türkçe çeviri.
7. Yönlendirme tablosu, sitemap, `hreflang`, Search Console; alan adını yeni siteye çevirme.

## 8. Açık kararlar

1. Tasarım yönü.
2. İçerik kaynağı: XML dışa aktarma dosyasını yükleyebilir misiniz?
3. Yayın yeri: GitHub Pages, Hostinger ya da Cloudflare Pages.
4. Randevu, kurslar ve etkinlikler yeni sitede kalsın mı?
5. Varsayılan dil modu: sabit (hangi dil?) ya da bölgesel.
