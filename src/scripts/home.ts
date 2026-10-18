/** Ana sayfanın hareketleri: perde, ışık, rakam sahneleri, sahadan fotoğraf dizisi, kariyer çubukları, kurucu metni. */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { showAlt } from './gallery';
import { fieldFx, type FxMode } from './field-fx';

const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const $ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => root.querySelector<T>(s);
const $$ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => [...root.querySelectorAll<T>(s)];

/*
 * Sayfa içi bağlantılar (menüdeki "Rakamlarla" gibi): sabitlenen bölüm kaydırma boyunca ekranda kalır,
 * tarayıcı onu o anki yerinde bulur. Aşağıdan gelince bu, bölümün sonu (son sahne) olur.
 * Sabitlenen bölümler buraya kendi başlangıçlarına gitme yolunu kaydeder; bağlantı onu kullanır.
 */
const pinJump = new Map<string, () => void>();
document.addEventListener('click', (e) => {
  const a = (e.target as Element).closest<HTMLAnchorElement>('a[href*="#"]');
  if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
  const url = new URL(a.href);
  const jump = url.pathname === location.pathname && pinJump.get(url.hash);
  if (!jump) return;
  e.preventDefault();
  jump();
  if (location.hash !== url.hash) history.pushState(null, '', url.hash);
});

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
/** efektli gösterimde geçiş sürerken kaydırmayı tutan dinleyicileri kaldırır */
let unhold = () => {};
/** efektli gösterimde listeden ya da küçük resimden seçilen fotoğrafa tek geçişle gider */
let fxJump: ((slot: number) => void) | null = null;
/** efektli gösterimde fotoğrafın kaydırma payının başı yerinde durduğu kısımdır; ortası değil başı hedeflenir */
let slotAim = 0.5;
function jumpPlace(i: number, k = 0) {
  if (!fieldST) return setSlot(i, k);
  const s0 = SLOTS.findIndex((x) => x[0] === i && x[1] === k);
  fxJump?.(s0);
  const y = fieldST.start + (fieldST.end - fieldST.start) * ((s0 + slotAim) / SLOTS.length);
  scrollTo({ top: y, behavior: reduce ? 'auto' : 'smooth' });
}
$$<HTMLButtonElement>('[data-jump]').forEach((b) => b.addEventListener('click', () => jumpPlace(Number(b.dataset.jump))));
// efektli gösterimde küçük resme tıklayınca o fotoğrafın kaydırma yerine gidilir (fotoğrafı kaydırma çizer)
pics?.addEventListener(
  'click',
  (e) => {
    const b = (e.target as Element).closest<HTMLElement>('[data-alt]');
    const fig = b?.closest<HTMLElement>('[data-place]');
    if (!b || !fig || !pics.closest('[data-fx-on]')) return;
    e.stopPropagation();
    jumpPlace(Number(fig.dataset.place), Number(b.dataset.alt));
  },
  true,
);

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

/** Sayfa ekrandayken çalıştırır; dil düğmesi için arka planda hazırlanan sayfada, ziyaretçi o sayfaya geçince. */
function onShow(run: () => void) {
  if ((document as Document & { prerendering?: boolean }).prerendering) document.addEventListener('prerenderingchange', run, { once: true });
  else run();
}
const allOn = () => {
  clist.forEach((li) => {
    li.classList.add('on');
    li.classList.remove('now');
  });
  $$('[data-count]').forEach((b) => (b.textContent = b.dataset.count ?? ''));
  drawDots(1);
};

/* ülkeler: kaydırdıkça tek tek yanar, sayaç her ülkede bir artar; sıra sürerken son yanan ülke sarıdır */
const cNum = clist[0]?.closest('.scene')?.querySelector<HTMLElement>('[data-count]');
const showCountries = (k: number) => {
  clist.forEach((li, j) => {
    li.classList.toggle('on', j < k);
    li.classList.toggle('now', k < clist.length && j === k - 1);
  });
  if (cNum) cNum.textContent = String(k);
};

if (reduce) {
  $$('.cur').forEach((c) => c.remove());
  if (shade) shade.style.opacity = '0';
  if (focus) focus.style.opacity = '0';
} else {
  gsap.registerPlugin(ScrollTrigger);
  /*
   * Fotoğraf her ekranda alttaki bölümler kadar geniştir (CSS: .s1), yüksekliğe göre daraltılmaz.
   * Ekran kısaysa başlığın satırları ilk ekranın altında kalır: altta kalan satır ilk ekranda hiç görünmez
   * (yarısı kesik durmasın), kaydırınca gelir. Fotoğrafın altı da ekrana sığmıyorsa sayfa önce o kadar kayar,
   * ışık fotoğrafın tamamı görünürken yayılır.
   */
  const s1 = $('.s1')!, stage = $('.stage')!;
  const lines = $$<HTMLElement>('.s1 h1 > span');
  const below = (el: Element) => !narrow.matches && el.getBoundingClientRect().bottom + scrollY > innerHeight + 1;
  const late = lines.filter(below);
  const heroTall = late.length > 0;
  const pinShift = narrow.matches ? 0 : Math.max(0, Math.ceil(stage.getBoundingClientRect().bottom + scrollY + 12 - innerHeight));
  // perde açılır; ışık lideri bulur. Sayfa dil düğmesi için arka planda hazırlandıysa, ekrana gelince başlar.
  onShow(() => {
    gsap.to('.cur.l, .cur.r', { scaleX: 0, duration: 1.6, ease: 'expo.inOut', delay: 0.2 });
    if (shade) gsap.fromTo(shade, { '--r': '3%' }, { '--r': '9%', duration: 2, ease: 'expo.out', delay: 0.9 });
    const early = lines.filter((l) => !late.includes(l));
    if (early.length) gsap.from(early, { yPercent: 70, opacity: 0, duration: 1.2, stagger: 0.12, ease: 'expo.out', delay: 0.9 });
    gsap.from('.s1 .kicker, .s1 .sub', { opacity: 0, duration: 1, delay: 1.3 });
  });

  const mm = gsap.matchMedia();
  mm.add('(min-width: 861px)', () => {
    /*
     * 1: ışık liderden bütün ekibe yayılır, renk geri gelir; başlığın ikinci satırı ışığın renginde dolar.
     * Başlığın satırları ilk ekranın altındaysa (heroTall): ışık fotoğrafın tamamı görünürken yayılır,
     * altta kalan satırlar kaydırınca ekrana girerken belirir; ikinci satır o sırada dolar.
     */
    const two = $('.s1 h1 .two')!;
    const fill = { color: '#f2a900', webkitTextStroke: '0px', duration: 0.6 };
    if (heroTall) gsap.set(late, { opacity: 0, yPercent: 40 });
    const hero = gsap
      .timeline({ scrollTrigger: { trigger: s1, start: `top top-=${pinShift}`, end: `+=${heroTall ? 70 : 100}%`, scrub: 1, pin: true } })
      .to(shade, { '--r': '120%', ease: 'power1.in', duration: 1 })
      .to(focus, { opacity: 0, ease: 'none', duration: 0.8 }, '<.2');
    late.forEach((l) =>
      gsap.to(l, {
        ...(l === two ? fill : {}),
        opacity: 1,
        yPercent: 0,
        ease: 'none',
        scrollTrigger: { trigger: l, pinnedContainer: s1, start: 'top bottom', end: 'bottom 72%', scrub: 1 },
      }),
    );
    if (!late.includes(two)) hero.to(two, fill, '<.2');
    hero.to({}, { duration: 0.3 });

    /*
     * 2: dört rakam, her biri kanıtıyla. Hepsi kaydırmaya bağlı: kaydırma durunca durur, yukarı çıkınca geri silinir.
     * 800 nokta ve 19 ülke kaydırmayla doğru orantılı, sahne tam göründükten sonra ve okunacak kadar uzun bir kaydırmada dolar.
     */
    const DOTS_LEN = 2, COUNTRIES_LEN = 2.2;
    const scenes = $$('.scene'), ticks = $$('.s2 .ticks2 i');
    // alttaki dört çizgi, kaydırmanın hangi sahnede olduğunu gösterir (iki yönde de)
    const tl = gsap.timeline({
      paused: true,
      onUpdate: () => {
        let at = 0;
        scenes.forEach((_, i) => { if (tl.time() >= tl.labels[`s${i}`]) at = i; });
        ticks.forEach((tk, k) => tk.classList.toggle('on', k <= at));
      },
    });
    const dotsP = { p: 0 }, cP = { k: 0 };
    drawDots(0);
    showCountries(0);
    scenes.forEach((s, i) => {
      const b = $('[data-count]', s)!, to = Number(b.dataset.count), o = { v: 0 };
      if (i) tl.to(scenes[i - 1], { autoAlpha: 0, y: -40, duration: 0.25 }).addLabel(`s${i}`).fromTo(s, { autoAlpha: 0, y: 40 }, { autoAlpha: 1, y: 0, duration: 0.35 }, `s${i}`);
      else tl.addLabel('s0', 0);
      if (i === 1) {
        // noktalar ve sayaç birlikte dolar
        b.textContent = '0';
        tl.fromTo(dotsP, { p: 0 }, { p: 1, duration: DOTS_LEN, ease: 'none', onUpdate: () => { drawDots(dotsP.p); b.textContent = String(Math.round(to * dotsP.p)); } }, '>');
      } else if (i === 2) {
        tl.fromTo(cP, { k: 0 }, { k: clist.length, duration: COUNTRIES_LEN, ease: 'none', onUpdate: () => showCountries(Math.round(cP.k)) }, '>');
      } else {
        tl.fromTo(o, { v: 0 }, { v: to, duration: 0.7, ease: 'power1.out', onUpdate: () => { b.textContent = String(Math.round(o.v)); } }, `s${i}`);
      }
      if (i === 0) tl.fromTo('.ruler .roles span', { scaleX: 0 }, { scaleX: 1, stagger: 0.12, duration: 0.3 }, 's0');
      if (i === 3) tl.from('.slides figure', { y: 30, autoAlpha: 0, stagger: 0.08, duration: 0.3 }, 's3');
      tl.addLabel(`in${i}`).to({}, { duration: 0.45 });
    });
    // zaman çizelgesinin her birimi yarım ekran kaydırma: yıllar ve slaytlar önceki hızında kalır
    const numST = ScrollTrigger.create({ animation: tl, trigger: '.s2', start: 'top top', end: `+=${Math.round(tl.duration() * 50)}%`, scrub: 0.8, pin: true });
    // menüden ya da başka sayfadan "Rakamlarla"ya gelince: ilk sahne, yıl sayısı ve çizgisi dolmuş hâlde
    const toNumbers = () => {
      tl.time(tl.labels.in0);
      scrollTo({ top: numST.start + (numST.end - numST.start) * tl.progress(), behavior: 'instant' });
    };
    pinJump.set('#numbers', toNumbers);
    if (location.hash === '#numbers') requestAnimationFrame(toNumbers);

    // 3: her seferinde bir yer; kaydırma her yerin her fotoğrafından geçer
    const viewer = $('[data-viewer]');
    const fx = viewer?.dataset.fx ? fieldFx(viewer, viewer.dataset.fx as FxMode) : null;
    if (fx) {
      /*
       * 3 (efektli): kaydırma hangi fotoğrafın gösterileceğini seçer, geçiş ise kendi hızında oynar.
       * Sahibinin isteği: hızlı kaydırınca efekt bir kırpışma gibi kaçmasın. Geçiş oynarken sayfa olduğu yerde
       * bekler (tekerlek, dokunma ve klavye kaydırması tutulur), bitince kaydırma her zamanki hızıyla sürer.
       */
      slotAim = 0.15;
      const N = SLOTS.length;
      const D = 1.4; // bir geçişin süresi (sn)
      const v = { lo: 0, hi: 0, t: 0, enter: 0, sp: 0 };
      let at = 0, target = 0, inside = false;
      let run: gsap.core.Tween | null = null;
      const paint = () => {
        const r = fx.render(v);
        ambs.forEach((im, j) => (im.style.opacity = String(j === r.from && j === r.to ? 1 : j === r.from ? 1 - r.t : j === r.to ? r.t : 0)));
        if (r.place !== cur.place || r.photo !== cur.photo) {
          if (r.place !== cur.place) placeItems.forEach((li, j) => li.classList.toggle('on', j === r.place));
          showAlt(figs[r.place], r.photo);
        }
      };
      // ilk fotoğraf: bölüm ekrana girerken kendi hızında gelir, yukarı dönülünce kendi hızında çekilir
      const enterTw = gsap.to(v, { enter: 1, duration: D, ease: 'power1.inOut', paused: true, onUpdate: paint, onComplete: () => step(), onReverseComplete: paint });
      const enterST = ScrollTrigger.create({ trigger: viewer, start: 'top 70%', onEnter: () => enterTw.play(), onLeaveBack: () => enterTw.reverse() });
      // sıradaki fotoğrafa geç; kaydırma birkaç fotoğraf birden atladıysa (kaydırma çubuğu, listeden seçim) doğrudan oraya
      const step = (direct = false) => {
        if (run || enterTw.isActive() || target === at) return;
        const to = direct || Math.abs(target - at) > 2 ? target : at + Math.sign(target - at);
        const fwd = to > at;
        v.lo = Math.min(at, to);
        v.hi = Math.max(at, to);
        v.t = fwd ? 0 : 1;
        run = gsap.to(v, {
          t: fwd ? 1 : 0,
          duration: D,
          ease: 'power1.inOut',
          onUpdate: paint,
          onComplete: () => {
            at = to;
            Object.assign(v, { lo: to, hi: to, t: 0 });
            run = null;
            paint();
            step();
          },
        });
      };
      fieldST = ScrollTrigger.create({
        trigger: viewer,
        start: 'top top',
        end: () => `+=${innerHeight * 0.5 * N}`,
        pin: true,
        onUpdate: (s) => {
          v.sp = s.progress * N;
          target = Math.min(N - 1, Math.floor(v.sp));
          // bölüme aşağıdan girilince ya da sayfa bölümün ortasında açılınca o konumun fotoğrafı animasyonsuz yerinde durur
          if (s.isActive && !inside && !run && target !== at && (s.direction < 0 || Math.abs(target - at) > 2)) {
            enterTw.progress(1).pause();
            at = target;
            Object.assign(v, { lo: at, hi: at, t: 0 });
          }
          inside = s.isActive;
          if (run || enterTw.isActive()) return;
          if (target !== at) step();
          else paint();
        },
        onRefresh: () => fx.measure(),
      });
      // geçiş sürerken sayfa bekler; bölümün dışında hiçbir şey tutulmaz
      const busy = () => !!fieldST?.isActive && (!!run || enterTw.isActive());
      const hold = (e: Event) => { if (busy() && e.cancelable) e.preventDefault(); };
      const KEYS = new Set([' ', 'ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End']);
      const holdKey = (e: KeyboardEvent) => {
        if (KEYS.has(e.key) && !(e.target as Element).closest?.('input, textarea, select, [contenteditable]')) hold(e);
      };
      addEventListener('wheel', hold, { passive: false });
      addEventListener('touchmove', hold, { passive: false });
      addEventListener('keydown', holdKey);
      fxJump = (slot) => {
        target = slot;
        step(true);
      };
      unhold = () => {
        removeEventListener('wheel', hold);
        removeEventListener('touchmove', hold);
        removeEventListener('keydown', holdKey);
        run?.kill();
        enterTw.kill();
        enterST.kill();
        fxJump = null;
        unhold = () => {};
      };
      paint();
    } else fieldST = ScrollTrigger.create({
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
      allOn();
      unhold();
      fx?.destroy();
      slotAim = 0.5;
      fieldST = null;
      pinJump.delete('#numbers');
    };
  });
  mm.add('(max-width: 860px)', () => {
    allOn();
    onShow(() => gsap.timeline({ delay: 2.6 }).to(shade, { '--r': '120%', duration: 2.4, ease: 'power2.inOut' }).to(focus, { opacity: 0, duration: 1.6 }, '<.4'));
    // 3, telefonda tül: her yerin fotoğrafı ekrana girerken kendi hızında iner; küçük resimle değişince yenisi iner
    if ($('[data-viewer]')?.dataset.fx !== 'tul') return;
    const img = (f: HTMLElement) => $<HTMLImageElement>('.shots > .ph.on img', f);
    const drape = (el: HTMLElement | null) => el && gsap.fromTo(el, { '--dp': 1 }, { '--dp': 0, duration: 1.4, ease: 'power1.inOut', overwrite: true });
    const shown = new Set<HTMLElement>();
    const last = new Map<HTMLElement, number>(); // yerin şu an gösterilen fotoğrafı
    const sts = figs.map((f) => {
      gsap.set($$('.shots > .ph img', f), { '--dp': 1 });
      return ScrollTrigger.create({
        trigger: f,
        start: 'top 78%',
        onEnter: () => (shown.add(f), last.set(f, Number($('[data-alt][aria-current="true"]', f)?.dataset.alt ?? 0)), drape(img(f))),
        onLeaveBack: () => (shown.delete(f), gsap.to($$('.shots > .ph img', f), { '--dp': 1, duration: 0.8, ease: 'power1.inOut', overwrite: true })),
      });
    });
    const onPhoto = (e: Event) => {
      const f = e.currentTarget as HTMLElement;
      const k = (e as CustomEvent<number>).detail;
      if (!shown.has(f) || last.get(f) === k) return;
      last.set(f, k);
      drape(img(f));
    };
    figs.forEach((f) => f.addEventListener('photo', onPhoto));
    return () => {
      sts.forEach((st) => st.kill());
      figs.forEach((f) => f.removeEventListener('photo', onPhoto));
      $$('.s3 .shots > .ph img').forEach((el) => el.style.removeProperty('--dp'));
    };
  });

  /*
   * Bundan sonrası da kaydırmaya bağlı: kaydırdıkça gelir, durunca durur, yukarı çıkınca geri silinir.
   * 4: kariyer: her satır ekrana girerken gelir, çubuk kendi süresi kadar uzar; satırlar yukarıdan aşağıya sırayla.
   */
  const SCRUB = 0.6;
  $$('.gantt .row').forEach((row) => {
    const tlr = gsap.timeline({ scrollTrigger: { trigger: row, start: 'top 92%', end: 'top 58%', scrub: SCRUB } });
    tlr.from($$('.who > *', row), { opacity: 0, y: 14, duration: 0.5, stagger: 0.1, ease: 'none' })
      // çubuk soldan açılır (sıkıştırılmaz): kaydırma ortada durunca içindeki yazı bozuk görünmez
      .fromTo($('.barx', row), { clipPath: 'inset(0% 100% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1, ease: 'none' }, 0.15);
    const meta = $('.meta', row);
    if (meta) tlr.from(meta, { opacity: 0, duration: 0.4, ease: 'none' }, '-=0.3');
  });
  $$('.h2').forEach((h) => gsap.from(h, { yPercent: 30, opacity: 0, ease: 'none', scrollTrigger: { trigger: h, start: 'top 96%', end: 'top 72%', scrub: SCRUB } }));
  $$('.area').forEach((a) => gsap.from(a, { y: 40, opacity: 0, ease: 'none', scrollTrigger: { trigger: a, start: 'top 96%', end: 'top 76%', scrub: SCRUB } }));
  gsap.from('.lit img', { y: 40, opacity: 0, ease: 'none', scrollTrigger: { trigger: '.lit', start: 'top 92%', end: 'top 55%', scrub: SCRUB } });
  // hizmet alanı açılıp kapanınca alttaki bölümler kayar; kaydırma noktaları yeniden ölçülür
  const areasEl = $('.areas');
  if (areasEl) {
    let areasH = areasEl.offsetHeight, wait = 0;
    new ResizeObserver(() => {
      if (areasEl.offsetHeight === areasH) return;
      areasH = areasEl.offsetHeight;
      clearTimeout(wait);
      wait = window.setTimeout(() => ScrollTrigger.refresh(), 250);
    }).observe(areasEl);
  }
  if (bio && words.length) {
    bio.classList.add('dim');
    litBio(0);
    ScrollTrigger.create({ trigger: bio, start: 'top 80%', end: 'bottom 50%', scrub: true, onUpdate: (s) => litBio(s.progress) });
  }
  // yazı tipleri yüklenince ölçüler değişir
  document.fonts?.ready.then(() => ScrollTrigger.refresh());
}
