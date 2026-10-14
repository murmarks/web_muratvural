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
  data/services.ts       hizmet ağacı (sol menü, sayfalar ve eski adres yönlendirmeleri buradan)
  components/            ServiceNav (akordiyon hizmet haritası), Breadcrumbs …
  layouts/               BaseLayout, ServiceLayout
  pages/                 /, /services/…, 404
  styles/global.css      tasarım belirteçleri
public/                  favicon, robots.txt
docs/YOL-HARITASI.md     WordPress → Astro tasarım ve içerik yol haritası
```

Eski adresler (`/menu-a1/`, `/menu-b442/` …) derlemede otomatik olarak yeni adreslere yönlendirilir.
