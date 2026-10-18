"""Sahadan fotoğraflarına ortak renk işlemi: farklı makinelerden, farklı ışıklardan gelen kareler
sayfanın tonunda tek bir seri gibi görünsün.

- Renk kayması kısmen düzeltilir (floresan mavisi, sarı ampul), tamamen değil: yerin havası kalır.
- Siyahlar sayfanın koyusuna, beyazlar sayfanın krem yazı rengine çekilir: beyaz duvarlar koyu sayfada parlamaz.
- Gölgeler sıcak, ışıklar krem; doygunluk biraz düşer, orta tonlara hafif kontrast.

Kaynak: public/photos/<ad>.webp ve t/<ad>.webp. Çıktı: public/photos/g/<ad>.webp ve g/t/<ad>.webp.
Çalıştırma: python3 scripts/grade-photos.py && node scripts/photo-sizes.mjs
"""
import os
import numpy as np
from PIL import Image

ROOT = os.path.join(os.path.dirname(__file__), '..', 'public', 'photos')
NAMES = ['kosovo-1', 'moldova', 'azerbaijan', 'georgia', 'croatia', 'bosnia', 'libya',
         'antalya-1', 'antalya-3', 'antalya-4', 'vietnam-1', 'vietnam-2', 'vietnam-3']

SHADOW = np.array([16, 13, 11]) / 255   # sıcak koyu, sayfanın zemininden bir ton açık
LIGHT = np.array([238, 231, 218]) / 255  # krem, sayfanın yazı rengine yakın
WB_AMOUNT = 0.6     # renk kaymasının ne kadarı düzeltilir
SAT = 0.78          # doygunluk
CONTRAST = 0.22     # orta tonlarda S eğrisi


def stats(a):
    """Seviye ve beyaz dengesi büyük kareden ölçülür; küçük resim de aynı ayarı alır."""
    lum = a @ [0.2126, 0.7152, 0.0722]
    lo, hi = np.percentile(lum, [0.8, 99.6])
    mid = (lum > np.percentile(lum, 20)) & (lum < np.percentile(lum, 85))
    mean = a[mid].mean(axis=0)
    gain = (mean.mean() / np.maximum(mean, 1e-3)) ** WB_AMOUNT
    return lo, hi, gain


def grade(a, lo, hi, gain):
    a = np.clip(a * gain, 0, 1)
    a = np.clip((a - lo) / max(hi - lo, 1e-3), 0, 1)
    lum = a @ [0.2126, 0.7152, 0.0722]
    a = lum[..., None] + (a - lum[..., None]) * SAT
    a = np.clip(a, 0, 1)
    a = a + CONTRAST * a * (1 - a) * (2 * a - 1)  # yumuşak S: orta tonlar ayrışır, uçlar sıkışmaz
    # gölgeler sıcak koyuya, ışıklar kreme: renk korunur, iki ucun tonu değişir
    return np.clip(SHADOW + a * (LIGHT - SHADOW), 0, 1)


def load(p):
    return np.asarray(Image.open(p).convert('RGB'), dtype=np.float64) / 255


def save(a, p, q):
    os.makedirs(os.path.dirname(p), exist_ok=True)
    Image.fromarray((a * 255 + 0.5).astype(np.uint8)).save(p, 'WEBP', quality=q, method=6)


for n in NAMES:
    big = load(os.path.join(ROOT, f'{n}.webp'))
    st = stats(big)
    save(grade(big, *st), os.path.join(ROOT, 'g', f'{n}.webp'), 86)
    save(grade(load(os.path.join(ROOT, 't', f'{n}.webp')), *st), os.path.join(ROOT, 'g', 't', f'{n}.webp'), 82)
    print(n)
