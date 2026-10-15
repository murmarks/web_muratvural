// Fotoğrafların gerçek piksel boyutlarını ve içerik özetini src/data/photo-sizes.json dosyasına yazar.
// Özet adrese ?v= olarak eklenir: fotoğraf değişince adresi de değişir, tarayıcı ya da sunucu eskisini göstermez.
// Fotoğraf eklenince ya da değişince çalıştırın: node scripts/photo-sizes.mjs
// Boyutlar, fotoğrafın asla çekildiğinden büyük gösterilmemesi ve sayfa yüklenirken yer kaplaması için kullanılır.
import { readFile, readdir, writeFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import { existsSync } from 'node:fs';
import { basename, dirname, join, relative } from 'node:path';
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
  const hash = createHash('sha1').update(await readFile(file));
  const thumb = join(dirname(file), 't', basename(file));
  if (existsSync(thumb)) hash.update(await readFile(thumb));
  sizes[relative(root, file).replace(/\.[^.]+$/, '')] = [width, height, hash.digest('hex').slice(0, 8)];
}
await writeFile(out, JSON.stringify(sizes, null, 0).replace(/\],/g, '],\n') + '\n');
console.log(`${Object.keys(sizes).length} fotoğraf → ${relative(process.cwd(), out)}`);
