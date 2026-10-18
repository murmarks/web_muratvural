/**
 * Seçenek 3 · Rota: nokta harita, kamera batıdan doğuya şehirden şehre gider; geçilen yol sarı çizgiyle kalır.
 * Uzak bir şehre geçerken kamera geri çekilir, bölgenin tamamı görünür, sonra yeni şehre yaklaşır.
 * Fotoğraf sağda; ince bir çizgi şehri fotoğrafa bağlar. Hepsi kaydırmaya bağlı, geri kaydırınca yol da geri silinir.
 */
import { gsap } from 'gsap';
import map from '../../data/field-map.json';
import { $, $$, jumpTo, mobileReveal, pinTimeline, type Variant } from './common';

type XY = [number, number];
/** harita satır satır sıkıştırılmış: ".12l5w3" = 12 boş, 5 kara, 3 çalışılan ülke noktası (scripts: field-map-uret.mjs) */
const LAND: XY[] = [], WORKED: XY[] = [];
map.rows.forEach((row, r) => {
  let c = 0;
  for (const [, t, n] of row.matchAll(/([.lw])(\d*)/g)) {
    const k = Number(n || 1);
    if (t !== '.') for (let j = 0; j < k; j++) (t === 'l' ? LAND : WORKED).push([map.step / 2 + (c + j) * map.step, map.step / 2 + r * map.step]);
    c += k;
  }
});
const ZOOM = 2;

/** iki şehir arası hafif kavisli yol */
function arc(a: XY, b: XY, n = 48): XY[] {
  const mx = (a[0] + b[0]) / 2, my = (a[1] + b[1]) / 2, dx = b[0] - a[0], dy = b[1] - a[1];
  const k = 0.12, cx = mx - dy * k, cy = my + dx * k;
  return Array.from({ length: n + 1 }, (_, i) => {
    const t = i / n, u = 1 - t;
    return [u * u * a[0] + 2 * u * t * cx + t * t * b[0], u * u * a[1] + 2 * u * t * cy + t * t * b[1]] as XY;
  });
}

export const rota: Variant = {
  desktop() {
    const stage = $('[data-fstage]')!, cv = $<HTMLCanvasElement>('[data-fmap]', stage)!, lead = $<SVGLineElement>('[data-flead]', stage)!;
    const cx = cv.getContext('2d')!;
    const cities = (JSON.parse(stage.dataset.cities!) as { key: string; name: string; lang: string }[]).map((c) => ({ ...c, p: (map.cities as unknown as Record<string, XY>)[c.key] }));
    const legs = cities.slice(1).map((c, i) => arc(cities[i].p, c.p));
    const figs = $$('.fr-f', stage), caps = $$('.fr-cap', stage);
    const cam = { t: 0 };
    let W = 0, H = 0, dpr = 1;

    const view = () => {
      const i = Math.floor(cam.t), f = cam.t - i, a = cities[i].p, b = cities[Math.min(i + 1, cities.length - 1)].p;
      const dist = Math.hypot(b[0] - a[0], b[1] - a[1]);
      const e = f < 0.5 ? 2 * f * f : 1 - (-2 * f + 2) ** 2 / 2;
      const z = ZOOM * (1 - 0.58 * Math.min(1, dist / 380) * Math.sin(Math.PI * f));
      const s = (W / map.W) * z;
      return { c: [a[0] + (b[0] - a[0]) * e, a[1] + (b[1] - a[1]) * e] as XY, s, f };
    };
    const draw = () => {
      const { c, s, f } = view();
      const ax = W * 0.235, ay = H * 0.56;
      const X = (p: XY) => (p[0] - c[0]) * s + ax, Y = (p: XY) => (p[1] - c[1]) * s + ay;
      cx.setTransform(dpr, 0, 0, dpr, 0, 0);
      cx.clearRect(0, 0, W, H);
      const r = Math.max(0.9, Math.min(3, map.step * s * 0.22));
      const dots = (pts: XY[], color: string) => {
        cx.beginPath();
        for (const p of pts) {
          const x = X(p), y = Y(p);
          if (x < -5 || y < -5 || x > W + 5 || y > H + 5) continue;
          cx.moveTo(x + r, y);
          cx.arc(x, y, r, 0, Math.PI * 2);
        }
        cx.fillStyle = color;
        cx.fill();
      };
      dots(LAND, 'rgba(243,240,234,0.15)');
      dots(WORKED, 'rgba(243,240,234,0.34)');
      // geçilen yol
      cx.lineWidth = 1.6;
      cx.strokeStyle = '#f2a900';
      cx.lineCap = 'round';
      cx.beginPath();
      legs.forEach((leg, k) => {
        const upto = Math.min(1, Math.max(0, cam.t - k));
        if (upto <= 0) return;
        const n = Math.round(upto * (leg.length - 1));
        cx.moveTo(X(leg[0]), Y(leg[0]));
        for (let j = 1; j <= n; j++) cx.lineTo(X(leg[j]), Y(leg[j]));
      });
      cx.stroke();
      // şehirler: gidilmiş olanlar sarı, sıradakiler sönük halka
      const now = Math.round(cam.t);
      cities.forEach((ct, k) => {
        const x = X(ct.p), y = Y(ct.p);
        cx.beginPath();
        if (k <= cam.t + 0.001) {
          cx.arc(x, y, 4, 0, Math.PI * 2);
          cx.fillStyle = '#f2a900';
          cx.fill();
        } else {
          cx.arc(x, y, 3.5, 0, Math.PI * 2);
          cx.strokeStyle = 'rgba(242,169,0,0.45)';
          cx.lineWidth = 1;
          cx.stroke();
        }
      });
      // şu anki şehir: halka ve adı
      const here = cities[now], hx = X(here.p), hy = Y(here.p);
      const settled = 1 - Math.min(1, Math.abs(cam.t - now) * 4);
      cx.globalAlpha = settled;
      cx.beginPath();
      cx.arc(hx, hy, 11, 0, Math.PI * 2);
      cx.strokeStyle = 'rgba(242,169,0,0.6)';
      cx.lineWidth = 1;
      cx.stroke();
      cx.font = '500 11px "JetBrains Mono", monospace';
      cx.fillStyle = 'rgba(243,240,234,0.85)';
      cx.textAlign = 'right';
      cx.fillText(here.name.toLocaleUpperCase(here.lang), hx - 20, hy + 4);
      cx.globalAlpha = 1;
      // şehirden fotoğrafa çizgi
      const ph = $('.fr-f.on .ph', stage);
      if (ph && settled > 0) {
        const sr = stage.getBoundingClientRect(), pr = ph.getBoundingClientRect();
        lead.setAttribute('x1', String(hx + 12));
        lead.setAttribute('y1', String(hy));
        lead.setAttribute('x2', String(pr.left - sr.left - 14));
        lead.setAttribute('y2', String(pr.top - sr.top + pr.height / 2));
      }
      lead.style.opacity = String(settled);
      void f;
    };
    const size = () => {
      dpr = Math.min(2, devicePixelRatio || 1);
      W = stage.clientWidth;
      H = stage.clientHeight;
      cv.width = Math.round(W * dpr);
      cv.height = Math.round(H * dpr);
      draw();
    };

    const tl = gsap.timeline({ paused: true, defaults: { ease: 'none' } });
    gsap.set(figs, { autoAlpha: 0 });
    gsap.set(figs[0], { autoAlpha: 1 });
    gsap.set(caps.slice(1), { autoAlpha: 0 });
    gsap.set(caps[0], { autoAlpha: 1 });
    const place = (f: HTMLElement) => Number(f.dataset.place);
    tl.addLabel('p0', 0).to({}, { duration: 0.6 });
    figs.forEach((f, s) => {
      if (!s) return;
      const prev = figs[s - 1], i = place(f), moved = i !== place(prev), at = `s${s}`;
      tl.addLabel(at);
      if (moved) {
        tl.addLabel(`p${i}`, at)
          .to(prev, { autoAlpha: 0, y: -16, duration: 0.25 }, at)
          .to(caps[place(prev)], { autoAlpha: 0, duration: 0.2 }, at)
          .to(cam, { t: i, duration: 0.8, ease: 'none', onUpdate: draw }, at)
          .fromTo(f, { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: 0.3 }, `${at}+=0.5`)
          .to(caps[i], { autoAlpha: 1, duration: 0.3 }, `${at}+=0.55`);
      } else {
        tl.to(prev, { autoAlpha: 0, duration: 0.2 }, at).fromTo(f, { autoAlpha: 0 }, { autoAlpha: 1, duration: 0.25 }, `${at}+=0.2`);
      }
      tl.to({}, { duration: 0.6 });
    });
    const marks = figs.map((_, s) => (s ? tl.labels[`s${s}`] : 0));
    tl.eventCallback('onUpdate', () => {
      let s = 0;
      marks.forEach((m, k) => { if (tl.time() >= m + 0.3) s = k; });
      figs.forEach((x, k) => x.classList.toggle('on', k === s));
      const ph = $('.ph', figs[s]);
      if (ph?.offsetWidth) stage.style.setProperty('--cw', `${ph.offsetWidth}px`);
      draw();
    });
    figs.forEach((x, k) => x.classList.toggle('on', k === 0));
    const st = pinTimeline(stage, tl, 0.5);
    const ro = new ResizeObserver(size);
    ro.observe(stage);
    size();
    requestAnimationFrame(() => { const ph = $('.ph', figs[0]); if (ph) stage.style.setProperty('--cw', `${ph.offsetWidth}px`); draw(); });
    document.fonts?.ready.then(draw);
    void jumpTo;
    return () => {
      ro.disconnect();
      st.kill();
      tl.kill();
      gsap.set([...figs, ...caps], { clearProps: 'all' });
    };
  },
  mobile: mobileReveal,
};
