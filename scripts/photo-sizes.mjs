// Fotoğrafların gerçek piksel boyutlarını src/data/photo-sizes.json dosyasına yazar.
// Fotoğraf eklenince ya da değişince çalıştırın: node scripts/photo-sizes.mjs
// Boyutlar, fotoğrafın asla çekildiğinden büyük gösterilmemesi ve sayfa yüklenirken yer kaplaması için kullanılır.
import { readdir, writeFile } from 'node:fs/promises';
import { join, relative } from 'node:path';
import sharp from 'sharp';

const root = new URL('../public/photos/', import.meta.url).pathname;
const out = new URL('../src/data/photo-sizes.json', import.meta.url).pathname;

async function walk(dir) {
  const items = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const it of items) {
    // t/ klasörleri küçük önizlemeler; boyutları gerekmiyor
    if (it.isDirectory()) { if (it.name !== 't') files.push(...(await walk(join(dir, it.name)))); }
    else if (/\.(webp|png|jpe?g)$/i.test(it.name)) files.push(join(dir, it.name));
  }
  return files;
}

const sizes = {};
for (const file of (await walk(root)).sort()) {
  const { width, height } = await sharp(file).metadata();
  sizes[relative(root, file).replace(/\.[^.]+$/, '')] = [width, height];
}
await writeFile(out, JSON.stringify(sizes, null, 0).replace(/\],/g, '],\n') + '\n');
console.log(`${Object.keys(sizes).length} fotoğraf → ${relative(process.cwd(), out)}`);
