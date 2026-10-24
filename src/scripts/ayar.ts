/**
 * Panel ayarlarının sayfadaki hali (src/config/ayar.ts). İlk değer tarayıcıdaki son kayıttan gelir;
 * sunucudaki kayıt gelince (window.mvAyar, AyarHead) o geçer. Zemin gibi parçalar dinle() ile haber alır.
 * Adreste ?ayar varsa ya da şifre bu sekmede girildiyse panel yüklenir.
 */
import { duzelt, type Ayar } from '../config/ayar';

declare global {
  interface Window {
    mvAyar?: Promise<unknown>;
    mvAyarOnbellek?: unknown;
    mvSayfa?: (s: unknown) => void;
    mvPanel?: boolean;
    mvTaslak?: unknown;
  }
}

let simdiki: Ayar = duzelt(window.mvTaslak ?? window.mvAyarOnbellek);
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

let panelAcik = false;
try {
  panelAcik = sessionStorage.getItem('mv-panel') === '1'; // şifre bu sekmede girildi, panel sayfadan sayfaya açık kalır
} catch {
  /* sessionStorage yok */
}
if (panelAcik || new URLSearchParams(location.search).has('ayar')) {
  window.mvPanel = true;
  import('./ayar-panel');
}
