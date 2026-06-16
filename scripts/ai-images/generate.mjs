#!/usr/bin/env node
// Trece pozele de produs scrape-uite prin Nano Banana (Gemini image), transformandu-le
// in studio shots premium pastrand identitatea exacta a produsului.
//
// Sursa (intacta):  storage/scrape/images/<slug>/<file>
// Staging (output): storage/scrape/images-ai/<slug>/<file>   (oglindeste structura)
// Manifest:         storage/scrape/images-ai/manifest.json
//
// Idempotent / reluabil: sare peste imaginile cu output deja in staging (fara --force).
// Vezi README.md pentru flag-uri si flux.
//
// API (verificat in docs https://ai.google.dev/gemini-api/docs/image-generation + @google/genai):
//   image-to-image = generateContent cu contents:[{text}, {inlineData:{mimeType,data(base64)}}]
//   config.imageConfig = { aspectRatio: "1:1", imageSize: "1K"|"2K"|"4K" }
//   raspunsul vine ca part.inlineData.data (base64) -> salvam PNG.
//   Toate ies cu SynthID (watermark invizibil, nu se poate dezactiva — ok pentru produse).

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { GoogleGenAI } from '@google/genai';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..', '..'); // radacina proiectului Laravel

const SRC_DIR = path.join(ROOT, 'storage', 'scrape', 'images');
let OUT_DIR = path.join(ROOT, 'storage', 'scrape', 'images-ai'); // override-abil cu --out
let MANIFEST = path.join(OUT_DIR, 'manifest.json');
const PROMPT_FILE = path.join(__dirname, 'prompt.txt');

const IMAGE_EXTS = new Set(['.jpg', '.jpeg', '.png', '.webp']);
const MIME = { '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.png': 'image/png', '.webp': 'image/webp' };

// ---------- args ----------
function parseArgs(argv) {
  const a = {
    limit: null,
    only: null,
    model: 'gemini-3-pro-image', // Nano Banana Pro (fidelitate maxima). Alt.: gemini-3.1-flash-image
    force: false,
    all: false,
    concurrency: 3, // mic, ca sa respectam rate limit-ul
    aspect: '1:1',
    size: '2K', // "1K"|"2K"|"4K"
    out: null, // dir staging alternativ (relativ la radacina), pt. comparatii intre modele
    retries: 4,
    costPerImage: null, // USD/imagine, optional, pentru estimare; vezi pricing in docs
    help: false,
  };
  for (let i = 0; i < argv.length; i++) {
    const t = argv[i];
    const next = () => argv[++i];
    switch (t) {
      case '--limit': a.limit = parseInt(next(), 10); break;
      case '--only': a.only = next(); break;
      case '--model': a.model = next(); break;
      case '--force': a.force = true; break;
      case '--all': a.all = true; break; // confirmare explicita pentru batch complet
      case '--concurrency': a.concurrency = Math.max(1, parseInt(next(), 10)); break;
      case '--aspect': a.aspect = next(); break;
      case '--size': a.size = next(); break;
      case '--out': a.out = next(); break;
      case '--retries': a.retries = parseInt(next(), 10); break;
      case '--cost-per-image': a.costPerImage = parseFloat(next()); break;
      case '-h': case '--help': a.help = true; break;
      default: console.error(`Flag necunoscut: ${t}`); process.exit(2);
    }
  }
  return a;
}

const HELP = `
Genereaza poze AI (studio) din pozele scrape-uite, in staging.

  node generate.mjs [optiuni]

Optiuni:
  --limit N            Proceseaza maxim N imagini (pentru testare).
  --only <slug>        Doar produsul cu acest slug.
  --model <id>         Default gemini-3-pro-image (Pro). Alt.: gemini-3.1-flash-image (rapid).
  --size 1K|2K|4K      Rezolutie output (default 2K).
  --aspect 1:1         Aspect ratio (default 1:1).
  --out <dir>          Dir staging alternativ (relativ la radacina), ex. pt. comparatii intre modele.
  --concurrency N      Cereri concurente (default 3).
  --retries N          Reincercari pe 429/5xx (default 4).
  --force              Re-genereaza chiar daca exista deja output in staging.
  --all                Necesar ca sa pornesti BATCH-UL COMPLET (cand lipsesc --limit/--only).
  --cost-per-image U   USD/imagine pentru estimare cost (optional; vezi pricing in docs).
  -h, --help           Ajutor.

Cheia GEMINI_API_KEY se citeste din .env (radacina proiectului) sau din environment.
`;

// ---------- .env ----------
function loadApiKey() {
  if (process.env.GEMINI_API_KEY) return process.env.GEMINI_API_KEY;
  const envPath = path.join(ROOT, '.env');
  if (fs.existsSync(envPath)) {
    const txt = fs.readFileSync(envPath, 'utf8');
    for (const line of txt.split(/\r?\n/)) {
      const m = line.match(/^\s*GEMINI_API_KEY\s*=\s*(.*)\s*$/);
      if (m) {
        let v = m[1].trim();
        if ((v.startsWith('"') && v.endsWith('"')) || (v.startsWith("'") && v.endsWith("'"))) {
          v = v.slice(1, -1);
        }
        if (v) return v;
      }
    }
  }
  return null;
}

// ---------- manifest ----------
function loadManifest() {
  if (fs.existsSync(MANIFEST)) {
    try { return JSON.parse(fs.readFileSync(MANIFEST, 'utf8')); }
    catch { console.warn('manifest.json corupt, pornesc de la zero'); }
  }
  return { updatedAt: null, items: {} };
}
function saveManifest(m) {
  m.updatedAt = new Date().toISOString();
  fs.mkdirSync(OUT_DIR, { recursive: true });
  fs.writeFileSync(MANIFEST, JSON.stringify(m, null, 2));
}

// ---------- enumerare imagini sursa ----------
function listSources(onlySlug) {
  if (!fs.existsSync(SRC_DIR)) {
    console.error(`Nu gasesc directorul sursa: ${SRC_DIR}`);
    console.error('Ruleaza scriptul din checkout-ul care are storage/scrape/ (pozele scrape-uite).');
    process.exit(1);
  }
  // --only acceptă unul sau mai multe slug-uri separate prin virgulă.
  const onlySet = onlySlug
    ? new Set(String(onlySlug).split(',').map((s) => s.trim()).filter(Boolean))
    : null;
  const out = [];
  const slugs = fs.readdirSync(SRC_DIR, { withFileTypes: true })
    .filter((d) => d.isDirectory())
    .map((d) => d.name)
    .sort();
  for (const slug of slugs) {
    if (onlySet && !onlySet.has(slug)) continue;
    const dir = path.join(SRC_DIR, slug);
    const files = fs.readdirSync(dir)
      .filter((f) => IMAGE_EXTS.has(path.extname(f).toLowerCase()))
      .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
    for (const file of files) {
      out.push({ slug, file, rel: `images/${slug}/${file}`, srcPath: path.join(dir, file) });
    }
  }
  return out;
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

function isRetryable(err) {
  const s = err?.status ?? err?.code ?? err?.response?.status;
  if (s === 429 || (typeof s === 'number' && s >= 500)) return true;
  const msg = String(err?.message || '').toLowerCase();
  return msg.includes('429') || msg.includes('rate') || msg.includes('quota') ||
    msg.includes('overloaded') || msg.includes('unavailable') || msg.includes('internal') ||
    msg.includes('timeout') || msg.includes('econnreset') || msg.includes('etimedout') ||
    msg.includes('fetch failed') || msg.includes('network') || msg.includes('socket') ||
    msg.includes('enotfound') || msg.includes('eai_again');
}

// extrage prima imagine (inlineData) din raspuns
function extractImage(response) {
  const parts = response?.candidates?.[0]?.content?.parts ?? [];
  for (const p of parts) {
    if (p.inlineData?.data) {
      return { data: p.inlineData.data, mimeType: p.inlineData.mimeType || 'image/png' };
    }
  }
  return null;
}
function refusalText(response) {
  const parts = response?.candidates?.[0]?.content?.parts ?? [];
  return parts.map((p) => p.text).filter(Boolean).join(' ').trim();
}

async function generateOne(ai, args, item) {
  const ext = path.extname(item.file).toLowerCase();
  const mimeType = MIME[ext] || 'image/jpeg';
  const base64 = fs.readFileSync(item.srcPath).toString('base64');

  let lastErr;
  for (let attempt = 0; attempt <= args.retries; attempt++) {
    try {
      const response = await ai.models.generateContent({
        model: args.model,
        contents: [
          { text: args.prompt },
          { inlineData: { mimeType, data: base64 } },
        ],
        config: {
          imageConfig: { aspectRatio: args.aspect, imageSize: args.size },
        },
      });

      const img = extractImage(response);
      if (!img) {
        // refuz / doar text — nu reincerca, marcheaza failed si treci mai departe
        const why = refusalText(response) || 'raspuns fara imagine';
        const e = new Error(`fara imagine: ${why.slice(0, 200)}`);
        e.nonRetryable = true;
        throw e;
      }

      const outDir = path.join(OUT_DIR, item.slug);
      fs.mkdirSync(outDir, { recursive: true });
      // pastram acelasi nume de fisier ca sursa (path-ul din DB ramane valid la promovare),
      // chiar daca bytes-ii sunt PNG.
      const outPath = path.join(outDir, item.file);
      fs.writeFileSync(outPath, Buffer.from(img.data, 'base64'));
      return { ok: true, bytes: Buffer.byteLength(img.data, 'base64'), mimeType: img.mimeType };
    } catch (err) {
      lastErr = err;
      if (err.nonRetryable || !isRetryable(err) || attempt === args.retries) break;
      const backoff = Math.min(30000, 1000 * 2 ** attempt) + Math.floor(Math.random() * 500);
      console.warn(`  retry ${item.rel} (incercarea ${attempt + 1}) dupa ${backoff}ms: ${err.message}`);
      await sleep(backoff);
    }
  }
  return { ok: false, error: String(lastErr?.message || lastErr) };
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  if (args.help) { console.log(HELP); return; }

  if (args.out) {
    OUT_DIR = path.resolve(ROOT, args.out);
    MANIFEST = path.join(OUT_DIR, 'manifest.json');
  }

  args.prompt = fs.readFileSync(PROMPT_FILE, 'utf8').trim();

  const apiKey = loadApiKey();
  if (!apiKey) {
    console.error('Lipseste GEMINI_API_KEY (in .env la radacina proiectului sau in environment).');
    process.exit(1);
  }

  let sources = listSources(args.only);

  const manifest = loadManifest();

  // filtreaza ce ramane de facut (idempotent)
  const todo = [];
  let skipped = 0;
  for (const s of sources) {
    const outPath = path.join(OUT_DIR, s.slug, s.file);
    const entry = manifest.items[s.rel];
    const alreadyDone = !args.force && fs.existsSync(outPath) && entry?.status === 'done';
    if (alreadyDone) { skipped++; continue; }
    todo.push(s);
  }

  // poarta pentru batch complet
  const isFullBatch = !args.limit && !args.only;
  if (isFullBatch && !args.all && todo.length > 0) {
    console.error(`\nAr fi de procesat ${todo.length} imagini (batch COMPLET).`);
    console.error('Refuz sa pornesc batch-ul complet fara confirmare. Optiuni:');
    console.error('  - testeaza intai:   node generate.mjs --limit 5');
    console.error('  - sau confirma:     node generate.mjs --all');
    process.exit(1);
  }

  const batch = args.limit ? todo.slice(0, args.limit) : todo;

  console.log(`Sursa:   ${SRC_DIR}`);
  console.log(`Staging: ${OUT_DIR}`);
  console.log(`Model:   ${args.model}   aspect=${args.aspect} size=${args.size} concurrency=${args.concurrency}`);
  console.log(`Total imagini sursa: ${sources.length} | deja in staging: ${skipped} | de procesat acum: ${batch.length}`);
  if (args.costPerImage) {
    console.log(`Estimare cost: ~${(batch.length * args.costPerImage).toFixed(2)} USD (la ${args.costPerImage}/imagine).`);
  } else {
    console.log('Estimare cost: seteaza --cost-per-image <USD> pentru un total (vezi pricing in docs).');
  }
  if (batch.length === 0) { console.log('Nimic de facut.'); return; }

  const ai = new GoogleGenAI({ apiKey });

  let done = 0, failed = 0, processed = 0;
  const queue = [...batch];

  async function worker(id) {
    while (queue.length) {
      const item = queue.shift();
      if (!item) break;
      processed++;
      const n = processed;
      process.stdout.write(`[${n}/${batch.length}] ${item.rel} … `);
      const res = await generateOne(ai, args, item);
      manifest.items[item.rel] = {
        status: res.ok ? 'done' : 'failed',
        model: args.model,
        aspect: args.aspect,
        size: args.size,
        timestamp: new Date().toISOString(),
        ...(res.ok ? { output: path.relative(ROOT, path.join(OUT_DIR, item.slug, item.file)), bytes: res.bytes } : { error: res.error }),
      };
      saveManifest(manifest); // salveaza dupa fiecare -> reluabil
      if (res.ok) { done++; console.log('ok'); }
      else { failed++; console.log(`ESEC: ${res.error}`); }
    }
  }

  const workers = Array.from({ length: Math.min(args.concurrency, batch.length) }, (_, i) => worker(i));
  await Promise.all(workers);

  console.log(`\nGata. done=${done} failed=${failed} (din ${batch.length}).`);
  console.log(`Manifest: ${MANIFEST}`);
  if (done > 0) console.log('Urmator: node review.mjs  -> storage/scrape/images-ai/review.html (poarta de review).');
  if (failed > 0) process.exitCode = 1;
}

main().catch((e) => { console.error(e); process.exit(1); });
