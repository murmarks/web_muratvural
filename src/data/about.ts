/**
 * Hakkında sayfası. Metin, eski sitedeki "dr-murat-vural" sayfasından (EN: pebuc.site, TR: muratvural.online)
 * paragraf paragraf aynen alındı; yalnızca sayfadaki yerleri yeni tasarıma göre gruplandı.
 * Fotoğraflar da o sayfadakiler (public/photos/about/).
 */
import type { Locale } from '../config/site';
import { photo, type Photo } from './photos';

export interface About {
  title: string;
  /** ilk paragraf, başlığın altında büyük */
  lede: string;
  /** Ankara'dan uluslararası kariyere */
  career: string[];
  todayTitle: string;
  /** Vietnam ve bugünkü iş */
  today: string[];
  photos: { podium: Photo; career: Photo; vietnam: Photo; map: Photo };
}

export const about: Record<Locale, About> = {
  en: {
    title: "Who is Dr. Murat Vural?",
    lede: "After graduating from Ankara University Faculty of Medicine — a well-established institution providing world-standard education — Murat Vural practiced as a physician for several years and then took on senior management and training roles at an international level in the pharmaceutical sector. He was responsible for the coordination and operational efficiency of teams composed of various nationalities in different regions. Additionally, he provided intensive training for many years to team members and physicians in the countries where he worked, contributing to their professional development by sharing his sectoral and scientific knowledge.",
    career: [
      "Born in Ankara, Murat Vural completed his primary and secondary education in his hometown and achieved success by ranking <b>among the top 500 nationwide in Turkey</b> in the university entrance exam conducted by <b>ÖSYM</b>. His deep interest in science — even beyond his early fascination with technology — led him to Ankara University Faculty of Medicine. After completing his medical education, he began working as a physician in Ankara; later he transitioned to the pharmaceutical sector in Istanbul, taking the first steps of his professional career focused on training and management. This journey gradually gained an international dimension.",
      "During his <b>more than twenty years</b> of pharmaceutical sector experience, he led an intensive and fast-paced professional life. In some periods, he resided in different countries for three months to a year, while in other periods he worked based in Turkey and traveled to four to six countries per month to provide on-site training and management services. His operational activities covered a wide cultural geography including <b>Croatia, Turkmenistan, Moldova, Libya, Vietnam, Yemen, Russia and Bosnia</b>. In these countries, he provided <b>medical, scientific and sectoral training to over a thousand team members — mostly medical professionals</b>.",
      "He built these teams from scratch, managed them with performance evaluations and operational planning, and directly contributed to their development.",
      "Throughout his many years of international career, Murat Vural carefully identified <b>high-potential young professionals</b> within his teams and guided their development with <b>one-on-one mentoring</b> support. In every country where he worked, he trained <b>middle and senior managers</b> and <b>trainers</b>, contributing to the strengthening of local leadership structures. Over time, he transformed traditional field management processes into an innovative remote management system, increasing efficiency and enabling his teams to achieve sustainable success.",
    ],
    todayTitle: "Today...",
    today: [
      "Having built his life in Vietnam with his wife whom he met there, Dr. Murat Vural launched the <b>Performance Business Consulting (PeBuC)</b> brand by combining his many years of international experience and leadership accumulation with a new vision.",
      "PeBuC is an international consulting and training platform built upon Dr. Vural's strategic and operational experience gained at a global level. The company provides comprehensive services in the areas of <b>corporate structuring, new market expansion, performance improvement, sales and marketing strategies, leadership development</b> and <b>remote management solutions</b>.",
      "Today, PeBuC; while based in Vietnam, collaborates with companies operating in different parts of the world including <b>Asia, Europe and the Middle East</b>. Each project is specially designed according to the institution's needs; concrete and applicable solutions are provided in areas such as <b>market research, business plan development, performance systems design</b> and <b>team training</b>.",
      "Dr. Vural's multidisciplinary accumulation in the fields of <b>medicine, business development and training</b> brings a unique perspective to PeBuC's consulting approach. This approach combines analytical thinking with human-centered leadership; it aims for companies to achieve not only short-term but <b>lasting and sustainable success</b>.",
      "Today, Dr. Murat Vural offers the international knowledge he has accumulated under the PeBuC umbrella to companies of all sizes that want to <b>strengthen corporate performance, transform teams and unlock the potential of businesses</b>. His purpose is to enable institutions to make a difference in the global arena by bringing together knowledge, strategy and team spirit.",
    ],
    photos: { podium: photo('about/podium', "Dr. Murat Vural"), career: photo('about/career-en', "Resume"), vietnam: photo('about/vietnam', "Vietnam"), map: photo('about/map', "Map of Vietnam and Türkiye") },
  },
  tr: {
    title: "Dr. Murat Vural Kimdir?",
    lede: "Ankara Üniversitesi Tıp Fakültesi’nden — dünya standartlarında eğitim veren köklü bir kurumdan — mezun olduktan sonra Murat Vural, birkaç yıl hekimlik yaptı ve ardından ilaç sektöründe uluslararası düzeyde üst düzey yönetici ve eğitici roller üstlendi. Farklı bölgelerde, çeşitli milletlerden oluşan ekiplerin koordinasyon ve operasyonel verimliliğinden sorumlu oldu. Ayrıca görev yaptığı ülkelerdeki ekip üyelerine ve hekimlere uzun yıllar boyunca yoğun eğitimler vererek, sektörel ve bilimsel bilgi birikimini paylaşmak suretiyle onların mesleki gelişimlerine katkı sağladı.",
    career: [
      "Ankara doğumlu olan Murat Vural, ilkokul ve ortaöğretimini memleketinde tamamladı ve <b>ÖSYM</b> tarafından yapılan üniversite giriş sınavında <b>Türkiye genelinde ilk 500</b> arasına girme başarısını gösterdi. Bilime olan derin ilgisi — genç yaşlardaki teknoloji merakının bile ötesinde — onu Ankara Üniversitesi Tıp Fakültesi’ne yönlendirdi. Tıp eğitimini tamamladıktan sonra Ankara’da hekim olarak çalışmaya başladı; daha sonra İstanbul’da ilaç sektörüne geçiş yaparak eğitim ve yönetim odaklı profesyonel kariyerinin ilk adımlarını attı. Bu yolculuk zamanla uluslararası bir boyut kazandı.",
      "<b>Yirmi yılı aşkın</b> ilaç sektörü deneyimi süresince yoğun ve tempolu bir profesyonel yaşam sürdürdü. Kimi dönemlerde üç ay ile bir yıl arası farklı ülkelerde ikamet etti, kimi dönemlerde ise Türkiye merkezli çalışıp ayda dört ila altı ülkeye seyahat ederek yerinde eğitim ve yönetim hizmetleri verdi. Operasyonel faaliyetleri <b>Hırvatistan, Türkmenistan, Moldova, Libya, Vietnam, Yemen, Rusya ve Bosna</b> gibi geniş bir kültürel coğrafyayı kapsadı. Bu ülkelerde <b>binin üzerinde ekip üyesine — çoğu tıp kökenli profesyonellere — tıbbi, bilimsel ve sektörel eğitimler</b> sundu.",
      "Bu ekipleri sıfırdan kurdu, performans değerlendirmeleri ve operasyon planlamalarıyla yönetti, gelişimlerine doğrudan katkı sağladı.",
      "Uzun yıllar süren uluslararası kariyeri boyunca Murat Vural, ekipleri içinde <b>yüksek potansiyelli genç profesyonelleri</b> dikkatle belirleyip <b>birebir mentorluk</b> desteğiyle gelişimlerine yön verdi. Görev yaptığı her ülkede <b>orta ve üst düzey yöneticiler</b> ile <b>eğitmenleri</b> yetiştirerek yerel liderlik yapılarının güçlenmesine katkı sundu. Zamanla geleneksel saha yönetim süreçlerini yenilikçi bir uzaktan yönetim sistemine dönüştürerek verimliliği artırdı ve ekiplerinin sürdürülebilir başarı elde etmesini sağladı.",
    ],
    todayTitle: "Bugün...",
    today: [
      "Vietnam’da tanıştığı eşiyle birlikte hayatını orada kuran Dr. Murat Vural, uzun yıllar süren uluslararası tecrübelerini ve liderlik birikimini yeni bir vizyonla birleştirerek <b>Performance Business Consulting (PeBuC)</b> markasını hayata geçirdi.",
      "PeBuC, Dr. Vural’ın küresel düzeyde edindiği stratejik ve operasyonel deneyim üzerine inşa edilmiş uluslararası bir danışmanlık ve eğitim platformudur. Şirket, <b>kurumsal yapılanma, yeni pazar açılımı, performans artırma, satış ve pazarlama stratejileri, liderlik gelişimi</b> ve <b>uzaktan yönetim çözümleri</b> alanlarında kapsamlı hizmetler sunmaktadır.",
      "Bugün PeBuC; Vietnam merkezli olmakla birlikte, <b>Asya, Avrupa ve Orta Doğu</b> dahil olmak üzere dünyanın farklı bölgelerinde faaliyet gösteren şirketlerle iş birliği yapmaktadır. Her proje, kurumun ihtiyaçlarına göre özel olarak tasarlanmakta; <b>pazar araştırması, iş planı geliştirme, performans sistemleri tasarımı</b> ve <b>ekip eğitimi</b> gibi alanlarda somut ve uygulanabilir çözümler sunulmaktadır.",
      "Dr. Vural’ın <b>tıp, iş geliştirme ve eğitim</b> alanlarındaki çok disiplinli birikimi, PeBuC’un danışmanlık anlayışına benzersiz bir bakış kazandırmaktadır. Bu yaklaşım, analitik düşünme ile insan odaklı liderliği birleştirir; şirketlerin yalnızca kısa vadeli değil, <b>kalıcı ve sürdürülebilir başarılar</b> elde etmesini hedefler.",
      "Bugün Dr. Murat Vural, PeBuC çatısı altında edindiği uluslararası bilgi birikimini; <b>kurumsal performansı güçlendirmek, ekipleri dönüştürmek ve işletmelerin potansiyelini açığa çıkarmak</b> isteyen her ölçekteki şirkete sunmaktadır. Amacı; bilgi, strateji ve takım ruhunu bir araya getirerek, kurumların küresel arenada fark yaratmalarını sağlamaktır.",
    ],
    photos: { podium: photo('about/podium', "Dr. Murat Vural"), career: photo('about/career-tr', "Özgeçmiş"), vietnam: photo('about/vietnam', "Vietnam"), map: photo('about/map', "Vietnam ve Türkiye haritası") },
  },
};
