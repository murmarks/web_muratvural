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

/*
 * Dil değişirken yeni sayfa sunucudan gelene kadar eski sayfa ekranda kalır ve tıklama işe yaramamış gibi görünür.
 * Bu yüzden basılır basılmaz: seçilen dil düğmede seçili olur, üstte sarı bir çizgi akar,
 * sağ üstte "… yükleniyor" yazar, sayfa hafifçe kararır. Yeni sayfa gelince hepsi kendiliğinden gider.
 */
let note: HTMLElement | null = null;
let clearT = 0;
let retryT = 0;
const clearSwitching = () => {
  clearTimeout(clearT);
  clearTimeout(retryT);
  document.documentElement.classList.remove('switching');
  note?.remove();
  note = null;
  langLinks.forEach((x) => x.setAttribute('aria-current', String(x.dataset.langLink === document.documentElement.lang)));
};
const showSwitching = (a: HTMLAnchorElement) => {
  langLinks.forEach((x) => x.setAttribute('aria-current', String(x === a)));
  note?.remove();
  note = document.createElement('div');
  note.className = 'lang-note mono';
  note.setAttribute('role', 'status');
  note.lang = a.dataset.langLink ?? '';
  note.innerHTML = `<i aria-hidden="true"></i><span></span>`;
  note.querySelector('span')!.textContent = a.dataset.loading ?? '';
  document.body.append(note);
  document.documentElement.classList.add('switching');
  // yeni sayfa 8 saniyede gelmezse istek bir kez daha gönderilir; sayfa hiçbir zaman sessizce eski dilde bırakılmaz.
  // Ziyaretçi gezinmeyi kendisi durdurursa (Esc, durdur düğmesi) sayfa kararmış kalmasın diye 25 sn sonra hal kalkar.
  clearTimeout(retryT);
  retryT = window.setTimeout(() => location.assign(a.href), 8000);
  clearT = window.setTimeout(clearSwitching, 25000);
};
langLinks.forEach((a) =>
  a.addEventListener('click', (e) => {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (a.getAttribute('aria-current') === 'true') return void e.preventDefault(); // zaten bu dildesiniz
    rememberLang(a);
    showSwitching(a);
  }),
);
// geri tuşuyla bu sayfaya dönülürse (tarayıcı sayfayı olduğu gibi saklamış olabilir) yükleniyor hali kalkar
addEventListener('pageshow', (e) => {
  if (e.persisted) clearSwitching();
});
