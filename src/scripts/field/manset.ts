/** Seçenek 1 · Manşet: yeni fotoğraf aşağıdan yukarı açılır, yer değişince ülke adı yukarı kayıp yenisine yer verir. */
import { gsap } from 'gsap';
import { $, $$, jumpTo, mobileReveal, pinTimeline, type Variant } from './common';

export const manset: Variant = {
  desktop() {
    const stage = $('[data-fstage]')!;
    const figs = $$('.fm-f', stage), texts = $$('.fm-t', stage), items = $$('.fm-index li', stage);
    const tl = gsap.timeline({ paused: true, defaults: { ease: 'none' } });
    gsap.set(figs.slice(1), { clipPath: 'inset(100% 0% 0% 0%)', autoAlpha: 1 });
    gsap.set(texts.slice(1), { autoAlpha: 0, y: 40 });
    gsap.set(texts[0], { autoAlpha: 1 });
    const place = (f: HTMLElement) => Number(f.dataset.place);
    tl.addLabel('p0', 0).to({}, { duration: 0.6 });
    figs.forEach((f, s) => {
      if (!s) return;
      const prev = figs[s - 1], i = place(f), changed = i !== place(prev);
      const at = `s${s}`;
      tl.addLabel(at);
      if (changed) tl.addLabel(`p${i}`, at);
      // yeni fotoğraf perde gibi açılır, içindeki kare hafifçe yerine oturur; eskisi geride kararır
      tl.to(f, { clipPath: 'inset(0% 0% 0% 0%)', duration: 0.5, ease: 'power2.inOut' }, at)
        .fromTo($('img', f), { scale: 1.08 }, { scale: 1, duration: 0.7, ease: 'power2.out' }, at)
        .to(prev, { autoAlpha: 0, duration: 0.35 }, `${at}+=0.2`);
      if (changed) {
        tl.to(texts[place(prev)], { autoAlpha: 0, y: -40, duration: 0.25 }, at).to(texts[i], { autoAlpha: 1, y: 0, duration: 0.35 }, `${at}+=0.2`);
      }
      tl.to({}, { duration: 0.6 });
    });
    // hangi yer, hangi fotoğraf: sınıflar çizelgenin konumundan
    const marks = figs.map((_, s) => (s ? tl.labels[`s${s}`] : 0));
    const sync = () => {
      const t = tl.time();
      let s = 0;
      marks.forEach((m, k) => { if (t >= m + 0.25) s = k; });
      const i = place(figs[s]), k = Number(figs[s].dataset.k);
      texts.forEach((x, j) => x.classList.toggle('on', j === i));
      items.forEach((x, j) => x.classList.toggle('on', j === i));
      $$('.fm-ticks i', texts[i]).forEach((x, j) => x.classList.toggle('on', j === k));
    };
    tl.eventCallback('onUpdate', sync);
    // perde açılırken alttaki ve üstteki birlikte görünür
    figs.forEach((f) => f.classList.add('on'));
    const st = pinTimeline(stage, tl, 0.5);
    const onJump = (e: Event) => {
      const b = (e.target as Element).closest<HTMLElement>('[data-fjump]');
      if (b) jumpTo(st, tl, `p${b.dataset.fjump}`);
    };
    stage.addEventListener('click', onJump);
    return () => {
      stage.removeEventListener('click', onJump);
      st.kill();
      tl.kill();
      gsap.set([...figs, ...texts, ...$$('img', stage)], { clearProps: 'all' });
    };
  },
  mobile: mobileReveal,
};
