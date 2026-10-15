/** Her sayfada: menü perdesi, kopyala düğmeleri, dil seçimini hatırlama, galeriler. */
import './gallery';

const LANG_KEY = 'site-lang';

/* menü */
const ov = document.getElementById('overlay');
const openB = document.querySelector<HTMLButtonElement>('[data-open]');
if (ov && openB) {
  let hideT = 0;
  const setMenu = (open: boolean) => {
    clearTimeout(hideT);
    if (open) {
      ov.hidden = false;
      void ov.offsetWidth; // perde açılış geçişi kapalı halden başlasın
    } else {
      hideT = window.setTimeout(() => (ov.hidden = true), 850);
    }
    ov.classList.toggle('open', open);
    ov.setAttribute('aria-hidden', String(!open));
    ov.toggleAttribute('inert', !open);
    openB.setAttribute('aria-expanded', String(open));
    document.documentElement.style.overflow = open ? 'hidden' : '';
    if (open) ov.querySelector<HTMLElement>('a')?.focus();
    else openB.focus({ preventScroll: true });
  };
  openB.addEventListener('click', () => setMenu(true));
  ov.querySelector('[data-close]')?.addEventListener('click', () => setMenu(false));
  ov.querySelectorAll('[data-nav]').forEach((a) => a.addEventListener('click', () => setMenu(false)));
  addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && ov.classList.contains('open')) setMenu(false);
  });
}

/* kopyala */
document.querySelectorAll<HTMLButtonElement>('[data-copy]').forEach((b) =>
  b.addEventListener('click', () => {
    const label = b.textContent;
    const done = () => {
      b.textContent = b.dataset.copied ?? label;
      setTimeout(() => (b.textContent = label), 1600);
    };
    const select = () => {
      const target = b.previousElementSibling;
      if (!target) return;
      const r = document.createRange();
      r.selectNodeContents(target);
      const s = getSelection();
      s?.removeAllRanges();
      s?.addRange(r);
    };
    try {
      navigator.clipboard.writeText(b.dataset.copy ?? '').then(done, select);
    } catch {
      select();
    }
  }),
);

/* dil: ziyaretçinin seçimi saklanır, kök adres (/) bir sonraki ziyarette bu dili açar */
const langLinks = [...document.querySelectorAll<HTMLAnchorElement>('[data-lang-link]')];
const rememberLang = (a: HTMLAnchorElement) => {
  try {
    localStorage.setItem(LANG_KEY, a.dataset.langLink ?? '');
  } catch {}
};
langLinks.forEach((a) => a.addEventListener('click', () => rememberLang(a)));
/*
 * Sabit üst çubuk sayfa çok kaydırıldıktan sonra bazı tarayıcılarda tıklamayı alamayabiliyor
 * (tıklama alttaki katmana gidiyor). Tıklama dil düğmesinin üstüne düştüyse hedef ne olursa olsun o dile geçilir.
 */
document.addEventListener(
  'click',
  (e) => {
    if (e.defaultPrevented || e.button !== 0 || ov?.classList.contains('open')) return;
    if ((e.target as Element | null)?.closest?.('[data-lang-link]')) return;
    const hit = langLinks.find((a) => {
      const r = a.getBoundingClientRect();
      return e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
    });
    if (!hit) return;
    e.preventDefault();
    rememberLang(hit);
    location.assign(hit.href);
  },
  true,
);
