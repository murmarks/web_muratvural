/** Ana sayfanın hareketleri: perde, ışık, rakam sahneleri, sahadan fotoğraf dizisi, kariyer çubukları, kurucu metni. */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { showAlt } from './gallery';

const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const $ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => root.querySelector<T>(s);
const $$ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => [...root.querySelectorAll<T>(s)];

/* 800 nokta: her biri bir kişi */
const cv = $<HTMLCanvasElement>('[data-dots]');
const cx = cv?.getContext('2d');
const DOTS = Number(cv?.dataset.n ?? 800), COLS = 40, ROWS = Math.ceil(DOTS / COLS);
function drawDots(p: number) {
  if (!cv || !cx) return;
  const w = cv.width, h = cv.height, sx = w / COLS, sy = h / ROWS, n = Math.round(DOTS * p);
  cx.clearRect(0, 0, w, h);
  for (let i = 0; i < DOTS; i++) {
    const c = i % COLS, r = Math.floor(i / COLS);
    cx.beginPath();
    cx.arc(c * sx + sx / 2, r * sy + sy / 2, Math.min(sx, sy) * 0.3, 0, Math.PI * 2);
    cx.fillStyle = i < n ? '#f2a900' : 'rgba(243,240,234,.13)';
    cx.fill();
  }
}
drawDots(1);
const clist = $$('[data-clist] li');

/* ana başlık, telefonda: iki satır, her satır kendi boyunda ekran genişliğini doldurur (masaüstü boyutu CSS'te) */
const h1 = $('.s1 h1'), one = $('.s1 h1 .one'), two = $('.s1 h1 .two');
const narrow = matchMedia('(max-width: 860px)');
function fitTitle() {
  if (!h1 || !one || !two) return;
  h1.style.removeProperty('--fit1');
  h1.style.removeProperty('--fit2');
  const W = h1.clientWidth;
  if (!narrow.matches || !W) return;
  // 100px'te metnin kendi genişliği ölçülür (kutunun değil), sonra orantıyla büyütülür
  const probe = (el: HTMLElement) => {
    const prev = el.style.fontSize;
    el.style.fontSize = '100px';
    const r = document.createRange();
    r.selectNodeContents(el);
    const w = r.getBoundingClientRect().width;
    el.style.fontSize = prev;
    return w;
  };
  h1.style.setProperty('--fit1', `${(((W - 2) / probe(one)) * 100).toFixed(2)}px`);
  h1.style.setProperty('--fit2', `${(((W - 2) / probe(two)) * 100).toFixed(2)}px`);
}
fitTitle();
document.fonts?.ready.then(fitTitle);
let fitW = 0;
if (h1) new ResizeObserver(() => { if (h1.clientWidth !== fitW) { fitW = h1.clientWidth; fitTitle(); } }).observe(h1);

/* sahadan: her yer kendi fotoğraflarıyla; kaydırdıkça bütün fotoğraflar sırayla gelir */
const pics = $('[data-pics]');
const figs = pics ? ([...pics.children] as HTMLElement[]) : [];
const placeItems = $$('.places li');
const ambs = $$('.amb img');
const SLOTS: [number, number][] = [];
figs.forEach((f, i) => $$('.shots > .ph', f).forEach((_, k) => SLOTS.push([i, k])));
const cur = { place: 0, photo: 0 };

// fotoğrafın altındaki yazı, fotoğrafın kendi genişliğini alır; kenarları hizalanır
const fitCap = (fig: HTMLElement) => {
  const on = $('.shots > .ph.on', fig);
  if (on?.offsetWidth) fig.style.setProperty('--cw', `${on.offsetWidth}px`);
};
const fitAll = () => figs.forEach(fitCap);
fitAll();
addEventListener('resize', fitAll);
figs.forEach((f, i) =>
  f.addEventListener('photo', (e) => {
    cur.place = i;
    cur.photo = (e as CustomEvent<number>).detail;
    fitCap(f);
  }),
);

function setSlot(i: number, k: number) {
  if (i !== cur.place) {
    figs.forEach((f, j) => f.classList.toggle('on', j === i));
    placeItems.forEach((li, j) => li.classList.toggle('on', j === i));
    ambs.forEach((im, j) => im.classList.toggle('on', j === i));
  }
  showAlt(figs[i], k);
}

let fieldST: ScrollTrigger | null = null;
function jumpPlace(i: number) {
  if (!fieldST) return setSlot(i, 0);
  const s0 = SLOTS.findIndex((x) => x[0] === i);
  const y = fieldST.start + (fieldST.end - fieldST.start) * ((s0 + 0.5) / SLOTS.length);
  scrollTo({ top: y, behavior: reduce ? 'auto' : 'smooth' });
}
$$<HTMLButtonElement>('[data-jump]').forEach((b) => b.addEventListener('click', () => jumpPlace(Number(b.dataset.jump))));

/* hizmet alanları: akordiyon */
$$('.area').forEach((li) => {
  const btn = $<HTMLButtonElement>('h3 button', li)!;
  btn.addEventListener('click', () => btn.setAttribute('aria-expanded', String(li.classList.toggle('open'))));
});

/* kurucu metni: okundukça kelimeler aydınlanır */
const bio = $('[data-bio]');
let words: HTMLElement[] = [];
if (bio && !reduce) {
  bio.innerHTML = (bio.textContent ?? '')
    .trim()
    .split(/\s+/)
    .map((w) => `<span class="wd">${w.replace(/&/g, '&amp;').replace(/</g, '&lt;')}</span>`)
    .join(' ');
  words = $$('.wd', bio);
}
const litBio = (p: number) => {
  const k = Math.round(words.length * p);
  words.forEach((w, i) => w.classList.toggle('on', i < k));
};

/* hareket */
const shade = $('.stage .shade'), focus = $('.stage .focus');
const allOn = () => {
  clist.forEach((li) => li.classList.add('on'));
  $$('[data-count]').forEach((b) => (b.textContent = b.dataset.count ?? ''));
  drawDots(1);
};

/*
 * ülkeler: sahne açılınca tek tek, sayaç her ülkede bir artar.
 * Kaydırmaya değil zamana bağlı: ziyaretçi hızlı geçse de sıra görünür.
 */
const COUNTRY_STEP = 0.13;
const cNum = clist[0]?.closest('.scene')?.querySelector<HTMLElement>('[data-count]');
const cState = { k: 0 };
let cRun: gsap.core.Tween | null = null;
// son gelen ülke bir an sarı yanar: sıranın nerede olduğu görünür
const showCountries = (k: number, running = false) => {
  clist.forEach((li, j) => {
    li.classList.toggle('on', j < k);
    li.classList.toggle('now', running && j === k - 1);
  });
  if (cNum) cNum.textContent = String(k);
};
const playCountries = () => {
  if (cRun?.isActive() || cState.k >= clist.length) return;
  cRun = gsap.to(cState, {
    k: clist.length,
    duration: (clist.length - cState.k) * COUNTRY_STEP,
    ease: 'none',
    onUpdate: () => showCountries(Math.ceil(cState.k), true),
    onComplete: () => showCountries(clist.length),
  });
};
const resetCountries = () => {
  cRun?.kill();
  cState.k = 0;
  showCountries(0);
};

if (reduce) {
  $$('.cur').forEach((c) => c.remove());
  if (shade) shade.style.opacity = '0';
  if (focus) focus.style.opacity = '0';
} else {
  gsap.registerPlugin(ScrollTrigger);
  // perde açılır; ışık lideri bulur
  gsap.to('.cur.l, .cur.r', { scaleX: 0, duration: 1.6, ease: 'expo.inOut', delay: 0.2 });
  if (shade) gsap.fromTo(shade, { '--r': '3%' }, { '--r': '9%', duration: 2, ease: 'expo.out', delay: 0.9 });
  gsap.from('.s1 h1 > span', { yPercent: 70, opacity: 0, duration: 1.2, stagger: 0.12, ease: 'expo.out', delay: 0.9 });
  gsap.from('.s1 .kicker, .s1 .sub', { opacity: 0, duration: 1, delay: 1.3 });

  const mm = gsap.matchMedia();
  mm.add('(min-width: 861px)', () => {
    // 1: ışık liderden bütün ekibe yayılır, renk geri gelir; ikinci satır ışığın renginde dolar
    gsap
      .timeline({ scrollTrigger: { trigger: '.s1', start: 'top top', end: '+=100%', scrub: 1, pin: true } })
      .to(shade, { '--r': '120%', ease: 'power1.in', duration: 1 })
      .to(focus, { opacity: 0, ease: 'none', duration: 0.8 }, '<.2')
      .to('.s1 h1 .two', { color: '#f2a900', webkitTextStroke: '0px', duration: 0.6 }, '<.2')
      .to({}, { duration: 0.3 });

    // 2: dört rakam, her biri kanıtıyla
    const scenes = $$('.scene'), ticks = $$('.s2 .ticks2 i');
    const tl = gsap.timeline({ scrollTrigger: { trigger: '.s2', start: 'top top', end: '+=320%', scrub: 0.8, pin: true } });
    const dotsP = { p: 0 };
    resetCountries();
    scenes.forEach((s, i) => {
      const b = $('[data-count]', s)!, to = Number(b.dataset.count), o = { v: 0 };
      if (i) tl.to(scenes[i - 1], { autoAlpha: 0, y: -40, duration: 0.25 }).fromTo(s, { autoAlpha: 0, y: 40 }, { autoAlpha: 1, y: 0, duration: 0.35 }, '>');
      if (i === 2) {
        // ülkeler zamanla gelir (yukarıda); bu sahne kaydırmada okunacak kadar yer kaplar
        tl.addLabel('countries', '<')
          .call(() => (tl.time() >= tl.labels.countries ? playCountries() : resetCountries()), undefined, 'countries')
          .to({}, { duration: 1.7 }, 'countries');
      } else {
        tl.fromTo(o, { v: 0 }, { v: to, duration: 0.7, ease: 'power1.out', onUpdate: () => { b.textContent = String(Math.round(o.v)); } }, i ? '<' : 0);
      }
      tl.call(() => ticks.forEach((tk, k) => tk.classList.toggle('on', k <= i)), undefined, '<');
      if (i === 0) tl.fromTo('.ruler .roles span', { scaleX: 0 }, { scaleX: 1, stagger: 0.12, duration: 0.3 }, '<');
      if (i === 1) tl.fromTo(dotsP, { p: 0 }, { p: 1, duration: 0.7, onUpdate: () => drawDots(dotsP.p) }, '<');
      if (i === 3) tl.from('.slides figure', { y: 30, autoAlpha: 0, stagger: 0.08, duration: 0.3 }, '<');
      tl.to({}, { duration: 0.45 });
    });

    // 3: her seferinde bir yer; kaydırma her yerin her fotoğrafından geçer
    fieldST = ScrollTrigger.create({
      trigger: '[data-viewer]',
      start: 'top top',
      end: () => `+=${innerHeight * 0.42 * SLOTS.length}`,
      pin: true,
      onUpdate: (st) => {
        const [i, k] = SLOTS[Math.min(SLOTS.length - 1, Math.floor(st.progress * SLOTS.length))];
        if (i !== cur.place || k !== cur.photo) setSlot(i, k);
      },
    });
    return () => {
      cRun?.kill();
      allOn();
      fieldST = null;
    };
  });
  mm.add('(max-width: 860px)', () => {
    allOn();
    gsap.timeline({ delay: 2.6 }).to(shade, { '--r': '120%', duration: 2.4, ease: 'power2.inOut' }).to(focus, { opacity: 0, duration: 1.6 }, '<.4');
  });

  /*
   * 4: kariyer: görevler birer birer gelir, çubuk kendi süresi kadar uzar.
   * Satırlar ekrana birlikte girse de (hızlı kaydırma, uzun ekran) her biri öncekinden ROW_GAP saniye sonra başlar.
   * Ekranın üstünde kalmış satır sıra beklemez, hemen görünür.
   */
  const ROW_GAP = 0.75;
  let rowFree = 0;
  $$('.gantt .row').forEach((row) => {
    const tlr = gsap.timeline({ paused: true });
    tlr.from($$('.who > *', row), { opacity: 0, y: 14, duration: 0.55, stagger: 0.09, ease: 'power2.out' })
      .from($('.barx', row), { scaleX: 0, duration: 1.1, ease: 'power3.out' }, 0.2);
    const meta = $('.meta', row);
    if (meta) tlr.from(meta, { opacity: 0, duration: 0.5 }, '-=0.35');
    ScrollTrigger.create({
      trigger: row,
      start: 'top 85%',
      once: true,
      onEnter: () => {
        if (row.getBoundingClientRect().bottom < 0) return void tlr.progress(1);
        const now = gsap.ticker.time, at = Math.max(now, rowFree);
        rowFree = at + ROW_GAP;
        gsap.delayedCall(at - now, () => tlr.play());
      },
    });
  });
  $$('.h2').forEach((h) => gsap.from(h, { yPercent: 30, opacity: 0, duration: 1.1, ease: 'expo.out', scrollTrigger: { trigger: h, start: 'top 88%' } }));
  gsap.from('.area', { y: 40, opacity: 0, stagger: 0.08, duration: 0.9, ease: 'power3.out', scrollTrigger: { trigger: '.areas', start: 'top 85%' } });
  gsap.from('.lit img', { y: 40, opacity: 0, duration: 1.4, ease: 'expo.out', scrollTrigger: { trigger: '.lit', start: 'top 75%' } });
  if (bio && words.length) {
    bio.classList.add('dim');
    litBio(0);
    ScrollTrigger.create({ trigger: bio, start: 'top 80%', end: 'bottom 50%', scrub: true, onUpdate: (s) => litBio(s.progress) });
  }
  // yazı tipleri yüklenince ölçüler değişir
  document.fonts?.ready.then(() => ScrollTrigger.refresh());
}
