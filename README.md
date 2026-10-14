# PeBuC web sitesi (Astro)

pebuc.site'ın WordPress'ten Astro'ya taşınmış yeni sürümü.

## Dallar

| Dal | Ne var | Kim yazar |
|---|---|---|
| `wordpress` | Eski WordPress kurulumunun arşivi. Tema, görseller ve sayfa yapısı için referans. Değiştirilmez. | Bir kez oluşturuldu |
| `source` | Bu proje: Astro kaynak kodu, içerik, görseller. Tüm geliştirme burada. | Biz |
| `main` | Sadece derlenmiş statik site (`dist/`). Hosting buradan yayınlar. Elle commit atılmaz. | GitHub Actions |

## Geliştirme

```sh
npm install
npm run dev      # http://localhost:4321
npm run check    # tip kontrolü
npm run build    # dist/ klasörüne derler
npm run preview  # derlenmiş siteyi yerelde açar
```

## Yayın

`source` dalına push yapıldığında `.github/workflows/deploy.yml` çalışır: siteyi derler ve `dist/`
içeriğini `main` dalına tek commit olarak yazar (önceki sürümün üzerine).
Elle tetiklemek için GitHub'da **Actions → Derle ve main'e yayınla → Run workflow**.

Actions kullanılamazsa aynı işi elle yapmak için:

```sh
npm ci && npm run build
cd dist
git init -b main
git add -A
git commit -m "Yayın: $(git -C .. rev-parse --short HEAD)"
git push --force https://github.com/murmarks/web_muratvural.git main
```

## Yapı

```
src/
  config/site.ts         dil listesi, varsayılan dil, dil modu (sabit/bölgesel), marka, iletişim bilgileri
  i18n/en.json, tr.json  arayüz metinleri; i18n/index.ts dil yardımcıları
  content/services/      43 hizmet sayfasının eski sitelerden alınan metinleri (<dil>/<wp>.json)
  data/services.ts       hizmet ağacı (sol harita, sayfalar ve eski adres yönlendirmeleri buradan)
  data/home.ts           ana sayfa: sahadan fotoğraflar, kariyer, hizmet alanları
  data/photos.ts         fotoğraflar; gerçek boyutları data/photo-sizes.json'da
  components/            üst çubuk, menü, iletişim bandı, hizmet haritası, galeri
  layouts/BaseLayout     bütün sayfaların iskeleti
  pages/                 / (dil seçici), /[dil]/ ana sayfa, /[dil]/services/…, 404
  scripts/               tarayıcı betikleri (ana sayfa hareketleri GSAP ile, hizmet haritası, galeri)
  styles/                global.css (ortak dil), home.css, service.css
public/photos/           fotoğraflar ve t/ altında küçük önizlemeleri
scripts/photo-sizes.mjs  fotoğraf eklenince boyut listesini yeniler
docs/YOL-HARITASI.md     WordPress → Astro tasarım ve içerik yol haritası
```

Adresler: `/tr/…` ve `/en/…`. Kök adres (`/`) ziyaretçiyi daha önce seçtiği dile, yoksa `site.ts`'deki dil moduna göre
Türkçe ya da İngilizce sayfaya gönderir. Yeni dil eklemek için `site.ts` içindeki listeye kodu, `i18n/<kod>.json` dosyasını ve
`content/services/<kod>/` metinlerini eklemek yeter; `npm run check` eksikleri gösterir.

Eski adresler (`/menu-a1/`, `/menu-b442/` …) derlemede otomatik olarak yeni İngilizce adreslere yönlendirilir.

## Metin dili

Metinler okurun vereceği hükmü onun yerine vermez; hükmü taşıyan bilgiyi dik ve eksiksiz söyler.

- Güçlü bilgi silinmez, tonu ayarlanır: "≈800 kişiyi aynı anda yönetti" yerine "≈800 kişilik uluslararası satış ve pazarlama ekipleri". Rakam, şirket, görev, ülke ve şehir çekinmeden yazılır.
- "Pek çok", "lider" gibi okurun kendi varacağı sonucu söyleyen sözler yerine o sonucu doğuran somut bilgi konur ("Farklı bayraklar. Tek ekip.").
- Tasarımın amacı ya da sayfanın nasıl yapıldığı sayfaya yazılmaz; zaten görünen şey sayılmaz ("4 fotoğraf" gibi).
- Eski sitelerden gelen metinler (hizmet sayfaları, alan girişleri, slogan, çağrı cümlesi, kısa özgeçmiş) sahibinin kendi metnidir; çevrilmez, olduğu gibi kullanılır.
