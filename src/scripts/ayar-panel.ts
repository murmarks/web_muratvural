/**
 * Site sahibinin ayar paneli: adresin sonuna ?ayar eklenince sağ altta açılır.
 * Şifre girilmeden hiçbir ayar görünmez; şifreyi sunucu denetler (public/ayar.php), sekme kapanana kadar hatırlanır.
 * Şifre girildikten sonra panel aynı sekmede gezilen her sayfada açık kalır ve henüz kaydedilmemiş ayarlar da
 * sayfadan sayfaya taşınır (sessionStorage); "Çık"a basınca kapanır.
 * Her değişiklik sayfada hemen görünür ama yalnızca "Kaydet"e basınca sitenin kendisi değişir.
 * "Varsayılanlar" src/config/ayar.ts'deki ilk ayarları getirir; "Dosyaya indir" o anki ayarları okunur bir
 * dosyaya yazar, "Dosyadan yükle" o dosyayı geri getirir. İkisinden sonra da geçerli olması için Kaydet gerekir.
 */
import { AD, EN_FAZLA, SECENEK, VARSAYILAN, ayniMi, dosyaOku, dosyaYaz, duzelt, type Ayar } from '../config/ayar';
import { uygula } from './ayar';

const SIFRE = 'mv-sifre', PANEL = 'mv-panel', TASLAK = 'mv-taslak', KUCUK = 'mv-kucuk';
const kopya = (a: Ayar): Ayar => JSON.parse(JSON.stringify(a));
const oku = (k: string) => {
  try {
    return sessionStorage.getItem(k) ?? '';
  } catch {
    return '';
  }
};
const yaz = (k: string, v: string) => {
  try {
    if (v) sessionStorage.setItem(k, v);
    else sessionStorage.removeItem(k);
  } catch {
    /* gizli pencere: her açılışta şifre sorulur */
  }
};

async function sunucu(govde: object): Promise<{ durum: number; veri: Record<string, unknown> }> {
  try {
    const r = await fetch('/ayar.php', { method: 'POST', headers: { 'content-type': 'application/json' }, body: JSON.stringify(govde), cache: 'no-store' });
    return { durum: r.status, veri: await r.json().catch(() => ({})) };
  } catch {
    return { durum: 0, veri: {} };
  }
}

const box = document.createElement('section');
box.className = 'ap';
box.setAttribute('aria-label', 'Site ayarları');
document.body.append(box);

let sifre = oku(SIFRE);
let kayitli: Ayar = duzelt(window.mvAyarOnbellek);
let taslak: Ayar = kopya(kayitli);
let sunucuOkundu = false;

const bas = `<header class="ap-bas"><strong>Site ayarları</strong><span><button type="button" class="ap-x" data-kucult aria-label="Küçült" aria-expanded="true">–</button><button type="button" class="ap-x" data-cik aria-label="Kapat">×</button></span></header>`;
const not = (msg: string, tur: '' | 'iyi' | 'kotu' = '') => {
  const p = box.querySelector<HTMLElement>('.ap-not');
  if (!p) return;
  p.textContent = msg;
  p.dataset.tur = tur;
};

/* ---------- kilit ---------- */
function kilit(msg = '') {
  box.innerHTML = `${bas}<form class="ap-kilit"><label>Şifre<input type="password" name="sifre" autocomplete="current-password" required></label><button type="submit" class="ap-ana">Aç</button></form><p class="ap-not" role="status"></p>`;
  if (msg) not(msg, 'kotu');
  const f = box.querySelector('form')!;
  const inp = f.querySelector('input')!;
  inp.focus();
  f.addEventListener('submit', async (e) => {
    e.preventDefault();
    const b = f.querySelector('button')!;
    b.disabled = true;
    not('Denetleniyor…');
    const { durum } = await sunucu({ sifre: inp.value });
    b.disabled = false;
    if (durum === 200) {
      sifre = inp.value;
      yaz(SIFRE, sifre);
      ac();
    } else {
      not(durum === 403 ? 'Şifre yanlış.' : 'Sunucuya ulaşılamadı. Bağlantıyı kontrol edip yeniden deneyin.', 'kotu');
      inp.select();
    }
  });
}

/* ---------- ayarlar ---------- */
const kaydirici = (sec: string, k: string, v: number, ipucu = '') =>
  `<label class="ap-k"><span class="ap-ad">${AD[k]}</span><span class="ap-ray"><input type="range" min="0" max="${EN_FAZLA}" step="1" value="${v}" list="ap-izler" data-sec="${sec}" data-key="${k}"><output>${v}</output></span>${ipucu ? `<small>${ipucu}</small>` : ''}</label>`;
const secim = (sec: string, k: string, v: string, secenekler: string[]) =>
  `<div class="ap-satir"><span class="ap-ad">${AD[k]}</span><div class="ap-sec" role="group" aria-label="${AD[k]}">${secenekler
    .map((o) => `<button type="button" data-sec="${sec}" data-key="${k}" data-v="${o}" aria-pressed="${o === v}">${AD[o] ?? o}</button>`)
    .join('')}</div></div>`;
const anahtar = (sec: 'harita' | 'toz', acik: boolean) =>
  `<label class="ap-acik"><input type="checkbox" data-sec="${sec}" data-key="acik" ${acik ? 'checked' : ''}> ${AD[sec]}</label>`;

function ciz() {
  const a = taslak;
  const govde = box.querySelector('.ap-govde');
  const kaydir = govde?.scrollTop ?? 0;
  box.innerHTML = `${bas}
  <div class="ap-govde">
    <fieldset><legend>${AD.site}</legend>
      ${secim('site', 'tema', a.site.tema, SECENEK.tema)}
      ${secim('site', 'dil', a.site.dil, SECENEK.dil)}
      <small>Otomatik: Türkiye saatindeki ya da Türkçe tarayıcı Türkçe, diğerleri İngilizce açar. Ziyaretçi dili her zaman kendisi değiştirebilir.</small>
    </fieldset>
    <fieldset><legend>${AD.zemin}</legend>
      ${kaydirici('zemin', 'kayma', a.zemin.kayma, '0 sabit · 25 = 4\'e 1 · 100 sayfayla birlikte · 125 sayfanın 2 katı · 150 sayfanın 3 katı')}
    </fieldset>
    <fieldset data-kapali="${!a.harita.acik}"><legend>${anahtar('harita', a.harita.acik)}</legend>
      ${kaydirici('harita', 'noktalar', a.harita.noktalar)}
      ${kaydirici('harita', 'sari', a.harita.sari)}
      ${kaydirici('harita', 'ucus_yogunlugu', a.harita.ucus_yogunlugu)}
    </fieldset>
    <fieldset data-kapali="${!a.toz.acik}"><legend>${anahtar('toz', a.toz.acik)}</legend>
      ${kaydirici('toz', 'gorunurluk', a.toz.gorunurluk)}
      ${kaydirici('toz', 'yogunluk', a.toz.yogunluk)}
    </fieldset>
    <datalist id="ap-izler"><option value="0"></option><option value="100"></option><option value="${EN_FAZLA}"></option></datalist>
  </div>
  <footer class="ap-alt">
    <div class="ap-dugmeler">
      <button type="button" data-varsayilan>Varsayılanlar</button>
      <button type="button" data-indir>Dosyaya indir</button>
      <button type="button" data-yukle>Dosyadan yükle</button>
      <input type="file" accept=".txt,.ini,.json,text/plain,application/json" hidden>
    </div>
    <div class="ap-dugmeler">
      <button type="button" class="ap-ana" data-kaydet>Kaydet</button>
      <button type="button" data-cik>Çık</button>
    </div>
    <p class="ap-not" role="status"></p>
  </footer>`;
  const g = box.querySelector('.ap-govde');
  if (g) g.scrollTop = kaydir;
  if (box.classList.contains('kucuk')) {
    const k = box.querySelector('[data-kucult]')!;
    k.textContent = '+';
    k.setAttribute('aria-label', 'Büyüt');
    k.setAttribute('aria-expanded', 'false');
  }
  durum();
}

const degisti = () => !ayniMi(taslak, kayitli);
/** Ayarı sayfada gösterir ve sekmede saklar: başka sayfaya geçince de aynı önizleme sürer. */
function onizle(a: Ayar) {
  taslak = a;
  uygula(a);
  yaz(TASLAK, JSON.stringify(a));
}
function durum(msg?: string, tur: '' | 'iyi' | 'kotu' = '') {
  box.querySelector('[data-kaydet]')?.toggleAttribute('data-bekliyor', degisti());
  if (msg !== undefined) not(msg, tur);
  else if (!sunucuOkundu) not('Sunucudaki kayıt okunamadı; bu tarayıcının bildiği son ayarlar gösteriliyor.', 'kotu');
  else not(degisti() ? 'Kaydedilmemiş değişiklik var. Sitenin kendisi Kaydet\'e basınca değişir.' : 'Site şu an bu ayarlarla açılıyor.');
}

function degistir(sec: string, k: string, v: unknown) {
  const t = kopya(taslak) as unknown as Record<string, Record<string, unknown>>;
  t[sec][k] = v;
  onizle(duzelt(t));
}

function ac() {
  yaz(PANEL, '1');
  ciz();
}

box.addEventListener('input', (e) => {
  const el = e.target as HTMLInputElement;
  if (!el.dataset.sec) return;
  if (el.type === 'range') {
    degistir(el.dataset.sec, el.dataset.key!, Number(el.value));
    const o = el.nextElementSibling;
    if (o) o.textContent = el.value;
    durum();
  } else if (el.type === 'checkbox') {
    degistir(el.dataset.sec, el.dataset.key!, el.checked);
    el.closest('fieldset')?.setAttribute('data-kapali', String(!el.checked));
    durum();
  }
});

box.addEventListener('change', async (e) => {
  const el = e.target as HTMLInputElement;
  if (el.type !== 'file' || !el.files?.[0]) return;
  const f = el.files[0];
  el.value = '';
  if (f.size > 20000) return durum('Bu dosya bir ayar dosyası değil.', 'kotu');
  onizle(dosyaOku(await f.text()));
  ciz();
  durum(`"${f.name}" yüklendi. Sitenin kendisi Kaydet'e basınca değişir.`);
});

box.addEventListener('click', async (e) => {
  const b = (e.target as HTMLElement).closest('button');
  if (!b) return;
  if (b.hasAttribute('data-kucult')) {
    // sayfayı görmek için panel başlığına kadar küçülür
    const k = box.classList.toggle('kucuk');
    yaz(KUCUK, k ? '1' : '');
    b.textContent = k ? '+' : '–';
    b.setAttribute('aria-label', k ? 'Büyüt' : 'Küçült');
    b.setAttribute('aria-expanded', String(!k));
  } else if (b.dataset.v !== undefined) {
    degistir(b.dataset.sec!, b.dataset.key!, b.dataset.v);
    b.parentElement!.querySelectorAll('button').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
    durum();
  } else if (b.hasAttribute('data-varsayilan')) {
    onizle(kopya(VARSAYILAN));
    ciz();
    durum('Varsayılan ayarlar yüklendi. Sitenin kendisi Kaydet\'e basınca değişir.');
  } else if (b.hasAttribute('data-indir')) {
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([dosyaYaz(taslak)], { type: 'text/plain;charset=utf-8' }));
    a.download = `muratvural-ayarlar-${new Date().toISOString().slice(0, 10)}.txt`;
    document.body.append(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(a.href), 4000);
  } else if (b.hasAttribute('data-yukle')) {
    box.querySelector<HTMLInputElement>('input[type=file]')?.click();
  } else if (b.hasAttribute('data-kaydet')) {
    b.disabled = true;
    durum('Kaydediliyor…');
    const { durum: kod, veri } = await sunucu({ sifre, ayar: taslak });
    b.disabled = false;
    if (kod === 200 && veri.ayar) {
      kayitli = duzelt(veri.ayar);
      onizle(kopya(kayitli));
      sunucuOkundu = true;
      try {
        localStorage.setItem('mv-ayar', JSON.stringify(veri.ayar));
      } catch {
        /* bu tarayıcı saklamıyorsa site yine sunucudan okur */
      }
      durum('Kaydedildi. Site artık bu ayarlarla açılıyor.', 'iyi');
    } else if (kod === 403) {
      yaz(SIFRE, '');
      kilit('Şifre artık geçerli değil; yeniden girin. Değişiklikler sayfada duruyor, kaydedilmedi.');
    } else durum('Kaydedilemedi. Bağlantıyı kontrol edip yeniden deneyin.', 'kotu');
  } else if (b.hasAttribute('data-cik')) {
    if (degisti() && box.querySelector('.ap-govde') && !confirm('Kaydedilmemiş değişiklikler var. Kaydetmeden çıkılsın mı?')) return;
    taslak = kopya(kayitli);
    uygula(kayitli);
    box.remove();
    window.mvPanel = false;
    [PANEL, TASLAK, KUCUK].forEach((k) => yaz(k, ''));
    const u = new URL(location.href);
    u.searchParams.delete('ayar');
    history.replaceState(history.state, '', u);
  }
});

/* açılış: önce sunucudaki kayıt, sonra şifre (bu sekmede daha önce girildiyse sessizce denetlenir) */
box.innerHTML = `${bas}<p class="ap-not" role="status">Yükleniyor…</p>`;
if (oku(KUCUK)) box.classList.add('kucuk');
const s = await (window.mvAyar ?? Promise.resolve(null));
sunucuOkundu = !!s;
if (s) kayitli = duzelt(s);
// önceki sayfada kaydedilmeden bırakılan ayarlar sürer
const onceki = oku(PANEL) && oku(TASLAK);
taslak = onceki ? duzelt(JSON.parse(onceki)) : kopya(kayitli);
uygula(taslak);
if (sifre && (await sunucu({ sifre })).durum === 200) ac();
else {
  [SIFRE, PANEL, TASLAK].forEach((k) => yaz(k, ''));
  kilit();
}
