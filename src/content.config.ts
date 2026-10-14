import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';

/**
 * Hizmet sayfalarının metinleri: src/content/services/<dil>/<wp>.json
 * Eski sitelerin WordPress dışa aktarımlarından çıkarıldı (tools: /mnt/project-files/icerik/araclar/extract.py).
 * Satır içi HTML yalnızca <b>, <i> ve <br>.
 */
const block = z.object({
  /** p paragraf · h2/h3/h4 başlık · ul/ol liste · q alıntı · img fotoğraf */
  t: z.enum(['p', 'h2', 'h3', 'h4', 'ul', 'ol', 'q', 'img']),
  x: z.string().optional(),
  items: z.array(z.string()).optional(),
  start: z.number().optional(),
  /** img: public/photos/services/ içindeki dosya adı */
  n: z.string().optional(),
  alt: z.string().optional(),
  cap: z.string().optional(),
});

const services = defineCollection({
  loader: glob({ pattern: '*/*.json', base: './src/content/services' }),
  schema: z.object({ wp: z.string(), title: z.string(), blocks: z.array(block) }),
});

export const collections = { services };
