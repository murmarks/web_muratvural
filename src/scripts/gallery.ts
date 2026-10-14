/** Galeriler: önizlemeye tıklayınca büyük fotoğraf ve açıklaması değişir. */
export function showAlt(box: HTMLElement, i: number) {
  box.querySelectorAll(':scope .shots > .ph').forEach((e, k) => e.classList.toggle('on', k === i));
  box.querySelectorAll('[data-alt]').forEach((e, k) => e.setAttribute('aria-current', String(k === i)));
  if (box.dataset.caps) {
    const cap = box.querySelector<HTMLElement>('[data-gcap]');
    if (cap) cap.textContent = (JSON.parse(box.dataset.caps) as string[])[i] ?? '';
  }
  box.dispatchEvent(new CustomEvent('photo', { detail: i }));
}

document.addEventListener('click', (e) => {
  const b = (e.target as Element).closest<HTMLElement>('[data-alt]');
  const box = b?.closest<HTMLElement>('[data-box]');
  if (b && box) showAlt(box, Number(b.dataset.alt));
});
