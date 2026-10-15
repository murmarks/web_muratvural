/**
 * Fotoğraf yardımcısı. Her fotoğraf gerçek piksel boyutuyla gelir (scripts/photo-sizes.mjs üretir),
 * böylece hiçbir fotoğraf çekildiğinden büyük gösterilmez.
 */
import sizes from './photo-sizes.json';

export interface Photo {
  src: string;
  /** küçük önizleme (public/photos/**\/t/) */
  th: string;
  w: number;
  h: number;
  /** düşük çözünürlüklü arşiv karesi: üstüne daha belirgin bir film greni gelir */
  lo: boolean;
  /** fotoğrafın altındaki açıklama (fotoğrafın üstüne yazı konmaz) */
  cap?: string;
}

/**
 * Eğitim videolarından alınmış arşiv kareleri hep 1200×751 boyutunda (Antalya salonu ve 3.x eğitim sayfaları).
 * Netlikleri düşük olduğu için "arşiv" görünümüyle (daha belirgin gren, renk ayarı yok) gösterilir.
 */
const isArchiveFrame = ([w, h]: Size) => w === 1200 && h === 751;

/**
 * Küçük bir kolajdan büyütülmüş kareler (Vietnam, Phano semineri) ve eski bir kompakt makinenin karesi
 * (Hakkında sayfasındaki kürsü fotoğrafı): onlar da arşiv görünümüyle gösterilir.
 */
const enlarged = new Set(['vietnam-1', 'vietnam-2', 'vietnam-3', 'about/podium']);

/** [genişlik, yükseklik, içerik özeti] */
type Size = [number, number, string];
const SIZES = sizes as unknown as Record<string, Size>;

/** `photo('kosovo')`, `photo('services/a1_1')` */
export function photo(name: string, cap?: string): Photo {
  const size = SIZES[name];
  if (!size) throw new Error(`Fotoğraf bulunamadı: ${name} (public/photos/${name}.webp)`);
  const slash = name.lastIndexOf('/');
  const dir = name.slice(0, slash + 1), base = name.slice(slash + 1);
  return {
    src: `/photos/${name}.webp?v=${size[2]}`,
    th: `/photos/${dir}t/${base}.webp?v=${size[2]}`,
    w: size[0],
    h: size[1],
    lo: isArchiveFrame(size) || enlarged.has(name),
    cap,
  };
}

export const servicePhoto = (n: string, cap?: string) => photo(`services/${n}`, cap);
export const hasServicePhoto = (n: string) => `services/${n}` in SIZES;
