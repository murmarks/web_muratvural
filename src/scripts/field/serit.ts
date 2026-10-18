/**
 * Seçenek 2 · Şerit: dikey kaydırma şeridi sola akıtır. Ekranın ortasına yaklaşan fotoğraf aydınlanır ve rengi gelir,
 * uzaklaşan loşlaşır; yerin açıklaması da onunla birlikte belirir. Hepsi kaydırmanın o anki konumundan hesaplanır.
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { $, $$, mobileReveal, type Variant } from './common';

export const serit: Variant = {
  desktop() {
    const stage = $('[data-fstage]')!, track = $('[data-ftrack]', stage)!, bar = $('[data-fbar]', stage)!;
    const photos = $$('[data-fp]', track), groups = $$('.fs-g', track);
    // şeridin başında ilk fotoğrafın, sonunda son fotoğrafın ortası ekranın ortasında
    let x0 = 0, x1 = 0;
    const measure = () => {
      gsap.set(track, { x: 0 });
      const t = track.getBoundingClientRect().left, mid = innerWidth / 2;
      const ctr = (el: HTMLElement) => { const r = el.getBoundingClientRect(); return r.left - t + r.width / 2; };
      x0 = mid - t - ctr(photos[0]);
      x1 = mid - t - ctr(photos[photos.length - 1]);
    };
    const light = () => {
      const mid = innerWidth / 2, span = innerWidth * 0.36;
      photos.forEach((p) => {
        const r = p.getBoundingClientRect();
        const d = Math.min(1, Math.abs(r.left + r.width / 2 - mid) / span);
        const k = 1 - d * d;
        p.style.setProperty('--b', (0.38 + 0.62 * k).toFixed(3));
        p.style.setProperty('--s', (0.45 + 0.55 * k).toFixed(3));
      });
      groups.forEach((g) => {
        const r = g.getBoundingClientRect();
        const d = Math.max(0, Math.max(r.left - mid, mid - r.right)) / span;
        g.style.setProperty('--o', (1 - Math.min(1, d) * 0.7).toFixed(3));
      });
    };
    measure();
    const tw = gsap.fromTo(track, { x: () => x0 }, { x: () => x1, ease: 'none', immediateRender: true, onUpdate: light });
    const st = ScrollTrigger.create({
      animation: tw,
      trigger: stage,
      start: 'top top',
      end: () => `+=${Math.round((x0 - x1) * 1.15)}`,
      scrub: 0.6,
      pin: true,
      anticipatePin: 1,
      invalidateOnRefresh: true,
      onRefreshInit: measure,
      onUpdate: (s) => gsap.set(bar, { scaleX: s.progress }),
    });
    light();
    return () => {
      st.kill();
      tw.kill();
      gsap.set(track, { clearProps: 'all' });
    };
  },
  mobile: mobileReveal,
};
