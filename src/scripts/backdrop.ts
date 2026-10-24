/**
 * Sayfanın arkasındaki hareketli zemin. Tek bir tuval, içeriğin arkasında sabit durur.
 * - harita: noktalarla çizilmiş dünya haritası; çalışılan 20 ülkenin başkentinde sarı ışık, İstanbul'dan
 *   onlara giden ince yollar. Yollarda arada bir ışık yürür, vardığı şehir parlar.
 *   Kenarlarında yavaşça söner, kesik görünmez.
 * - toz: sol üstten vuran ışık ve içinde yavaşça süzülen toz zerreleri.
 * Kaydırınca harita ve toz, sayfaya göre `kayma` oranında kayar (0 hiç, 25 = 4'e 1, 100 sayfayla birlikte).
 * Uzun sayfada harita yukarı çıktıkça arkasından kopyası gelir, zerreler ekranın altından yeniden girer.
 * Açık ve kapalı olma, belirginlik ve yoğunluk ayar panelinden gelir (src/config/ayar.ts, scripts/ayar.ts).
 * Renkler temaya göre: koyuda karanlıkta parlayan ışık; açıkta kâğıt üstünde mürekkep noktalar, koyu altın şehirler,
 * fosforlu kalem sarısı haleler ve kâğıda düşen ılık gün ışığı.
 * Hareket azaltma tercihinde tek kare çizilir; sekme görünmezken çizim durur.
 */
import { map, rows } from '../data/world-dots';
import type { Ayar } from '../config/ayar';
import { ayar, dinle } from './ayar';

const cv = document.querySelector<HTMLCanvasElement>('canvas.backdrop');
const ctx = cv?.getContext('2d');

/* çalışılan ülkeler (src/data/home.ts kariyer listesi), başkent ya da ekibin şehri: boylam, enlem */
const HUB: [number, number] = [28.98, 41.01]; // İstanbul
const CITIES: [number, number][] = [
  [30.71, 36.9], [15.98, 45.81], [44.79, 41.72], [21.17, 42.67], [13.19, 32.89], [58.38, 37.95], [28.86, 47.01],
  [44.21, 15.35], [49.87, 40.41], [18.41, 43.86], [106.66, 10.76], [30.52, 50.45], [74.59, 42.87], [68.79, 38.56],
  [44.51, 40.18], [69.24, 41.3], [27.56, 53.9], [76.89, 43.24], [44.36, 33.31], [23.32, 42.7],
];

/* temaya göre renkler (r, g, b) ve belirginlik çarpanları */
const RENK = {
  dark: {
    kara: '243, 240, 234', karaK: 1, yol: '242, 169, 0', yolK: 1, nokta: '255, 196, 64', hale: '242, 169, 0', haleK: 1,
    isik: ['255, 238, 205', '255, 236, 200'], isikK: 1, isikMod: 'lighter' as GlobalCompositeOperation, zerre: '255, 240, 210', zerreK: 1,
  },
  light: {
    kara: '22, 20, 15', karaK: 1.5, yol: '143, 94, 0', yolK: 1.4, nokta: '143, 94, 0', hale: '242, 169, 0', haleK: 0.9,
    isik: ['255, 190, 80', '255, 196, 96'], isikK: 0.75, isikMod: 'source-over' as GlobalCompositeOperation, zerre: '128, 84, 8', zerreK: 1.2,
  },
};

if (cv && ctx) {
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const dpr = Math.min(devicePixelRatio || 1, 1.5);
  let S: Ayar = ayar();
  let R = RENK.dark;
  let W = 0, H = 0, cell = 0, ox = 0, oy = 0, top0 = 0, mapH = 0, period = 0;
  let dots: HTMLCanvasElement | null = null; // noktalar ve yaylar bir kez çizilir
  let beam: HTMLCanvasElement | null = null; // projektör ışığı bir kez çizilir
  const off = (w: number, h: number) => Object.assign(document.createElement('canvas'), { width: w, height: h });
  const on = (k: 'harita' | 'toz') => S[k].acik;

  const px = ([lon, lat]: [number, number]): [number, number] => [ox + ((lon - map.west) / map.step) * cell, oy + ((map.north - lat) / map.step) * cell];
  const arcCtl = (a: [number, number], b: [number, number]): [number, number] => {
    const mx = (a[0] + b[0]) / 2, my = (a[1] + b[1]) / 2, dx = b[0] - a[0], dy = b[1] - a[1];
    return [mx + dy * 0.18, my - Math.abs(dx) * 0.18 - Math.abs(dy) * 0.05];
  };
  const onArc = (a: [number, number], c: [number, number], b: [number, number], t: number): [number, number] => {
    const u = 1 - t;
    return [u * u * a[0] + 2 * u * t * c[0] + t * t * b[0], u * u * a[1] + 2 * u * t * c[1] + t * t * b[1]];
  };
  const smooth = (e: number, x: number) => Math.min(1, Math.max(0, x / e)) ** 2 * (3 - 2 * Math.min(1, Math.max(0, x / e)));

  const layout = () => {
    W = innerWidth;
    H = innerHeight;
    R = document.documentElement.dataset.theme === 'light' ? RENK.light : RENK.dark;
    cv.width = Math.round(W * dpr);
    cv.height = Math.round(H * dpr);
    const cols = Math.max(...rows.map((r) => r.length));
    // bilgisayarda bölgenin tamamı görünür; telefonda harita ekrandan büyük kalır, Türkiye ortada
    cell = Math.max((W * 1.05) / cols, (H * 0.8) / rows.length);
    const centre = W < 760 ? 38 : 55;
    ox = W / 2 - ((centre - map.west) / map.step) * cell;
    // harita kendi tuvalinde üstten `oy` kadar boşlukla durur (ışık halkaları kesilmesin); ekranda üst kenarı top0'da
    mapH = rows.length * cell;
    oy = cell * 3;
    top0 = (H - mapH) / 2 + cell * 4;
    period = mapH + cell * 6; // kopyalar arası boşluk: kenarlar zaten sönük, ekran haritasız kalmasın

    dots = null;
    if (on('harita')) {
      dots = off(cv.width, Math.round((mapH + oy * 2) * dpr));
      const g = dots.getContext('2d')!;
      g.scale(dpr, dpr);
      const r = Math.max(0.8, cell * 0.2);
      const a = 0.34 * (S.harita.noktalar / 100) * R.karaK;
      if (a > 0) {
        rows.forEach((row, y) => {
          // haritanın kenarına yaklaştıkça noktalar söner: bölgenin sınırı düz bir çizgi gibi görünmez
          const fy = smooth(0.18, Math.min(y, rows.length - 1 - y) / rows.length);
          for (let x = 0; x < row.length; x++) if (row[x] === '#') {
            const f = fy * smooth(0.14, Math.min(x, cols - 1 - x) / cols);
            if (f < 0.02) continue;
            g.fillStyle = `rgba(${R.kara}, ${(a * f).toFixed(3)})`;
            g.beginPath();
            g.arc(ox + x * cell, oy + y * cell, r, 0, 7);
            g.fill();
          }
        });
      }
      const hub = px(HUB);
      g.strokeStyle = `rgba(${R.yol}, ${Math.min(1, 0.2 * (S.harita.sari / 100) * R.yolK)})`;
      g.lineWidth = 1;
      for (const c of CITIES) {
        const b = px(c), k = arcCtl(hub, b);
        g.beginPath();
        g.moveTo(...hub);
        g.quadraticCurveTo(k[0], k[1], b[0], b[1]);
        g.stroke();
      }
    }
    beam = null;
    if (on('toz')) {
      beam = off(cv.width, cv.height);
      const g = beam.getContext('2d')!;
      g.scale(dpr, dpr);
      g.filter = `blur(${Math.round(Math.min(W, H) * 0.04)}px)`;
      const ax = -W * 0.08, ay = -H * 0.2;
      for (const [spread, a] of [[0.2, 0.05], [0.13, 0.06], [0.07, 0.07]] as const) {
        const grad = g.createLinearGradient(ax, ay, W * 0.75, H * 1.15);
        grad.addColorStop(0, `rgba(${R.isik[0]}, ${a * 2 * R.isikK})`);
        grad.addColorStop(0.55, `rgba(${R.isik[1]}, ${a * R.isikK})`);
        grad.addColorStop(1, `rgba(${R.isik[1]}, 0)`);
        g.fillStyle = grad;
        g.beginPath();
        g.moveTo(ax, ay);
        g.lineTo(W * (0.42 - spread), H * 1.25);
        g.lineTo(W * (0.95 + spread), H * 1.05);
        g.closePath();
        g.fill();
      }
    }
  };

  /* toz zerreleri: ışığın içindekiler parlak, dışındakiler zor seçilir */
  const mote = () => ({
    x: Math.random(), y: Math.random(),
    r: Math.random() < 0.06 ? 3 + Math.random() * 3 : 0.5 + Math.random() * 1.5,
    vx: (0.3 + Math.random()) * 0.004, vy: (Math.random() - 0.5) * 0.004, ph: Math.random() * 7,
  });
  let motes: ReturnType<typeof mote>[] = [];
  const setMotes = () => {
    const n = Math.round(S.toz.yogunluk * 4);
    while (motes.length < n) motes.push(mote());
    motes.length = n;
  };
  // projektör ekseni: (-0.08W,-0.2H) → (0.7W,1.15H); zerrenin eksene uzaklığı ışığın içinde olup olmadığını söyler
  const inBeam = (x: number, y: number) => {
    const ax = -W * 0.08, ay = -H * 0.2, bx = W * 0.7, by = H * 1.15;
    const dx = bx - ax, dy = by - ay, L = Math.hypot(dx, dy);
    const t = ((x - ax) * dx + (y - ay) * dy) / (L * L);
    const d = Math.abs((x - ax) * dy - (y - ay) * dx) / L;
    const half = Math.max(1, t * L * 0.19);
    return t <= 0 ? 0 : Math.max(0, 1 - d / half) * Math.min(1, t * 2.5);
  };

  /* uçuşlar: aynı anda en çok 6; yoğunluk kaç tanesinin yolda olacağını söyler */
  const FLY = 2.4, GLOW = 1.2;
  let trips: { city: number; start: number }[] = [];
  let nextTrip = 0;
  let last = 0;
  const frame = (now: number) => {
    const t = now / 1000;
    const dt = Math.min(0.1, (now - last) / 1000 || 0);
    last = now;
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, cv.width, cv.height);

    if (dots) {
      // harita kaydırmanın `kayma` oranında yukarı çıkar; ekrandan çıkan kopyanın yerine aşağıdan sonraki gelir
      const shift = scrollY * (S.zemin.kayma / 100);
      const first = top0 - (((shift % period) + period) % period);
      const bases: number[] = [];
      for (let b = first - period; b < H + oy; b += period) if (b + mapH + oy > 0) bases.push(b - oy);
      for (const b of bases) ctx.drawImage(dots, 0, Math.round(b * dpr));
      const y = S.harita.sari / 100;
      const hub = px(HUB);
      trips = trips.filter((p) => t - p.start < FLY + GLOW);
      const want = Math.round((S.harita.ucus_yogunlugu / 100) * 6);
      if (trips.length < want && t >= nextTrip) {
        const busy = new Set(trips.map((p) => p.city));
        const free = CITIES.map((_, i) => i).filter((i) => !busy.has(i));
        trips.push({ city: free[Math.floor(Math.random() * free.length)], start: t });
        nextTrip = t + (FLY + GLOW) / Math.max(1, want) * (0.6 + Math.random() * 0.8); // aynı anda kalkmasınlar
      }
      for (const b of bases) {
        ctx.setTransform(dpr, 0, 0, dpr, 0, b * dpr);
        CITIES.forEach((c, i) => {
          const p = px(c);
          const trip = trips.find((q) => q.city === i);
          const k = trip ? (t - trip.start) / FLY : 0;
          const arrived = k >= 1 ? Math.max(0, 1 - ((k - 1) * FLY) / GLOW) : 0;
          const breathe = 0.75 + 0.25 * Math.sin(t * 1.3 + i * 1.7);
          glow(p, cell * (1.3 + arrived * 1.4), (0.35 * breathe + arrived * 0.6) * y);
          dot(p, Math.max(1.4, cell * 0.24), Math.min(1, 1.2 * y));
        });
        glow(hub, cell * 1.8, 0.44 * y);
        dot(hub, Math.max(1.8, cell * 0.3), Math.min(1, 1.25 * y));
        for (const trip of trips) {
          const k = (t - trip.start) / FLY;
          if (k <= 0 || k >= 1) continue;
          const dest = px(CITIES[trip.city]), ctl = arcCtl(hub, dest);
          for (let j = 0; j < 8; j++) {
            const q = onArc(hub, ctl, dest, Math.max(0, k - j * 0.025));
            dot(q, Math.max(1, cell * 0.2) * (1 - j / 9), Math.min(1, 1.1 * y) * (1 - j / 8));
          }
        }
      }
    }

    if (beam) {
      const v = S.toz.gorunurluk / 70;
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.globalCompositeOperation = R.isikMod;
      // 70'in üstünde ışık üst üste bir kez daha eklenir (150'de üç kat)
      for (let left = (0.85 + 0.15 * Math.sin(t * 0.45)) * v; left > 0.01; left -= 1) {
        ctx.globalAlpha = Math.min(1, left);
        ctx.drawImage(beam, 0, 0);
      }
      ctx.globalAlpha = 1;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      for (const m of motes) {
        m.x += m.vx * dt * 6;
        m.y += (m.vy + Math.sin(t * 0.6 + m.ph) * 0.0015) * dt * 6;
        if (m.x > 1.05) m.x = -0.05;
        if (m.y > 1.05) m.y = -0.05;
        if (m.y < -0.05) m.y = 1.05;
        const x = m.x * W, y = ((((m.y * H - scrollY * (S.zemin.kayma / 100)) % H) + H) % H);
        const lit = inBeam(x, y);
        const a = (m.r > 2.5 ? 0.05 + lit * 0.12 : 0.04 + lit * 0.6) * v * R.zerreK;
        ctx.fillStyle = `rgba(${R.zerre}, ${Math.min(1, a * (0.7 + 0.3 * Math.sin(t * 2 + m.ph)))})`;
        ctx.beginPath();
        ctx.arc(x, y, m.r, 0, 7);
        ctx.fill();
      }
      ctx.globalCompositeOperation = 'source-over';
    }
  };
  function dot([x, y]: [number, number], r: number, a: number) {
    ctx!.fillStyle = `rgba(${R.nokta}, ${a})`;
    ctx!.beginPath();
    ctx!.arc(x, y, r, 0, 7);
    ctx!.fill();
  }
  function glow([x, y]: [number, number], r: number, a: number) {
    if (a <= 0) return;
    const g = ctx!.createRadialGradient(x, y, 0, x, y, r);
    g.addColorStop(0, `rgba(${R.hale}, ${Math.min(1, a * R.haleK)})`);
    g.addColorStop(1, `rgba(${R.hale}, 0)`);
    ctx!.fillStyle = g;
    ctx!.fillRect(x - r, y - r, r * 2, r * 2);
  }

  const apply = (next: Ayar) => {
    S = next;
    setMotes();
    layout(); // tuvalin boyu yeniden verilince içi de temizlenir
    if (reduce) frame(4000);
  };

  apply(S);
  let raf = 0, prev = 0, lastY = -1;
  const loop = (now: number) => {
    raf = requestAnimationFrame(loop);
    if (!dots && !beam) return; // ikisi de kapalı: tuval apply()'da temizlendi
    // saniyede ~30 kare yeter, pil yorulmaz; kaydırılırken her kare çizilir ki zemin sayfayla takılmadan aksın
    const moving = S.zemin.kayma > 0 && scrollY !== lastY;
    if (!moving && now - prev < 32) return;
    prev = now;
    lastY = scrollY;
    frame(now);
  };
  if (reduce) addEventListener('scroll', () => frame(4000), { passive: true });
  else raf = requestAnimationFrame(loop);
  addEventListener('resize', () => {
    // telefonda adres çubuğu açılıp kapanınca yükseklik biraz değişir; haritayı yeniden çizmeye değmez
    if (innerWidth === W && Math.abs(innerHeight - H) < 140) return;
    layout();
    if (reduce) frame(4000);
  });
  document.addEventListener('visibilitychange', () => {
    cancelAnimationFrame(raf);
    if (!document.hidden && !reduce) raf = requestAnimationFrame(loop);
  });

  dinle(apply); // panelden ya da sunucudan yeni ayar gelince
}
