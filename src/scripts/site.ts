/** Her sayfada: menü perdesi, kopyala düğmeleri, dil seçimini hatırlama, galeriler. */
import './gallery';

const LANG_KEY = 'site-lang';

/* menü */
const ov = document.getElementById('overlay');
const openB = document.querySelector<HTMLButtonElement>('[data-open]');
if (ov && openB) {
  const setMenu = (open: boolean) => {
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
document.querySelectorAll<HTMLAnchorElement>('[data-lang-link]').forEach((a) =>
  a.addEventListener('click', () => {
    try {
      localStorage.setItem(LANG_KEY, a.dataset.langLink ?? '');
    } catch {}
  }),
);
