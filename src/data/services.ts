/**
 * Hizmet ağacı. Eski WordPress sitesindeki sol menünün (akordiyon) birebir karşılığı.
 * Başlıklar eski sitelerin menülerinden (EN: pebuc.site, TR: muratvural.online) aynen alındı.
 * `wp` alanı eski adresin kısa adı (pebuc.site/menu-a1/ gibi); sayfa metni ve yönlendirmeler buradan bulunur.
 */
import type { Locale } from '../config/site';

export interface Service {
  /** Eski WordPress kısa adı, ör. "a1" → /menu-a1/ ve src/content/services/<dil>/a1.json */
  wp: string;
  /** Menüde görünen numara, ör. "1.1" */
  no: string;
  title: Record<Locale, string>;
  /** Bu düğümün adres parçası (bütün dillerde aynı) */
  slug: string;
  children?: Service[];
}

const S = (wp: string, no: string, slug: string, en: string, tr: string, children?: Service[]): Service => ({
  wp, no, slug, title: { en, tr }, children,
});

export const services: Service[] = [
  S('a', '1', 'new-business-setup', 'New Business Setup', 'Yeni İş Kurulumu', [
    S('a1', '1.1', 'global-business-expansion', 'Global Business Expansion & Development', 'Küresel İş Genişlemesi ve Geliştirme', [
      S('a11', '1.1.1', 'export-strategy', 'Export Strategy & Partnering', 'İhracat Stratejisi ve Yeni İş Birlikleri'),
      S('a12', '1.1.2', 'import-strategy', 'Import Strategy & Partnering', 'İthalat Stratejisi ve Yeni İş Birlikleri'),
    ]),
    S('a2', '1.2', 'market-research', 'Market Research & Opportunity Analysis', 'Pazar Araştırması ve Fırsat Analizi'),
    S('a3', '1.3', 'strategic-marketing-plan', 'Strategic Marketing Plan Development', 'Stratejik Pazarlama Planı Geliştirme'),
    S('a4', '1.4', 'business-plan', 'Business Plan Creation & Financial Modeling', 'İş Planı Oluşturma ve Finansal Modelleme'),
    S('a5', '1.5', 'recruitment-administration', 'Recruitment and Administrative Structuring', 'İşe Alım ve İdari Yapılanma'),
    S('a6', '1.6', 'team-building', 'Sales & Marketing Team Building & Recruitment', 'Satış ve Pazarlama Ekibi Kurma ve İşe Alım'),
    S('a7', '1.7', 'legal-setup', 'Legal Setup & Product Registration', 'Hukuki Kurulum ve Ürün Tescili'),
    S('a8', '1.8', 'logistics-supply-chain', 'Logistics and Supply Chain Setup', 'Lojistik ve Tedarik Zinciri Kurulumu'),
  ]),
  S('b', '2', 'performance-enhancement', 'Performance Enhancement & Expansion', 'Performans Artırımı ve Genişleme', [
    S('b1', '2.1', 'productivity-audit', 'Business Analysis & Productivity Audit', 'İş Analizi ve Verimlilik Denetimi'),
    S('b2', '2.2', 'marketing-plan-review', 'Marketing Plan Review & Enhancement', 'Pazarlama Planı Gözden Geçirme ve İyileştirme'),
    S('b3', '2.3', 'hr-workforce', 'HR Structure & Workforce Optimization', 'İK Yapısı ve İşgücü Optimizasyonu'),
    S('b4', '2.4', 'team-performance', 'Sales & Marketing Team Performance Optimization', 'Satış ve Pazarlama Ekibi Performans Optimizasyonu', [
      S('b41', '2.4.1', 'diagnostics', 'Team Performance Diagnostics', 'Ekip Performansı Analizi'),
      S('b42', '2.4.2', 'performance-management-system', 'Performance Management System Design', 'Performans Yönetim Sistemi Kurulumu'),
      S('b43', '2.4.3', 'monitoring-kpis', 'Performance Monitoring & KPIs', 'Performans İzleme ve KPI’ler'),
      S('b44', '2.4.4', 'coaching', 'Coaching & Counseling Programs', 'Koçluk ve Danışmanlık Programları', [
        S('b441', '2.4.4.a', 'sales-representatives', 'For Sales Representatives', 'Satış Temsilcileri için'),
        S('b442', '2.4.4.b', 'field-sales-managers', 'For Field Sales Managers', 'Saha Satış Müdürleri için'),
        S('b443', '2.4.4.c', 'marketing-managers', 'For Marketing Managers', 'Pazarlama Müdürleri için'),
      ]),
    ]),
    S('b5', '2.5', 'legal-logistics-review', 'Legal & Logistics Efficiency Review', 'Hukuki ve Lojistik Verimlilik Değerlendirmesi'),
  ]),
  S('c', '3', 'training', 'Training Services', 'Eğitim Hizmetleri', [
    S('c1', '3.1', 'basic-technical', 'Basic Technical Training', 'Temel Teknik Eğitim'),
    S('c2', '3.2', 'product', 'Product Training', 'Ürün Eğitimi'),
    S('c3', '3.3', 'sales-techniques', 'Sales Techniques Training', 'Satış Teknikleri Eğitimi', [
      S('c31', '3.3.1', 'beginner', 'Beginner Level', 'Başlangıç Seviyesi'),
      S('c32', '3.3.2', 'intermediate', 'Intermediate Level', 'Orta Seviye'),
      S('c33', '3.3.3', 'advanced', 'Advanced Level', 'İleri Seviye'),
    ]),
    S('c4', '3.4', 'marketing-strategy', 'Marketing Strategy & Execution Training', 'Pazarlama Stratejisi ve Uygulama Eğitimi', [
      S('c41', '3.4.1', 'beginner', 'Beginner Level', 'Başlangıç Seviyesi'),
      S('c42', '3.4.2', 'intermediate', 'Intermediate Level', 'Orta Seviye'),
      S('c43', '3.4.3', 'advanced', 'Advanced Level', 'İleri Seviye'),
    ]),
    S('c5', '3.5', 'leadership-management', 'Leadership & Management Training', 'Liderlik ve Yönetim Eğitimi'),
    S('c6', '3.6', 'coaching-skills', 'Coaching & Counseling Skills for Managers', 'Yöneticiler için Koçluk ve Danışmanlık Becerileri'),
    S('c7', '3.7', 'train-the-trainer', 'Train-the-Trainer Programs', 'Eğitmen Eğitimi Programları'),
  ]),
  S('d', '4', 'remote-support', 'Remote & On-Demand Support', 'Uzaktan ve Talebe Bağlı Destek', [
    S('d1', '4.1', 'virtual-office', 'Virtual Office & Contact Center Setup', 'Sanal Ofis ve Çağrı Merkezi Kurulumu'),
    S('d2', '4.2', 'remote-operations', 'Remote Office & Operations Management', 'Uzak Ofis ve Operasyon Yönetimi'),
    S('d3', '4.3', 'customer-engagement', 'Remote Customer Engagement Solutions', 'Uzak Müşteri Etkileşimi Çözümleri'),
    S('d4', '4.4', 'tailored-support', 'Other Tailored Remote Support Services', 'Diğer Özelleştirilmiş Uzaktan Destek Hizmetleri'),
  ]),
];

export interface FlatService extends Service {
  /** services/ altındaki tam yol, ör. "new-business-setup/global-business-expansion" */
  path: string;
  /** Kökten bu düğüme kadar olan zincir (kendisi dahil) */
  trail: Service[];
}

/** Ağacı menü sırasıyla düz bir listeye çevirir; sayfa üretimi, önceki/sonraki, yönlendirme ve konum çubuğu için. */
export function flattenServices(nodes: Service[] = services, parents: Service[] = []): FlatService[] {
  return nodes.flatMap((node) => {
    const trail = [...parents, node];
    const path = trail.map((s) => s.slug).join('/');
    return [{ ...node, path, trail }, ...flattenServices(node.children ?? [], trail)];
  });
}

export const flatServices = flattenServices();
export const serviceByWp = Object.fromEntries(flatServices.map((s) => [s.wp, s])) as Record<string, FlatService>;

export const serviceHref = (lang: Locale, path: string) => `/${lang}/services/${path}/`;

/** Bir düğümün altındaki bütün sayfaların sayısı */

/** Eski WordPress adresleri → yeni adresler (astro.config.mjs içinde kullanılır). Eski site İngilizceydi. */
export function legacyServiceRedirects(): Record<string, string> {
  const lang = 'en' satisfies Locale;
  const map: Record<string, string> = { '/menu': `/${lang}/services/`, '/our-services': `/${lang}/services/` };
  for (const s of flatServices) map[`/menu-${s.wp}`] = serviceHref(lang, s.path);
  return map;
}

