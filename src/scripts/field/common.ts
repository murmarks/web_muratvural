/** Seçeneklerin ortak parçaları: sabitlenen sahne, dilim başına kaydırma, yer listesinden atlama. */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export const $ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => root.querySelector<T>(s);
export const $$ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => [...root.querySelectorAll<T>(s)];
export interface Variant {
  desktop: () => () => void;
  mobile?: () => () => void;
}

/**
 * Zaman çizelgesini sahneye bağlar: sahne sabitlenir, kaydırma çizelgeyi ilerletir (geri kaydırınca geri sarar).
 * `per`: çizelgenin bir birimi kaç ekran yüksekliği kaydırmaya denk gelir.
 */
export function pinTimeline(stage: HTMLElement, tl: gsap.core.Timeline, per = 0.5) {
  return ScrollTrigger.create({
    animation: tl,
    trigger: stage,
    start: 'top top',
    end: () => `+=${Math.round(tl.duration() * innerHeight * per)}`,
    scrub: 0.6,
    pin: true,
    anticipatePin: 1,
  });
}

/** listedeki bir yere tıklanınca sayfa o yerin dilimine kayar */
export function jumpTo(st: ScrollTrigger, tl: gsap.core.Timeline, label: string) {
  const p = tl.labels[label] / tl.duration();
  scrollTo({ top: st.start + (st.end - st.start) * p + 2, behavior: 'smooth' });
}

/** telefonda: her yer ekrana girerken kaydırmayla gelir */
export function mobileReveal() {
  const tws = $$('[data-fmob]').map((el) =>
    gsap.from(el.children, { y: 36, opacity: 0, stagger: 0.12, ease: 'none', scrollTrigger: { trigger: el, start: 'top 92%', end: 'top 50%', scrub: 0.6 } }),
  );
  return () => tws.forEach((t) => t.scrollTrigger?.kill());
}
