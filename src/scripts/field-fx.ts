/**
 * Sahadan fotoğraflarına efekt: düzen aynı kalır, fotoğraf WebGL ile çizilir.
 * Kaydırma hangi fotoğrafın gösterileceğini seçer; iki fotoğraf arasındaki geçiş ise kendi hızında oynar
 * (sahibinin isteği: hızlı kaydırınca efekt bir kırpışma gibi kaçmasın). Zamanlama home.ts'de.
 *
 * Seçenekler (viewer'daki data-fx):
 *  - tul:    yeni fotoğraf tül gibi dalgalanarak iner, önce saydamdır, sonra düzleşip yerine oturur
 *  - isik:   çapraz bir ışık bandı geçer, arkasında yeni fotoğraf kalır; kenarda koyu paspartu ve çift çizgi
 *  - panjur: dikey dilimler sırayla döner; ön yüzde eski, arka yüzde yeni fotoğraf
 *  - odak:   fotoğraf ışıklı ve flu gelir, netleşip kendi rengine oturur; köşeler yuvarlak, kenarı hafif kararmış
 */

const MODES = { tul: 0, isik: 1, panjur: 2, odak: 3 } as const;
export type FxMode = keyof typeof MODES;

const VERT = `
attribute vec2 aQ;
uniform vec4 uQuad;
uniform vec2 uView;
uniform vec4 uPhoto;
varying vec2 vP;
void main() {
  vec2 p = uQuad.xy + aQ * uQuad.zw;
  vP = p - uPhoto.xy;
  vec2 c = p / uView * 2.0 - 1.0;
  gl_Position = vec4(c.x, -c.y, 0.0, 1.0);
}`;

const FRAG = `
precision highp float;
varying vec2 vP;
uniform sampler2D uTex;
uniform vec4 uPhoto;
uniform float uE;     // fotoğrafın yerinde olma oranı: 0 yok, 1 yerinde
uniform float uDir;   // 1 geliyor, -1 gidiyor
uniform float uMode;
uniform float uPh;    // kaydırmayla ilerleyen evre
uniform float uLo;    // düşük çözünürlüklü arşiv karesi
uniform float uZ;     // fotoğraf yerinde dururken kaydırmayla çok hafif yaklaşır (üstten sabit, baş kesilmez)

float hash(vec2 p) { return fract(sin(dot(p, vec2(12.9898, 78.233))) * 43758.5453); }
// yaklaşma yalnız resmin içinde olur, kadraj ve maskeler yerinde kalır
vec3 tex(vec2 uv) { return texture2D(uTex, clamp(vec2(0.5 + (uv.x - 0.5) / uZ, 0.2 + (uv.y - 0.2) / uZ), 0.0, 1.0)).rgb; }
// kenarları piksel cinsinden yumuşatılmış dikdörtgen
float box(vec2 uv, vec2 f) {
  vec2 a = smoothstep(vec2(0.0), f, uv) * smoothstep(vec2(0.0), f, 1.0 - uv);
  return a.x * a.y;
}
// köşesi yuvarlatılmış dikdörtgen (px)
float rbox(vec2 p, vec2 s, float r) {
  vec2 q = abs(p - s * 0.5) - (s * 0.5 - r);
  float d = length(max(q, 0.0)) + min(max(q.x, q.y), 0.0) - r;
  return 1.0 - smoothstep(-0.75, 0.75, d);
}

void main() {
  vec2 S = uPhoto.zw;
  vec2 uv0 = vP / S;
  vec2 uv = uv0;
  vec2 px = 1.0 / S;
  float k = 1.0 - uE;
  vec3 c;
  float alpha;

  // geçişin ilerleyişi: gelen ve giden fotoğraf aynı değeri görür
  float tt = uDir > 0.0 ? uE : 1.0 - uE;

  if (uMode < 0.5) {
    // tül: yeni fotoğraf ince bir tül gibi yukarıdan iner; dalgalı ön kenarın hemen arkasında saydam ve kıvrımlıdır,
    // geride kalan kısım düzleşip yerine oturur. Eski fotoğraf altta kalır, üzerine tülün gölgesi düşer.
    float f = mix(-0.12, 1.4, tt);
    float e = 0.07 * sin(uv.x * 7.0 + uPh * 1.3) + 0.035 * sin(uv.x * 17.0 - uPh * 2.1);
    float d = f + e - uv0.y;
    if (uDir > 0.0) {
      float amp = exp(-max(d, 0.0) * 3.5) * (1.0 - smoothstep(0.8, 1.0, tt));
      float ph = (uv0.y - f) * 26.0 + uv0.x * 3.0 + uPh * 3.0;
      float h = sin(ph), dh = cos(ph);
      uv.y += h * 0.02 * amp - amp * 0.03;
      uv.x += sin(ph * 0.7 + uv0.y * 5.0) * 0.012 * amp;
      c = tex(uv);
      c *= 1.0 + dh * 0.3 * amp;
      c += (h * 0.5 + 0.5) * amp * 0.12 * vec3(1.0, 0.95, 0.86);
      c += exp(-pow((d - 0.015) / 0.022, 2.0)) * 0.22 * vec3(1.0, 0.95, 0.86);
      alpha = smoothstep(0.0, 0.025, d) * mix(0.42, 1.0, smoothstep(0.0, 0.24, d)) * box(uv, px);
    } else {
      c = tex(uv) * (1.0 - 0.3 * smoothstep(-0.14, 0.02, d));
      alpha = (1.0 - smoothstep(0.2, 0.3, d)) * box(uv, px);
    }
  } else if (uMode < 1.5) {
    // ışık: çapraz bant; gelen fotoğraf bandın gerisinde, giden önünde kalır
    float m = uDir > 0.0 ? uE : 1.0 - uE;
    float q = uv.x * 0.72 + (1.0 - uv.y) * 0.28;
    float b = mix(-0.14, 1.14, m);
    float s = 0.045;
    float vis = uDir > 0.0 ? 1.0 - smoothstep(b - s, b, q) : smoothstep(b, b + s, q);
    float on = sin(3.14159 * clamp(m, 0.0, 1.0));
    c = tex(uv);
    if (uDir > 0.0) {
      float near = smoothstep(0.22, 0.0, b - q) * on;
      c = mix(c, c * 1.22 + 0.05, near * 0.7);
      c += exp(-pow((q - b) / 0.022, 2.0)) * on * vec3(1.0, 0.8, 0.42) * 1.1;
      vis = max(vis, exp(-pow((q - b) / 0.022, 2.0)) * on);
    }
    alpha = vis * box(uv, px);
  } else if (uMode < 2.5) {
    // panjur: dokuz dikey dilim soldan sağa sırayla döner; dilimin ön yüzü eski, arka yüzü yeni fotoğraf
    float N = 9.0;
    float x = clamp(uv.x, 0.0, 0.99999);
    float j = floor(x * N);
    float lx = fract(x * N);
    float th = clamp(tt * 1.6 - j / N * 0.6, 0.0, 1.0);
    th = th * th * (3.0 - 2.0 * th) * 3.14159;
    float w = uDir > 0.0 ? -cos(th) : cos(th);
    float u = 0.5 + (lx - 0.5) / max(w, 0.001);
    // dönen dilimde uzak kenar kısalır
    float sy = 1.0 + (u - 0.5) * sin(th) * 0.16 * uDir;
    vec2 suv = vec2((j + u) / N, 0.5 + (uv.y - 0.5) * sy);
    c = tex(suv) * mix(0.35, 1.0, w);
    c += smoothstep(0.38, 0.5, abs(u - 0.5)) * sin(th) * vec3(1.0, 0.82, 0.45) * 0.35;
    float inside = step(abs(lx - 0.5), w * 0.5) * step(0.0, suv.y) * step(suv.y, 1.0);
    alpha = inside * box(uv, px) * step(0.002, w);
  } else {
    // odak: ışıklı ve flu gelir, netleşir; giderken kararıp bulanıklaşır
    vec2 cc = uv0 - 0.5;
    uv = 0.5 + cc * (1.0 - k * 0.07 * uDir);
    float r = k * 0.018;
    c = tex(uv) * 0.2;
    for (int i = 0; i < 8; i++) {
      float an = float(i) * 0.785398;
      c += tex(uv + vec2(cos(an), sin(an) * S.x / S.y) * r) * 0.1;
    }
    if (uDir > 0.0) c = mix(c, vec3(0.97, 0.93, 0.85), k * 0.85);
    else c *= 1.0 - k * 0.9;
    c *= 1.0 - 0.3 * pow(length(cc * vec2(1.0, 1.15)) * 1.45, 2.4);
    float leak = exp(-pow((uv0.x - 0.04 - 0.05 * sin(uPh * 0.7)) / 0.13, 2.0)) * (0.07 + 0.3 * k);
    c += leak * vec3(1.0, 0.55, 0.16);
    alpha = rbox(vP, S, 7.0) * smoothstep(0.0, 0.4, uE);
  }

  // film greni: kaydırınca değişir, durunca durur
  c += (hash(floor(vP) + floor(uPh * 12.0)) - 0.5) * (uLo > 0.5 ? 0.09 : 0.035);
  gl_FragColor = vec4(clamp(c, 0.0, 1.0) * alpha, alpha);
}`;

export interface View {
  lo: number;
  hi: number;
  t: number;
  enter: number;
  sp: number;
}

interface Item {
  el: HTMLElement;
  place: number;
  k: number;
  lo: boolean;
  tex: WebGLTexture | null;
  rect: { x: number; y: number; w: number; h: number };
}

/** WebGL yoksa null döner; o zaman bölüm eskisi gibi çalışır */
export function fieldFx(viewer: HTMLElement, mode: FxMode) {
  const pics = viewer.querySelector<HTMLElement>('[data-pics]')!;
  const figs = [...pics.querySelectorAll<HTMLElement>('.place-fig')];
  const caps = figs.map((f) => f.querySelector<HTMLElement>('.capbar')!);
  const items: Item[] = [];
  figs.forEach((f, i) =>
    f.querySelectorAll<HTMLElement>('.shots > .ph').forEach((el, k) =>
      items.push({ el, place: i, k, lo: el.classList.contains('lo'), tex: null, rect: { x: 0, y: 0, w: 0, h: 0 } }),
    ),
  );
  const cv = document.createElement('canvas');
  cv.className = 'fx-cv';
  cv.setAttribute('aria-hidden', 'true');
  const gl = cv.getContext('webgl', { premultipliedAlpha: true, alpha: true, antialias: false });
  if (!gl || !items.length) return null;

  const sh = (type: number, src: string) => {
    const s = gl.createShader(type)!;
    gl.shaderSource(s, src);
    gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(s) ?? 'shader');
    return s;
  };
  const prog = gl.createProgram()!;
  try {
    gl.attachShader(prog, sh(gl.VERTEX_SHADER, VERT));
    gl.attachShader(prog, sh(gl.FRAGMENT_SHADER, FRAG));
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return null;
  } catch {
    return null;
  }
  gl.useProgram(prog);
  const buf = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buf);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0, 0, 1, 0, 0, 1, 1, 1]), gl.STATIC_DRAW);
  const aQ = gl.getAttribLocation(prog, 'aQ');
  gl.enableVertexAttribArray(aQ);
  gl.vertexAttribPointer(aQ, 2, gl.FLOAT, false, 0, 0);
  const U = Object.fromEntries(['uQuad', 'uView', 'uPhoto', 'uTex', 'uE', 'uDir', 'uMode', 'uPh', 'uLo', 'uZ'].map((n) => [n, gl.getUniformLocation(prog, n)]));
  gl.uniform1f(U.uMode, MODES[mode]);
  gl.uniform1i(U.uTex, 0);
  gl.enable(gl.BLEND);
  gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);

  // çerçeve: fotoğrafın altında (gölge, paspartu) ve üstünde (çizgiler); iki fotoğraf arasında boyu kaydırmayla değişir
  const under = document.createElement('div');
  const over = document.createElement('div');
  under.className = 'fx-under';
  over.className = 'fx-over';
  pics.prepend(under);
  pics.append(cv, over);
  viewer.dataset.fxOn = mode;

  const M = 56; // dalga ve ışık fotoğrafın dışına taşabilsin
  let W = 0, H = 0, dpr = 1;
  const measure = () => {
    const pr = pics.getBoundingClientRect();
    W = pr.width + 2 * M;
    H = pr.height + 2 * M;
    dpr = Math.min(2, devicePixelRatio || 1);
    cv.width = Math.round(W * dpr);
    cv.height = Math.round(H * dpr);
    items.forEach((it) => {
      const r = it.el.getBoundingClientRect();
      it.rect = { x: r.left - pr.left + M, y: r.top - pr.top + M, w: r.width, h: r.height };
    });
  };

  let last: View = { lo: 0, hi: 0, t: 0, enter: 1, sp: 0 };
  // resim çizilemezse (başka kaynaktan gelen dosya vb.) efekt kapanır, fotoğraflar her zamanki gibi görünür
  const fail = () => {
    delete viewer.dataset.fxOn;
    cv.remove();
    under.remove();
    over.remove();
  };
  const load = (it: Item) => {
    const img = new Image();
    img.decoding = 'async';
    img.src = it.el.querySelector('img')!.currentSrc || it.el.querySelector('img')!.src;
    img.decode().then(() => {
      const t = gl.createTexture();
      gl.bindTexture(gl.TEXTURE_2D, t);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
      gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, false);
      try {
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
      } catch {
        return fail();
      }
      it.tex = t;
      render(last);
    }, fail);
  };
  items.forEach(load);

  const draw = (it: Item, e: number, dir: number, ph: number, z = 1) => {
    if (!it.tex || e <= 0.001) return;
    const r = it.rect;
    gl.bindTexture(gl.TEXTURE_2D, it.tex);
    gl.uniform4f(U.uQuad, r.x - M, r.y - M, r.w + 2 * M, r.h + 2 * M);
    gl.uniform4f(U.uPhoto, r.x, r.y, r.w, r.h);
    gl.uniform1f(U.uE, e);
    gl.uniform1f(U.uDir, dir);
    gl.uniform1f(U.uPh, ph);
    gl.uniform1f(U.uLo, it.lo ? 1 : 0);
    gl.uniform1f(U.uZ, z);
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
  };
  const smooth = (x: number) => { const t = Math.min(1, Math.max(0, x)); return t * t * (3 - 2 * t); };
  const placeBox = (el: HTMLElement, a: Item['rect'], b: Item['rect'] | null, t: number, op: number) => {
    const m = (p: number, q: number) => p + (q - p) * t;
    const r = b ? { x: m(a.x, b.x), y: m(a.y, b.y), w: m(a.w, b.w), h: m(a.h, b.h) } : a;
    el.style.cssText = `left:${r.x - M}px;top:${r.y - M}px;width:${r.w}px;height:${r.h}px;opacity:${op}`;
  };

  /**
   * v.lo / v.hi: geçişin iki ucundaki fotoğraf (sıra numarası), v.t: geçişin ilerleyişi (0 lo, 1 hi yerinde).
   * Geçiş zamanla oynar (home.ts), kaydırma hızına bağlı değildir. v.enter: bölüm ekrana girerken ilk fotoğrafın gelişi.
   * v.sp: kaydırma konumu (fotoğraf birimiyle); fotoğraf yerinde dururken kaydırdıkça çok hafif yaklaşır.
   */
  const render = (v: View) => {
    last = { ...v };
    const A = items[v.lo], B = v.hi !== v.lo ? items[v.hi] : null;
    const t = B ? v.t : 0;
    const ph = v.sp * 3 + t * 2;
    const z = (i: number) => 1 + 0.03 * Math.min(1, Math.max(0, v.sp - i));

    gl.viewport(0, 0, cv.width, cv.height);
    gl.clearColor(0, 0, 0, 0);
    gl.clear(gl.COLOR_BUFFER_BIT);
    gl.uniform2f(U.uView, W, H);
    if (B) {
      draw(A, 1 - t, -1, ph, z(v.lo));
      draw(B, t, 1, ph, z(v.hi));
    } else draw(A, v.lo === 0 ? v.enter : 1, 1, ph, z(v.lo));

    const frameOp = !B && v.lo === 0 ? smooth(v.enter * 1.6) : 1;
    placeBox(under, A.rect, B?.rect ?? null, t, frameOp);
    placeBox(over, A.rect, B?.rect ?? null, t, frameOp);

    // yazılar ve küçük resimler: aynı yerin fotoğrafıysa yazı yerinde kalır, yer değişiyorsa birbirine geçer
    const pa = A.place, pb = B ? B.place : pa;
    figs.forEach((f, i) => {
      const show = i === pa || i === pb;
      f.style.visibility = show ? 'visible' : 'hidden';
      f.style.opacity = '1';
      caps[i].style.opacity = String(i === pa && i === pb ? 1 : i === pa ? 1 - smooth(t * 2) : i === pb ? smooth(t * 2 - 1) : 0);
    });
    return { place: t > 0.5 ? pb : pa, photo: t > 0.5 && B ? B.k : A.k, from: pa, to: pb, t };
  };

  measure();
  const ro = new ResizeObserver(() => { measure(); render(last); });
  ro.observe(pics);
  return {
    render,
    measure: () => { measure(); render(last); },
    destroy() {
      ro.disconnect();
      cv.remove();
      under.remove();
      over.remove();
      delete viewer.dataset.fxOn;
      figs.forEach((f, i) => { f.style.cssText = ''; caps[i].style.opacity = ''; });
    },
  };
}
