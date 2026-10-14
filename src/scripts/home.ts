/** Ana sayfanın hareketleri: perde, ışık, rakam sahneleri, sahadan fotoğraf dizisi, kariyer çubukları, kurucu metni. */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { showAlt } from './gallery';

const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const $ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => root.querySelector<T>(s);
const $$ = <T extends Element = HTMLElement>(s: string, root: ParentNode = document) => [...root.querySelectorAll<T>(s)];

const clist = $$('[data-clist] li');

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
const allOn = () => clist.forEach((li) => li.classList.add('on'));

if (reduce) {
  $$('.cur').forEach((c) => c.remove());
  if (shade) shade.style.opacity = '0';
  if (focus) focus.style.opacity = '0';
} else {
  gsap.registerPlugin(ScrollTrigger);
  // perde açılır; ışık önce onu bulur
  gsap.to('.cur.l, .cur.r', { scaleX: 0, duration: 1.6, ease: 'expo.inOut', delay: 0.2 });
  if (shade) gsap.fromTo(shade, { '--r': '3%' }, { '--r': '9%', duration: 2, ease: 'expo.out', delay: 0.9 });
  gsap.from('.s1 h1 > span', { yPercent: 70, opacity: 0, duration: 1.2, stagger: 0.12, ease: 'expo.out', delay: 0.9 });
  gsap.from('.s1 .kicker, .s1 .sub', { opacity: 0, duration: 1, delay: 1.3 });

  const mm = gsap.matchMedia();
  mm.add('(min-width: 861px)', () => {
    // 1: ışık ondan bütün ekibe yayılır, renk geri gelir
    gsap
      .timeline({ scrollTrigger: { trigger: '.s1', start: 'top top', end: '+=100%', scrub: 1, pin: true } })
      .to(shade, { '--r': '120%', ease: 'power1.in', duration: 1 })
      .to(focus, { opacity: 0, ease: 'none', duration: 0.8 }, '<.2')
      .to({}, { duration: 0.3 });

    // 2: üç rakam, her biri kanıtıyla
    const scenes = $$('.scene'), ticks = $$('.s2 .ticks2 i');
    const tl = gsap.timeline({ scrollTrigger: { trigger: '.s2', start: 'top top', end: '+=200%', scrub: 0.8, pin: true } });
    const listP = { p: 0 };
    scenes.forEach((s, i) => {
      const b = $('[data-count]', s)!, to = Number(b.dataset.count), o = { v: 0 };
      if (i) tl.to(scenes[i - 1], { autoAlpha: 0, y: -40, duration: 0.25 }).fromTo(s, { autoAlpha: 0, y: 40 }, { autoAlpha: 1, y: 0, duration: 0.35 }, '>');
      tl.fromTo(o, { v: 0 }, { v: to, duration: 0.7, ease: 'power1.out', onUpdate: () => { b.textContent = String(Math.round(o.v)); } }, i ? '<' : 0)
        .call(() => ticks.forEach((tk, k) => tk.classList.toggle('on', k <= i)), undefined, '<');
      if (i === 0) tl.fromTo('.ruler .roles span', { scaleX: 0 }, { scaleX: 1, stagger: 0.12, duration: 0.3 }, '<');
      if (i === 1) tl.fromTo(listP, { p: 0 }, { p: 1, duration: 0.7, onUpdate: () => { const k = Math.round(clist.length * listP.p); clist.forEach((li, j) => li.classList.toggle('on', j < k)); } }, '<');
      if (i === 2) tl.from('.slides figure', { y: 30, autoAlpha: 0, stagger: 0.08, duration: 0.3 }, '<');
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
      allOn();
      fieldST = null;
    };
  });
  mm.add('(max-width: 860px)', () => {
    allOn();
    gsap.timeline({ delay: 2.6 }).to(shade, { '--r': '120%', duration: 2.4, ease: 'power2.inOut' }).to(focus, { opacity: 0, duration: 1.6 }, '<.4');
  });

  // 4: çubuklar ölçeğine göre uzar
  gsap.from('.gantt .barx', { scaleX: 0, duration: 1.1, stagger: 0.12, ease: 'expo.out', scrollTrigger: { trigger: '.gantt', start: 'top 75%' } });
  gsap.from('.gantt .meta', { opacity: 0, duration: 0.6, stagger: 0.12, delay: 0.5, scrollTrigger: { trigger: '.gantt', start: 'top 75%' } });
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
