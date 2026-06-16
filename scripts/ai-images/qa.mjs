// Auto-QA pentru output-urile AI: semnaleaza automat cazurile suspecte ca sa fie
// verificate tintit (nu inlocuieste ochiul uman — doar le scoate in fata).
//
// Flag-uri:
//   aspect  — dimensiuni ≠ 1:1
//   res     — rezolutie anormal de mica
//   empty   — output aproape uniform (posibil gol)
//   bg      — fundal non-neutru (colturi departe de near-white)
//   color   — deviatie mare de culoare in centru fata de original (posibil schimbare culoare)

import sharp from 'sharp';

async function regionMeanRGB(file, box) {
  const s = await sharp(file).extract(box).stats();
  const [r, g, b] = s.channels;
  return { r: r.mean, g: g.mean, b: b.mean };
}

function brightness({ r, g, b }) { return (r + g + b) / 3; }
function saturation({ r, g, b }) {
  const max = Math.max(r, g, b), min = Math.min(r, g, b);
  return max <= 0 ? 0 : (max - min) / max;
}

async function cornerStats(file, w, h) {
  const b = Math.max(16, Math.floor(Math.min(w, h) * 0.06));
  const boxes = [
    { left: 0, top: 0, width: b, height: b },
    { left: w - b, top: 0, width: b, height: b },
    { left: 0, top: h - b, width: b, height: b },
    { left: w - b, top: h - b, width: b, height: b },
  ];
  let minBrightness = Infinity, maxSat = 0;
  for (const box of boxes) {
    const m = await regionMeanRGB(file, box);
    minBrightness = Math.min(minBrightness, brightness(m));
    maxSat = Math.max(maxSat, saturation(m));
  }
  return { minBrightness, maxSat };
}

async function centralMeanRGB(file) {
  const meta = await sharp(file).metadata();
  const w = meta.width, h = meta.height;
  if (!w || !h) return null;
  const box = {
    left: Math.floor(w * 0.25), top: Math.floor(h * 0.25),
    width: Math.max(1, Math.floor(w * 0.5)), height: Math.max(1, Math.floor(h * 0.5)),
  };
  return regionMeanRGB(file, box);
}

export async function analyze(aiPath, originalPath) {
  const flags = [];
  let width = null, height = null;
  try {
    const meta = await sharp(aiPath).metadata();
    width = meta.width; height = meta.height;

    if (width && height) {
      const ratio = width / height;
      if (Math.abs(ratio - 1) > 0.02) flags.push({ code: 'aspect', msg: `aspect ${width}×${height} ≠ 1:1` });
      if (Math.min(width, height) < 1200) flags.push({ code: 'res', msg: `rezolutie mica ${width}×${height}` });
    }

    const stats = await sharp(aiPath).stats();
    const meanStdev = stats.channels.slice(0, 3).reduce((s, c) => s + c.stdev, 0) / 3;
    if (meanStdev < 8) flags.push({ code: 'empty', msg: `aproape uniform (stdev ${meanStdev.toFixed(1)})` });

    if (width && height) {
      // Pragurile sunt calibrate ca sa semnaleze DOAR outlieri reali: fundalul-studio
      // are intentionat un falloff subtil spre colturi (lum ~190-210, tinta usoara) — normal.
      const c = await cornerStats(aiPath, width, height);
      if (c.minBrightness < 150 || c.maxSat > 0.22) {
        flags.push({ code: 'bg', msg: `fundal non-neutru (lum ${Math.round(c.minBrightness)}, sat ${c.maxSat.toFixed(2)})` });
      }
    }

    if (originalPath) {
      // Heuristic grosier: centrul originalului include watermark/fundal verde, deci Δ are zgomot.
      // Prag mare → doar schimbari clare de culoare.
      const [a, o] = await Promise.all([centralMeanRGB(aiPath), centralMeanRGB(originalPath)]);
      if (a && o) {
        const dist = Math.sqrt((a.r - o.r) ** 2 + (a.g - o.g) ** 2 + (a.b - o.b) ** 2);
        if (dist > 110) flags.push({ code: 'color', msg: `posibila schimbare culoare (Δ ${Math.round(dist)})` });
      }
    }
  } catch (e) {
    flags.push({ code: 'error', msg: `QA error: ${e.message}` });
  }
  return { width, height, flags };
}

// pool de concurenta peste o lista de task-uri (functii care intorc Promise)
export async function pool(items, worker, concurrency = 8) {
  const results = new Array(items.length);
  let i = 0;
  async function run() {
    while (i < items.length) {
      const idx = i++;
      results[idx] = await worker(items[idx], idx);
    }
  }
  await Promise.all(Array.from({ length: Math.min(concurrency, items.length) }, run));
  return results;
}
