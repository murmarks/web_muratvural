/**
 * Panel ayarlarının sayfadaki hali (src/config/ayar.ts). İlk değer tarayıcıdaki son kayıttan gelir;
 * sunucudaki kayıt gelince (window.mvAyar, AyarHead) o geçer. Zemin gibi parçalar dinle() ile haber alır.
 * Adreste ?ayar varsa panel yüklenir.
 */
import { duzelt, type Ayar } from '../config/ayar';

declare global {
  interface Window {
    mvAyar?: Promise<unknown>;
    mvAyarOnbellek?: unknown;
    mvSayfa?: (s: unknown) => void;
    mvPanel?: boolean;
  }
}

let simdiki: Ayar = duzelt(window.mvAyarOnbellek);
const dinleyenler = new Set<(a: Ayar) => void>();

export const ayar = () => simdiki;

/** Ayarı sayfaya uygular: tema ve zemin hemen değişir (panel önizlemesi de bunu kullanır). */
export function uygula(a: Ayar) {
  simdiki = a;
  window.mvSayfa?.(a);
  dinleyenler.forEach((f) => f(a));
}

export function dinle(f: (a: Ayar) => void) {
  dinleyenler.add(f);
}

window.mvAyar?.then((s) => {
  if (s && !window.mvPanel) uygula(duzelt(s));
});

if (new URLSearchParams(location.search).has('ayar')) {
  window.mvPanel = true;
  import('./ayar-panel');
}
