import type { Variant } from './common';
import { manset } from './manset';
import { serit } from './serit';
import { rota } from './rota';
import { baski } from './baski';

/** "Sahada, salonun içinde" bölümünün seçenekleri: sayfadaki [data-field] hangisiyse o çalışır */
export const fieldVariants: Record<string, Variant> = { manset, serit, rota, baski };
