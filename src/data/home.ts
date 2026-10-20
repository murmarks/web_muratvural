/**
 * Ana sayfanın içeriği. Bütün bilgiler eski siteden ve oradaki fotoğraflardan geliyor.
 * Metinler her dil için ayrı; yeni dil eklenince tip denetimi eksikleri gösterir.
 */
import type { Locale } from '../config/site';
import { photo, servicePhoto, type Photo } from './photos';

type Text = Record<Locale, string>;
const T = (en: string, tr: string): Text => ({ en, tr });

/** Sahadan: her yer kendi fotoğraflarıyla. Altyazı "Ülke, Şehir" biçiminde. */
export interface Place {
  country: Text;
  city: Text;
  /** şehir adı yabancıysa büyük harfe o dilin kurallarıyla çevrilir (Ho Chi Minh City → CITY, CİTY değil) */
  cityLang?: Locale;
  what: Text;
  photos: Photo[];
}
export const places: Place[] = [
  { country: T('Kosovo', 'Kosova'), city: T('Pristina', 'Priştine'), what: T('Pharmaceutical training for the physicians on the team', 'Ekipteki hekimlere ilaç eğitimi'), photos: [photo('kosovo-1')] },
  { country: T('Moldova', 'Moldova'), city: T('Chișinău', 'Kişinev'), what: T('Training for field managers', 'Saha müdürleri eğitimi'), photos: [photo('moldova')] },
  { country: T('Bosnia and Herzegovina', 'Bosna-Hersek'), city: T('Sarajevo', 'Saraybosna'), what: T('With the local team at the office', 'Yerel ekiple ofiste çalışma'), photos: [photo('bosnia')] },
  { country: T('Georgia', 'Gürcistan'), city: T('Tbilisi', 'Tiflis'), what: T('Training seminar for physicians', 'Hekimler için eğitim semineri'), photos: [photo('georgia')] },
  { country: T('Croatia', 'Hırvatistan'), city: T('Zagreb', 'Zagreb'), what: T('Training seminar for physicians', 'Hekimler için eğitim semineri'), photos: [photo('croatia')] },
  { country: T('Azerbaijan', 'Azerbaycan'), city: T('Baku', 'Bakü'), what: T('Annual basic-science training for the field teams', 'Saha ekiplerine yıllık temel bilimler eğitimi'), photos: [photo('azerbaijan')] },
  { country: T('Libya', 'Libya'), city: T('Tripoli', 'Trablus'), what: T('Pharmacy visit in the field', 'Sahada eczane ziyareti'), photos: [photo('libya')] },
  { country: T('Türkiye', 'Türkiye'), city: T('Antalya', 'Antalya'), what: T('Sales techniques training for the field teams', 'Saha ekiplerine satış teknikleri eğitimi'), photos: ['antalya-1', 'antalya-3', 'antalya-4'].map((n) => photo(n)) },
  { country: T('Vietnam', 'Vietnam'), city: T('Ho Chi Minh City', 'Ho Chi Minh City'), cityLang: 'en', what: T('Training seminar for the Phano pharmacy chain', 'Phano eczane zinciri için eğitim semineri'), photos: ['vietnam-1', 'vietnam-2', 'vietnam-3'].map((n) => photo(n)) },
];

/** Aynı satış teknikleri slaytı, anlatıldığı beş dilde */
export const slides = [
  { lang: 'en', name: 'English', title: 'Sales Techniques Training', photo: photo('slide-en') },
  { lang: 'ru', name: 'Русский', title: 'Обучение методам продаж', photo: photo('slide-ru') },
  { lang: 'vi', name: 'Tiếng Việt', title: 'Đào tạo kỹ thuật bán hàng', photo: photo('slide-vi') },
  { lang: 'ka', name: 'ქართული', title: 'გაყიდვის მეთოდების შესწავლა', photo: photo('slide-ka') },
  { lang: 'tr', name: 'Türkçe', title: 'Satış Teknikleri', photo: photo('slide-tr') },
];

/** Ülke adları (kariyer listesindeki sırayla) */
export const countryNames: Record<string, Text> = {
  Turkey: T('Türkiye', 'Türkiye'), Croatia: T('Croatia', 'Hırvatistan'), Georgia: T('Georgia', 'Gürcistan'), Kosovo: T('Kosovo', 'Kosova'),
  Libya: T('Libya', 'Libya'), Turkmenistan: T('Turkmenistan', 'Türkmenistan'), Moldova: T('Moldova', 'Moldova'), Yemen: T('Yemen', 'Yemen'),
  Azerbaijan: T('Azerbaijan', 'Azerbaycan'), Bosnia: T('Bosnia and Herzegovina', 'Bosna-Hersek'), Vietnam: T('Vietnam', 'Vietnam'),
  Ukraine: T('Ukraine', 'Ukrayna'), Kyrgyzstan: T('Kyrgyzstan', 'Kırgızistan'), Tajikistan: T('Tajikistan', 'Tacikistan'), Armenia: T('Armenia', 'Ermenistan'),
  Uzbekistan: T('Uzbekistan', 'Özbekistan'), Belarus: T('Belarus', 'Belarus'), Kazakhstan: T('Kazakhstan', 'Kazakistan'), Iraq: T('Iraq', 'Irak'),
  Bulgaria: T('Bulgaria', 'Bulgaristan'),
};

/**
 * Şirket adı: marka kısmı ve sayfanın dilinde yazılan tür eki (İlaç / Pharmaceuticals).
 * `lang`, markanın büyük harfe çevrilirken hangi dilin kurallarına uyacağını söyler (İ/I);
 * verilmezse sayfanın dili kullanılır (Bilim: Türkçe sayfada BİLİM, İngilizce sayfada BILIM).
 */
export interface Company { brand: string; lang?: Locale; kind?: Text }
const pharma = T('Pharmaceuticals', 'İlaç');
const nutrifarma: Company = { brand: 'NutriFarma H.P. Inc.', lang: 'en' };
const bilim: Company = { brand: 'Bilim', kind: pharma };
const worldMedicine: Company = { brand: 'World Medicine', lang: 'en', kind: pharma };

export interface Role { years: number; company: Company; role: Text; detail: Text; people?: number; countries: string[]; from: number; to: number }
const roles: Omit<Role, 'from' | 'to'>[] = [
  { years: 3, company: nutrifarma, role: T('Marketing Planning Manager', 'Pazarlama Planlama Müdürü'), detail: T('Annual business plan, marketing and advertising, international relations', 'Yıllık iş planı, pazarlama ve reklam, uluslararası ilişkiler'), countries: ['Turkey'] },
  { years: 1, company: bilim, role: T('Business Development Manager', 'İş Geliştirme Müdürü'), detail: T('New partnerships, training of foreign teams, international logistics and registrations', 'Yeni iş birlikleri, yabancı ekiplerin eğitimi, uluslararası lojistik ve ruhsatlandırma'), countries: ['Turkey'] },
  { years: 3, company: bilim, role: T('International Marketing Manager', 'Uluslararası Pazarlama Müdürü'), people: 350, detail: T('Managing the country sales and marketing teams', 'Ülke satış ve pazarlama ekiplerinin yönetimi'), countries: ['Croatia', 'Georgia', 'Kosovo', 'Libya', 'Turkmenistan', 'Moldova', 'Yemen', 'Azerbaijan', 'Bosnia'] },
  { years: 7, company: worldMedicine, role: T('International Sales & Marketing Director', 'Uluslararası Satış ve Pazarlama Direktörü'), people: 800, detail: T('Building sales and marketing organisations from scratch and running them', 'Satış ve pazarlama organizasyonlarının sıfırdan kurulması ve yönetimi'), countries: ['Vietnam', 'Ukraine', 'Georgia', 'Kosovo', 'Libya', 'Turkmenistan', 'Kyrgyzstan', 'Tajikistan', 'Armenia', 'Moldova', 'Yemen', 'Azerbaijan', 'Bosnia', 'Uzbekistan', 'Belarus', 'Kazakhstan', 'Iraq', 'Bulgaria'] },
  { years: 6, company: worldMedicine, role: T('World Regional Director', 'Dünya Bölge Direktörü'), detail: T('Southeast Asia operations, supervising product registrations', 'Güneydoğu Asya operasyonları, ürün ruhsatlarının yönetimi'), countries: ['Vietnam'] },
];
/** Kariyer, yirmi yıllık çizgide yerleriyle (1. yıldan 20. yıla) */
export const career: Role[] = (() => {
  let y = 0;
  return roles.map((r) => { const from = y + 1; y += r.years; return { ...r, from, to: y }; });
})();
export const careerYears = career.reduce((s, r) => s + r.years, 0);

/** Çalışılan şirketler, ilk görüldükleri sırayla */
export const companies = [...new Set(career.map((r) => r.company))];

/** Türkiye dışında çalışılan ülkeler, ilk görüldükleri sırayla */
export const abroad = [...new Set(career.flatMap((r) => r.countries))].filter((c) => c !== 'Turkey');

/** Dört hizmet alanının giriş metni (eski sitedeki alan sayfalarının ilk paragrafı) */
export const areaIntro: Record<string, Text> = {
  a: T('Setting up a business in a new market requires the disciplined execution of <b>market assessment</b>, <b>proper positioning</b>, <b>legal/compliance steps</b> and a <b>logistics setup</b> at the same time. With multi‑country field and management experience, we offer an end‑to‑end setup model that turns an idea into a <b>scalable operation</b>.', 'Yeni bir pazarda iş kurmak; <b>pazar okuması</b>, <b>doğru konumlandırma</b>, <b>hukuki/uyum adımları</b> ve <b>lojistik kurgunun</b> aynı anda disiplinli yürütülmesini gerektirir. Çok ülkeli saha ve yönetim deneyimimizle, fikri <b>ölçeklenebilir işletmeye</b> dönüştüren uçtan uca bir kurulum modeli sunuyoruz.'),
  b: T('We read the current operation in the <b>cost-speed-quality</b> balance, reduce waste, and increase <b>added value</b>.', 'Mevcut operasyonu <b>maliyet–hız–kalite</b> dengesinde okuyup israfı azaltır, <b>katma değeri</b> artırırız.'),
  c: T('We design <b>role‑based</b> and <b>performance‑oriented</b> programs for your teams: a modular architecture spanning from basic technical competency, product knowledge, and sales techniques to <b>leadership</b> and <b>marketing strategy</b> trainings. The aim is for learning to turn into behavior and <b>measurable results</b>.', 'Ekipleriniz için <b>rol tabanlı</b> ve <b>performans odaklı</b> programlar tasarlarız: Temel teknik yetkinlikten, ürün bilgisinden ve satış tekniklerinden <b>liderlik</b> ve <b>pazarlama stratejisi</b> eğitimlerine uzanan modüler mimari. Amaç; öğrenilenin davranışa ve <b>ölçülebilir sonuca</b> dönüşmesidir.'),
  d: T('We scale your operations <b>remotely</b> and <b>on-demand</b>, offering cost flexibility, speed, and expertise advantages simultaneously. For virtual office, call center, back office, customer interaction, and project-based tasks, we provide <b>end-to-end</b> setup–operation–improvement support.', 'Operasyonlarınızı <b>uzaktan</b> ve <b>talebe bağlı</b> şekilde ölçekleyerek; maliyet esnekliği, hız ve uzmanlık avantajını aynı anda sunarız. Sanal ofis, çağrı merkezi, arka ofis, müşteri etkileşimi ve proje tabanlı görevler için <b>uçtan uca</b> kurulum–işletim–iyileştirme desteği veririz.'),
};

/** Her alanın tanıtım fotoğrafları (alan sayfalarındaki fotoğraflardan seçildi) */
export const areaPhotos: Record<string, Photo[]> = {
  a: ['a1_1', 'a12_1', 'a11_1', 'a4_1', 'a2_1', 'a_5'].map((n) => servicePhoto(n)),
  b: ['b42_1', 'b1_1', 'b41_1-1', 'b3_1', 'b43_2', 'b2_1'].map((n) => servicePhoto(n)),
  c: ['c1_1', 'c7_1', 'c5_1', 'c41_1', 'c3_3', 'c4_2'].map((n) => servicePhoto(n)),
  d: ['d_1', 'd2_4', 'd1_4', 'd1_1', 'd4_1', 'd2_1'].map((n) => servicePhoto(n)),
};

/**
 * Ana başlık: iki satır. Sayfa her açılışta bunlardan birini rastgele gösterir (bir önceki açılıştakini tekrar etmez).
 * Yeni bir başlık eklemek için listeye bir satır daha eklemek yeter.
 */
export const heroTitles: { one: Text; two: Text }[] = [
  { one: T('New markets.', 'Yeni pazarlar.'), two: T('Strong teams.', 'Güçlü ekipler.') },
  { one: T('Different flags.', 'Farklı bayraklar.'), two: T('One team.', 'Tek ekip.') },
];

export const heroPhotos = { color: photo('team-color'), focus: photo('team-focus'), portrait: photo('portrait') };
