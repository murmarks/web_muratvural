/** Hizmet haritası: dalları aç/kapat, ara, telefonda haritayı aç; bulunulan sayfayı haritada ortala. */
const map = document.querySelector<HTMLElement>('[data-map]');
const tree = map?.querySelector<HTMLElement>('[data-tree]');

if (map && tree) {
  tree.addEventListener('click', (e) => {
    const tg = (e.target as Element).closest<HTMLButtonElement>('.tg');
    if (!tg) return;
    const li = tg.closest('li')!;
    tg.setAttribute('aria-expanded', String(li.classList.toggle('open')));
  });

  // telefonda harita kapalı başlar
  const toggle = map.querySelector<HTMLButtonElement>('[data-map-toggle]');
  toggle?.addEventListener('click', () => {
    const open = map.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(open));
    if (open) centerCurrent();
  });

  // bulunulan sayfa haritanın ortasında görünsün (sayfanın kendisi kaydırılmadan)
  const centerCurrent = () => {
    const a = tree.querySelector<HTMLElement>('a[aria-current="page"]');
    if (!a || !tree.clientHeight) return;
    const top = a.getBoundingClientRect().top - tree.getBoundingClientRect().top + tree.scrollTop;
    tree.scrollTop = Math.max(0, top - tree.clientHeight / 2 + a.offsetHeight / 2);
  };
  // açılış animasyonu bitmeden ölçmemek için bir kare bekle
  requestAnimationFrame(centerCurrent);

  // arama: her dildeki başlıkta ve numarada
  const input = map.querySelector<HTMLInputElement>('[data-filter]');
  const empty = map.querySelector<HTMLElement>('[data-empty]');
  const lang = document.documentElement.lang;
  const rootList = tree.querySelector(':scope > ul');
  const remembered = new Map<HTMLElement, boolean>(); // aramadan önce hangi dallar açıktı
  input?.addEventListener('input', () => {
    const q = input.value.trim().toLocaleLowerCase(lang);
    if (q && !remembered.size) tree.querySelectorAll<HTMLElement>('li').forEach((li) => remembered.set(li, li.classList.contains('open')));
    let any = false;
    const walk = (ul: Element | null): boolean => {
      let found = false;
      ul?.querySelectorAll<HTMLElement>(':scope > li').forEach((li) => {
        const self = !q || (li.dataset.q ?? '').toLocaleLowerCase(lang).includes(q);
        const kid = walk(li.querySelector(':scope > .sub > div > ul'));
        li.hidden = !(self || kid);
        li.classList.toggle('hit', !!q && self);
        if (q) li.classList.toggle('open', kid);
        if (q && self) any = true;
        found ||= self || kid;
      });
      return found;
    };
    walk(rootList);
    if (!q) {
      remembered.forEach((open, li) => li.classList.toggle('open', open));
      remembered.clear();
    }
    tree.querySelectorAll<HTMLElement>('li > .row .tg').forEach((tg) => tg.setAttribute('aria-expanded', String(tg.closest('li')!.classList.contains('open'))));
    if (empty) empty.hidden = !q || any;
  });
}
