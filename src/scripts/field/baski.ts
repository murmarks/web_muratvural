/**
 * Seçenek 4 · Baskı: her yeni baskı sağdan gelip öncekilerin üstüne konur; alttakiler kararır,
 * üç baskıdan derindekiler görünmez olur. Geri kaydırınca baskı geldiği yere geri kalkar.
 */
import { gsap } from 'gsap';
import { $, $$, mobileReveal, pinTimeline, type Variant } from './common';

export const baski: Variant = {
  desktop() {
    const stage = $('[data-fstage]')!;
    const prints = $$('.fb-pr', stage), texts = $$('.fb-t', stage);
    const papers = prints.map((p) => $('.fb-paper', p)!);
    const rest = prints.map((p) => {
      const cs = getComputedStyle(p);
      return { x: parseFloat(cs.getPropertyValue('--dx')), y: parseFloat(cs.getPropertyValue('--dy')), rotation: parseFloat(cs.getPropertyValue('--r')) };
    });
    prints.forEach((p, s) => gsap.set(p, { ...rest[s], zIndex: s, autoAlpha: s ? 0 : 1 }));
    gsap.set(papers, { '--dim-b': 1 });
    gsap.set(texts, { autoAlpha: 0 });
    gsap.set(texts[0], { autoAlpha: 1 });
    const place = (el: HTMLElement) => Number(el.dataset.place);
    const tl = gsap.timeline({ paused: true, defaults: { ease: 'none' } });
    tl.to({}, { duration: 0.6 });
    prints.forEach((p, s) => {
      if (!s) return;
      const at = `s${s}`, i = place(p), prev = place(prints[s - 1]);
      tl.addLabel(at)
        .fromTo(
          p,
          { autoAlpha: 1, x: rest[s].x + innerWidth * 0.55, y: rest[s].y + 60, rotation: rest[s].rotation + 11 },
          { ...rest[s], autoAlpha: 1, duration: 0.6, ease: 'power2.out', immediateRender: false },
          at,
        );
      // alttakiler: bir kat aşağı inen her baskı biraz daha kararır; dördüncü kattan sonra görünmez
      [0.74, 0.56, 0.42].forEach((b, d) => {
        const k = s - 1 - d;
        if (k >= 0) tl.to(papers[k], { '--dim-b': b, duration: 0.4 }, `${at}+=0.2`);
      });
      if (s - 4 >= 0) tl.to(prints[s - 4], { autoAlpha: 0, duration: 0.3 }, `${at}+=0.3`);
      if (i !== prev) tl.to(texts[prev], { autoAlpha: 0, y: -24, duration: 0.25 }, at).fromTo(texts[i], { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: 0.3, immediateRender: false }, `${at}+=0.25`);
      tl.to({}, { duration: 0.6 });
    });
    const st = pinTimeline(stage, tl, 0.5);
    return () => {
      st.kill();
      tl.kill();
      gsap.set([...prints, ...texts, ...papers], { clearProps: 'all' });
    };
  },
  mobile() {
    const undo = mobileReveal();
    const tws = $$('.fmob-baski .fmob-p').map((p, k) =>
      gsap.from(p, { x: (k % 2 ? 1 : -1) * 60, rotation: (k % 2 ? 1 : -1) * 8, opacity: 0, ease: 'none', scrollTrigger: { trigger: p, start: 'top 95%', end: 'top 60%', scrub: 0.6 } }),
    );
    return () => { undo(); tws.forEach((t) => t.scrollTrigger?.kill()); };
  },
};
