/**
 * "Sahada, salonun içinde" bölümünün seçenekleri (Manşet, Şerit, Rota, Baskı) için ortak veri.
 * Fotoğraflar ortak renk işleminden geçmiş kopyalar (scripts/grade-photos.py → public/photos/g/).
 */
import type { Locale } from '../config/site';
import { places, type Place } from './home';
import { photo, type Photo } from './photos';

export type FieldVariant = 'current' | 'manset' | 'serit' | 'rota' | 'baski';
export const fieldVariant = (process.env.FIELD || 'current') as FieldVariant;

const graded = (p: Photo): Photo => {
  const name = p.src.split('/photos/')[1].split('.webp')[0];
  return { ...photo(`g/${name}`), lo: p.lo };
};

/** şehrin koordinatı [enlem, boylam] ve haritadaki anahtarı */
const where: Record<string, { ll: [number, number]; key: string }> = {
  Kosovo: { ll: [42.66, 21.17], key: 'kosovo' },
  Moldova: { ll: [47.01, 28.86], key: 'moldova' },
  Azerbaijan: { ll: [40.41, 49.87], key: 'azerbaijan' },
  Georgia: { ll: [41.72, 44.79], key: 'georgia' },
  Croatia: { ll: [45.81, 15.98], key: 'croatia' },
  'Bosnia and Herzegovina': { ll: [43.86, 18.41], key: 'bosnia' },
  Libya: { ll: [32.89, 13.19], key: 'libya' },
  Türkiye: { ll: [36.9, 30.71], key: 'turkey' },
  Vietnam: { ll: [10.76, 106.66], key: 'vietnam' },
};

export interface FieldPlace extends Place {
  key: string;
  ll: [number, number];
}
export const fieldPlaces: FieldPlace[] = places.map((pl) => ({ ...pl, photos: pl.photos.map(graded), ...where[pl.country.en] }));

/** batıdan doğuya: Rota seçeneğinde yol bu sırayla çizilir */
export const westToEast = [...fieldPlaces].sort((a, b) => a.ll[1] - b.ll[1]);

/** 42°40′ K · 21°10′ D */
export function dms([lat, lon]: [number, number], lang: Locale) {
  const f = (v: number) => `${Math.floor(v)}°${String(Math.round((v % 1) * 60)).padStart(2, '0')}′`;
  const [n, e] = lang === 'tr' ? ['K', 'D'] : ['N', 'E'];
  return `${f(lat)} ${n} · ${f(lon)} ${e}`;
}
