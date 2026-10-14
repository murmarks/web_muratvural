/**
 * PeBuC hizmet ağacı. Eski WordPress sitesindeki sol menünün (akordiyon) birebir karşılığı.
 * `wp` alanı eski adresin kısa adı (pebuc.site/menu-a1/ gibi); yönlendirmeler buradan üretilir.
 */
export interface Service {
  /** Eski WordPress kısa adı, ör. "a1" → /menu-a1/ */
  wp: string;
  /** Menüde görünen numara, ör. "1.1" */
  no: string;
  title: string;
  /** Bu düğümün adres parçası */
  slug: string;
  children?: Service[];
}

export const services: Service[] = [
  {
    wp: 'a', no: '1', title: 'New Business Setup', slug: 'new-business-setup',
    children: [
      {
        wp: 'a1', no: '1.1', title: 'Global Business Expansion & Development', slug: 'global-business-expansion',
        children: [
          { wp: 'a11', no: '1.1.1', title: 'Export Strategy & Partnering', slug: 'export-strategy' },
          { wp: 'a12', no: '1.1.2', title: 'Import Strategy & Partnering', slug: 'import-strategy' },
        ],
      },
      { wp: 'a2', no: '1.2', title: 'Market Research & Opportunity Analysis', slug: 'market-research' },
      { wp: 'a3', no: '1.3', title: 'Strategic Marketing Plan Development', slug: 'strategic-marketing-plan' },
      { wp: 'a4', no: '1.4', title: 'Business Plan Creation & Financial Modeling', slug: 'business-plan' },
      { wp: 'a5', no: '1.5', title: 'Recruitment & Administrative Structuring', slug: 'recruitment-administration' },
      { wp: 'a6', no: '1.6', title: 'Sales & Marketing Team Building & Recruitment', slug: 'team-building' },
      { wp: 'a7', no: '1.7', title: 'Legal Setup & Product Registration', slug: 'legal-setup' },
      { wp: 'a8', no: '1.8', title: 'Logistics & Supply Chain Setup', slug: 'logistics-supply-chain' },
    ],
  },
  {
    wp: 'b', no: '2', title: 'Performance Enhancement & Expansion', slug: 'performance-enhancement',
    children: [
      { wp: 'b1', no: '2.1', title: 'Business Analysis & Productivity Audit', slug: 'productivity-audit' },
      { wp: 'b2', no: '2.2', title: 'Marketing Plan Review & Enhancement', slug: 'marketing-plan-review' },
      { wp: 'b3', no: '2.3', title: 'HR Structure & Workforce Optimization', slug: 'hr-workforce' },
      {
        wp: 'b4', no: '2.4', title: 'Sales & Marketing Team Performance Optimization', slug: 'team-performance',
        children: [
          { wp: 'b41', no: '2.4.1', title: 'Team Performance Diagnostics', slug: 'diagnostics' },
          { wp: 'b42', no: '2.4.2', title: 'Performance Management System Design', slug: 'performance-management-system' },
          { wp: 'b43', no: '2.4.3', title: 'Performance Monitoring & KPIs', slug: 'monitoring-kpis' },
          {
            wp: 'b44', no: '2.4.4', title: 'Coaching & Counseling Programs', slug: 'coaching',
            children: [
              { wp: 'b441', no: '2.4.4.a', title: 'For Sales Representatives', slug: 'sales-representatives' },
              { wp: 'b442', no: '2.4.4.b', title: 'For Field Sales Managers', slug: 'field-sales-managers' },
              { wp: 'b443', no: '2.4.4.c', title: 'For Marketing Managers', slug: 'marketing-managers' },
            ],
          },
        ],
      },
      { wp: 'b5', no: '2.5', title: 'Legal & Logistics Efficiency Review', slug: 'legal-logistics-review' },
    ],
  },
  {
    wp: 'c', no: '3', title: 'Training Services', slug: 'training',
    children: [
      { wp: 'c1', no: '3.1', title: 'Basic Technical Training', slug: 'basic-technical' },
      { wp: 'c2', no: '3.2', title: 'Product Training', slug: 'product' },
      {
        wp: 'c3', no: '3.3', title: 'Sales Techniques Training', slug: 'sales-techniques',
        children: [
          { wp: 'c31', no: '3.3.1', title: 'Beginner Level', slug: 'beginner' },
          { wp: 'c32', no: '3.3.2', title: 'Intermediate Level', slug: 'intermediate' },
          { wp: 'c33', no: '3.3.3', title: 'Advanced Level', slug: 'advanced' },
        ],
      },
      {
        wp: 'c4', no: '3.4', title: 'Marketing Strategy & Execution Training', slug: 'marketing-strategy',
        children: [
          { wp: 'c41', no: '3.4.1', title: 'Beginner Level', slug: 'beginner' },
          { wp: 'c42', no: '3.4.2', title: 'Intermediate Level', slug: 'intermediate' },
          { wp: 'c43', no: '3.4.3', title: 'Advanced Level', slug: 'advanced' },
        ],
      },
      { wp: 'c5', no: '3.5', title: 'Leadership & Management Training', slug: 'leadership-management' },
      { wp: 'c6', no: '3.6', title: 'Coaching & Counseling Skills for Managers', slug: 'coaching-skills' },
      { wp: 'c7', no: '3.7', title: 'Train-the-Trainer Programs', slug: 'train-the-trainer' },
    ],
  },
  {
    wp: 'd', no: '4', title: 'Remote & On-Demand Support', slug: 'remote-support',
    children: [
      { wp: 'd1', no: '4.1', title: 'Virtual Office & Contact Center Setup', slug: 'virtual-office' },
      { wp: 'd2', no: '4.2', title: 'Remote Office & Operations Management', slug: 'remote-operations' },
      { wp: 'd3', no: '4.3', title: 'Remote Customer Engagement Solutions', slug: 'customer-engagement' },
      { wp: 'd4', no: '4.4', title: 'Other Tailored Remote Support Services', slug: 'tailored-support' },
    ],
  },
];

export interface FlatService extends Service {
  /** /services/ altındaki tam yol, ör. "new-business-setup/global-business-expansion" */
  path: string;
  /** Kökten bu düğüme kadar olan zincir (kendisi dahil) */
  trail: Service[];
}

/** Ağacı düz bir listeye çevirir; sayfa üretimi, yönlendirme ve breadcrumb için. */
export function flattenServices(nodes: Service[] = services, parents: Service[] = []): FlatService[] {
  return nodes.flatMap((node) => {
    const trail = [...parents, node];
    const path = trail.map((s) => s.slug).join('/');
    return [{ ...node, path, trail }, ...flattenServices(node.children ?? [], trail)];
  });
}

export const serviceHref = (path: string) => `/services/${path}/`;

/** Eski WordPress adresleri → yeni adresler (astro.config.mjs içinde kullanılır). */
export function legacyServiceRedirects(): Record<string, string> {
  const map: Record<string, string> = { '/menu': '/services/', '/our-services': '/services/' };
  for (const s of flattenServices()) map[`/menu-${s.wp}`] = serviceHref(s.path);
  return map;
}
