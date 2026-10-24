/**
 * Site sahibinin panelden değiştirdiği ayarlar (adrese ?ayar eklenince açılır: src/scripts/ayar-panel.ts).
 * Kayıt sunucuda durur (public/ayar.php); kayıt yoksa ya da okunamazsa buradaki varsayılanlar geçer.
 * Sayılar 0–150 arası: 100 eski en fazladır, üstü abartmak isteyene pay. Kaymada 100 sayfayla birlikte,
 * üstü daha dik artar (125 sayfanın 2, 150 sayfanın 3 katı): 1,5 kat gözle sayfayla aynı görünüyordu.
 */
import { locales, site, theme, type Locale } from './site';

export const EN_FAZLA = 150;

export type Tema = 'koyu' | 'acik';
export type Dil = 'otomatik' | Locale;
export type Ayar = {
  site: { tema: Tema; dil: Dil };
  zemin: { kayma: number };
  harita: { acik: boolean; noktalar: number; sari: number; ucus_yogunlugu: number };
  toz: { acik: boolean; gorunurluk: number; yogunluk: number };
};

export const VARSAYILAN: Ayar = {
  site: { tema: theme === 'light' ? 'acik' : 'koyu', dil: site.languageMode === 'fixed' ? site.defaultLocale : 'otomatik' },
  zemin: { kayma: 25 },
  harita: { acik: true, noktalar: 35, sari: 80, ucus_yogunlugu: 40 },
  toz: { acik: true, gorunurluk: 70, yogunluk: 65 },
};

export const SECENEK = {
  tema: ['koyu', 'acik'] as Tema[],
  dil: ['otomatik', ...locales] as Dil[],
};

const kopya = (a: Ayar): Ayar => JSON.parse(JSON.stringify(a));

/** Sunucudan, dosyadan ya da tarayıcıdan gelen her şeyi geçerli bir ayara çevirir; tanınmayan değer varsayılanda kalır. */
export function duzelt(raw: unknown): Ayar {
  const out = kopya(VARSAYILAN) as unknown as Record<string, Record<string, unknown>>;
  if (!raw || typeof raw !== 'object') return out as unknown as Ayar;
  const src = { ...(raw as Record<string, Record<string, unknown>>) };
  if (src.genel && !src.zemin) src.zemin = src.genel; // ilk ayar dosyasında kayma [genel] altındaydı
  for (const sec of Object.keys(out)) {
    const s = src[sec];
    if (!s || typeof s !== 'object') continue;
    for (const k of Object.keys(out[sec])) {
      const d = out[sec][k], v = s[k];
      if (typeof d === 'boolean') {
        if (typeof v === 'boolean') out[sec][k] = v;
        else if (typeof v === 'string' && /^(evet|açık|acik|true|1|hayır|hayir|kapalı|kapali|false|0)$/.test(v)) out[sec][k] = /^(evet|açık|acik|true|1)$/.test(v);
      } else if (typeof d === 'number') {
        const n = typeof v === 'number' ? v : typeof v === 'string' ? parseFloat(v) : NaN;
        if (Number.isFinite(n)) out[sec][k] = Math.max(0, Math.min(EN_FAZLA, Math.round(n)));
      } else if (typeof v === 'string' && (SECENEK as Record<string, string[]>)[k]?.includes(v)) out[sec][k] = v;
    }
  }
  return out as unknown as Ayar;
}

export const ayniMi = (a: Ayar, b: Ayar) => JSON.stringify(a) === JSON.stringify(b);

/* dosyada ve panelde görünen adlar */
export const AD: Record<string, string> = {
  site: 'Site',
  tema: 'Tema',
  dil: 'İlk açılış dili',
  zemin: 'Kaydırınca zemin',
  kayma: 'Kayma',
  harita: 'Harita',
  noktalar: 'Beyaz harita noktaları',
  sari: 'Sarı ülkeler ve yollar',
  ucus_yogunlugu: 'Uçuş yoğunluğu',
  toz: 'Toz ve ışık',
  gorunurluk: 'Görünürlük',
  yogunluk: 'Toz yoğunluğu',
  koyu: 'Koyu',
  acik: 'Açık',
  otomatik: 'Otomatik',
  tr: 'Türkçe',
  en: 'İngilizce',
};

/** İndirilen dosya: hem okunur (her satırın yanında ne olduğu yazar) hem de panele geri yüklenir. */
export function dosyaYaz(a: Ayar): string {
  const yn = (b: boolean) => (b ? 'evet' : 'hayır');
  const tarih = new Date().toLocaleString('tr-TR');
  return `; Dr. Murat Vural sitesinin ayarları (${tarih})
; Bu dosyayı ayar panelinde "Dosyadan yükle" ile geri yükleyip "Kaydet"e basınca site bu ayarlarla açılır.
; Sayılar 0 ile 150 arası: 0 en az, 100 eski en fazla, 150 abartılı.

[site]
tema = ${a.site.tema}${' '.repeat(Math.max(1, 14 - a.site.tema.length))}; koyu ya da acik
dil = ${a.site.dil}${' '.repeat(Math.max(1, 15 - a.site.dil.length))}; ilk açılışta: otomatik (Türkiye saati ya da Türkçe tarayıcı → Türkçe, diğerleri İngilizce), tr ya da en

[zemin]
kayma = ${a.zemin.kayma}${' '.repeat(Math.max(1, 13 - String(a.zemin.kayma).length))}; kaydırınca zeminin kayma oranı: 0 hiç kaymaz, 25 = 4'e 1, 100 sayfayla birlikte, 125 sayfanın 2 katı, 150 sayfanın 3 katı

[harita]
acik = ${yn(a.harita.acik)}${' '.repeat(Math.max(1, 14 - yn(a.harita.acik).length))}; noktalı dünya haritası
noktalar = ${a.harita.noktalar}${' '.repeat(Math.max(1, 10 - String(a.harita.noktalar).length))}; beyaz kara noktalarının belirginliği
sari = ${a.harita.sari}${' '.repeat(Math.max(1, 14 - String(a.harita.sari).length))}; sarı ülke ışıkları ve İstanbul'dan giden yolların belirginliği
ucus_yogunlugu = ${a.harita.ucus_yogunlugu}${' '.repeat(Math.max(1, 4 - String(a.harita.ucus_yogunlugu).length))}; yollarda aynı anda yürüyen ışık sayısı (0 hiç)

[toz]
acik = ${yn(a.toz.acik)}${' '.repeat(Math.max(1, 14 - yn(a.toz.acik).length))}; projektör ışığı ve toz
gorunurluk = ${a.toz.gorunurluk}${' '.repeat(Math.max(1, 8 - String(a.toz.gorunurluk).length))}; ışığın ve toz zerrelerinin belirginliği
yogunluk = ${a.toz.yogunluk}${' '.repeat(Math.max(1, 10 - String(a.toz.yogunluk).length))}; toz zerresi miktarı
`;
}

/** INI biçimi ([bölüm], ad = değer, ';' ya da '#' sonrası açıklama) ya da JSON okur. */
export function dosyaOku(text: string): Ayar {
  const t = text.replace(/^﻿/, '').trim();
  if (t.startsWith('{')) {
    try {
      return duzelt(JSON.parse(t));
    } catch {
      /* INI gibi okunur */
    }
  }
  const raw: Record<string, Record<string, string>> = {};
  let sec = '';
  for (const line of t.split(/\r?\n/)) {
    const l = line.replace(/[;#].*$/, '').trim();
    const head = l.match(/^\[(.+)\]$/);
    if (head) sec = head[1].trim().toLowerCase();
    else {
      const kv = l.match(/^([^=]+)=(.*)$/);
      if (kv && sec) (raw[sec] ??= {})[kv[1].trim().toLowerCase()] = kv[2].trim().toLowerCase();
    }
  }
  return duzelt(raw);
}
