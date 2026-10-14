/** Eski sitenin metinlerindeki satır içi HTML'den yalnızca kalın, italik ve satır sonuna izin verir; gerisini kaçırır. */
const ALLOWED = /^<\/?(b|i|em|strong)>$|^<br\s*\/?>$/i;
export const inline = (s = '') => s.replace(/<[^>]*>?/g, (tag) => (ALLOWED.test(tag) ? tag : tag.replace(/</g, '&lt;')));

/** Düz metin için HTML kaçışı */
export const esc = (s = '') => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
