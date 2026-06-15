#!/usr/bin/env node
/**
 * Scraper catalog site vechi — mobilier-stradal.ro (OpenCart).
 *
 * Extrage TOT catalogul (produse + imagini full-res) și îl salvează local în
 * storage/scrape/. Doar scraping + salvare; fără DB, fără import Laravel.
 *
 * Rulare:  node scripts/scraper/scrape.mjs
 *
 * Output (storage/scrape/):
 *   products.json     — array obiecte produs + căi relative imagini descărcate
 *   categories.json   — categoriile sursă: nume, url, count așteptat vs găsit
 *   images/<cod>/<n>.<ext> — imaginile full-res, grupate per produs
 *   report.md         — raport count așteptat vs găsit, total, probleme, 404-uri
 */

import { load } from 'cheerio';
import { mkdir, writeFile, access, stat } from 'node:fs/promises';
import { constants as FS } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const REPO_ROOT = resolve(__dirname, '..', '..');
const OUT_DIR = join(REPO_ROOT, 'storage', 'scrape');
const IMAGES_DIR = join(OUT_DIR, 'images');

const BASE = 'https://mobilier-stradal.ro';
const UA =
  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 ' +
  '(KHTML, like Gecko) Chrome/124.0 Safari/537.36';

// Categoriile sursă + count așteptat (din specificația fazei; necunoscut => null).
const CATEGORIES = [
  { name: 'Banci stradale', slug: 'banci-stradale-si-mobilier-urban', expected: 47 },
  { name: 'Cosuri de gunoi', slug: 'cosuri-de-gunoi-stradale-si-mobilier-stradal', expected: 22 },
  { name: 'Jardiniere', slug: 'jardiniere-parc-jardiniere-stradale-si-mobilier-urban', expected: 3 },
  { name: 'Pergole', slug: 'pergole-si-mobilier-stradal', expected: 3 },
  { name: 'Placute denumiri strazi', slug: 'placute-denumiri-strazi', expected: 2 },
  { name: 'Placute numere casa', slug: 'placute-numere-casa', expected: 1 },
  { name: 'Statii de autobuz', slug: 'statii-de-autobuz-si-mobilier-stradal', expected: 7 },
  { name: 'Suporturi biciclete', slug: 'suporturi-rastele-pentru-parcare-biciclete', expected: 3 },
  { name: 'Echipamente de joaca', slug: 'echipamente-de-joaca-si-mobilier-urban', expected: 7 },
  { name: 'Totemuri', slug: 'totemuri-panouri-cu-mesaje-permanente', expected: null },
  { name: 'Diverse produse', slug: 'diverse-produse', expected: null },
];

const CONCURRENCY = 4;
const MAX_RETRIES = 3;

// ---------------------------------------------------------------------------
// Utilitare
// ---------------------------------------------------------------------------

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const jitter = () => 200 + Math.floor(Math.random() * 300); // 200–500ms politețe

function log(...args) {
  console.log(...args);
}

async function exists(path) {
  try {
    await access(path, FS.F_OK);
    return true;
  } catch {
    return false;
  }
}

/** fetch cu retry + backoff pentru text HTML. */
async function fetchText(url) {
  let lastErr;
  for (let attempt = 1; attempt <= MAX_RETRIES; attempt++) {
    try {
      await sleep(jitter());
      const res = await fetch(url, { headers: { 'User-Agent': UA, 'Accept-Language': 'ro,en' } });
      if (res.status >= 500) throw new Error(`HTTP ${res.status}`);
      if (!res.ok) return { ok: false, status: res.status, body: null };
      return { ok: true, status: res.status, body: await res.text() };
    } catch (err) {
      lastErr = err;
      await sleep(attempt * 800);
    }
  }
  return { ok: false, status: 0, body: null, error: String(lastErr) };
}

/** fetch cu retry pentru imagini (binar). */
async function fetchBuffer(url) {
  let lastErr;
  for (let attempt = 1; attempt <= MAX_RETRIES; attempt++) {
    try {
      await sleep(jitter());
      const res = await fetch(url, { headers: { 'User-Agent': UA } });
      if (res.status >= 500) throw new Error(`HTTP ${res.status}`);
      if (!res.ok) return { ok: false, status: res.status, buffer: null };
      const buf = Buffer.from(await res.arrayBuffer());
      return { ok: true, status: res.status, buffer: buf };
    } catch (err) {
      lastErr = err;
      await sleep(attempt * 800);
    }
  }
  return { ok: false, status: 0, buffer: null, error: String(lastErr) };
}

/** Pool simplu de concurență. */
async function pool(items, size, worker) {
  const results = new Array(items.length);
  let i = 0;
  const runners = Array.from({ length: Math.min(size, items.length) }, async () => {
    while (i < items.length) {
      const idx = i++;
      results[idx] = await worker(items[idx], idx);
    }
  });
  await Promise.all(runners);
  return results;
}

const stripQuery = (u) => u.split('#')[0].split('?')[0];

function lastSegment(u) {
  const clean = stripQuery(u).replace(/\/+$/, '');
  return clean.substring(clean.lastIndexOf('/') + 1);
}

/**
 * Derivă URL-ul full-res dintr-o variantă cache OpenCart:
 *   /image/cache/catalog/cosuri/cos-c120-637x637.jpg
 *   -> /image/catalog/cosuri/cos-c120.jpg
 */
function toFullRes(cacheUrl) {
  return cacheUrl
    .replace('/image/cache/', '/image/')
    .replace(/-\d+x\d+(\.[a-zA-Z0-9]+)(?:$|\?)/, '$1');
}

/** Lățimea din sufixul -WxH (pentru a alege cea mai mare variantă cache la fallback). */
function cacheWidth(url) {
  const m = url.match(/-(\d+)x\d+\.[a-zA-Z0-9]+(?:$|\?)/);
  return m ? parseInt(m[1], 10) : 0;
}

function parseCode(raw) {
  // raw ex: "#C120"  ->  { code:'#C120', prefix:'C', number:'120' }
  if (!raw) return { code: null, prefix: null, number: null };
  const code = raw.trim();
  const m = code.replace('#', '').match(/^([A-Za-z]+)\s*0*(\d+)/);
  return {
    code,
    prefix: m ? m[1].toUpperCase() : null,
    number: m ? m[2] : null,
  };
}

/**
 * Numele folderului de imagini = slug.
 * NU folosim codul: pe site-ul vechi există coduri duplicate pe produse diferite
 * (ex. două produse distincte cu #PS100, sau #B201 apărând și pe pagina B220 —
 * erori de date la sursă). Slug-ul (ultimul segment canonical) e mereu unic.
 */
function imageFolderName(slug) {
  return (slug || 'fara-slug').replace(/[^A-Za-z0-9_-]/g, '');
}

// ---------------------------------------------------------------------------
// Parsare pagină categorie (cu paginare)
// ---------------------------------------------------------------------------

async function crawlCategory(cat, report) {
  const urls = [];
  let page = 1;
  let nextUrl = `${BASE}/${cat.slug}?limit=100`;
  const seenPages = new Set();

  while (nextUrl && !seenPages.has(nextUrl)) {
    seenPages.add(nextUrl);
    const res = await fetchText(nextUrl);
    if (!res.ok) {
      report.errors.push(`Categorie ${cat.slug} pagina ${page}: HTTP ${res.status} (${nextUrl})`);
      break;
    }
    const $ = load(res.body);
    // DOAR grila principală din #content — exclude .box.featured / sidebar / related.
    $('#content .product-thumb .name a').each((_, el) => {
      const href = $(el).attr('href');
      if (href) urls.push(stripQuery(href));
    });

    // Paginare: urmează linkul "next" (rel=next sau page=N+1) dacă există.
    let next = null;
    $('#content .pagination a').each((_, el) => {
      const href = $(el).attr('href') || '';
      const rel = ($(el).attr('rel') || '').toLowerCase();
      const m = href.match(/[?&]page=(\d+)/);
      if (rel.includes('next') || (m && parseInt(m[1], 10) === page + 1)) {
        next = href;
      }
    });
    nextUrl = next;
    page++;
  }

  // Dedup în interiorul categoriei (un produs poate apărea o singură dată în grilă).
  return [...new Set(urls)];
}

// ---------------------------------------------------------------------------
// Parsare pagină produs
// ---------------------------------------------------------------------------

function parseProduct($, sourceUrl, category) {
  const name =
    $('#content h2.product-title').first().text().trim() ||
    $('#content h1').first().text().trim() ||
    null;

  // Atribute din lista de info produs.
  let codeRaw = null;
  let availability = null;
  $('#content li').each((_, el) => {
    const t = $(el).text().trim().replace(/\s+/g, ' ');
    if (/^Cod Produs:/i.test(t)) codeRaw = t.replace(/^Cod Produs:\s*/i, '').trim();
    if (/^Disponibilitate:/i.test(t)) availability = t.replace(/^Disponibilitate:\s*/i, '').trim();
  });
  const { code, prefix, number } = parseCode(codeRaw);

  const canonical = $('link[rel="canonical"]').attr('href') || sourceUrl;
  const slug = lastSegment(canonical) || lastSegment(sourceUrl);

  // Breadcrumb.
  const breadcrumb = [];
  $('.breadcrumb li, .breadcrumb a').each((_, el) => {
    const t = $(el).text().trim().replace(/\s+/g, ' ');
    if (t && !breadcrumb.includes(t)) breadcrumb.push(t);
  });

  // Descriere: paragrafele din tab-description, fără duplicarea titlului din prima linie.
  const descParas = [];
  const descTab = $('#tab-description');
  descTab.find('p').each((_, el) => {
    const t = $(el).text().trim().replace(/\s+/g, ' ');
    if (t) descParas.push(t);
  });
  let description = descParas.join('\n\n');
  if (!description) description = descTab.text().trim().replace(/\n{3,}/g, '\n\n');
  // Dacă prima linie e exact numele, o eliminăm (duplicat de titlu).
  if (name && descParas[0] && descParas[0] === name) {
    description = descParas.slice(1).join('\n\n');
  }

  // Tags / etichete.
  const tags = [];
  $('#content a[href*="tag="], #content a[href*="search="]').each((_, el) => {
    const t = $(el).text().trim();
    if (t && !tags.includes(t)) tags.push(t);
  });

  // Imagini — DOAR galeria produsului (.product-gallery), exclude related/featured.
  const cacheUrls = [];
  $('.product-gallery img, .product-image img').each((_, el) => {
    const u = $(el).attr('src') || $(el).attr('data-src') || '';
    if (u.includes('/catalog/')) cacheUrls.push(u.startsWith('http') ? u : `${BASE}/${u.replace(/^\//, '')}`);
  });
  $('.product-gallery a').each((_, el) => {
    const u = $(el).attr('href') || '';
    if (u.includes('/catalog/')) cacheUrls.push(u.startsWith('http') ? u : `${BASE}/${u.replace(/^\//, '')}`);
  });

  // Grupare după originalul derivat; păstrăm cea mai mare variantă cache ca fallback.
  const byOriginal = new Map(); // original -> { original, bestCache, bestWidth }
  for (const cu of cacheUrls) {
    const original = toFullRes(cu);
    const w = cacheWidth(cu);
    const cur = byOriginal.get(original);
    if (!cur || w > cur.bestWidth) {
      byOriginal.set(original, { original, bestCache: cu, bestWidth: w });
    }
  }

  const meta = {
    description: $('meta[name="description"]').attr('content') || null,
    keywords: $('meta[name="keywords"]').attr('content') || null,
    title: $('title').text().trim() || null,
  };

  return {
    name,
    code,
    code_prefix: prefix,
    code_number: number,
    slug,
    source_url: sourceUrl,
    canonical,
    source_category: {
      name: category.name,
      slug: category.slug,
      url: `${BASE}/${category.slug}`,
    },
    breadcrumb,
    description,
    availability,
    price: null,
    price_on_request: true,
    tags,
    meta,
    _imageOriginals: [...byOriginal.values()], // intern, înlocuit cu căile descărcate
    images: [],
  };
}

// ---------------------------------------------------------------------------
// Descărcare imagini
// ---------------------------------------------------------------------------

async function downloadImages(product, report) {
  const folderName = imageFolderName(product.slug);
  const folder = join(IMAGES_DIR, folderName);
  await mkdir(folder, { recursive: true });

  const rels = [];
  let n = 1;
  for (const img of product._imageOriginals) {
    const ext = (img.original.match(/\.([a-zA-Z0-9]+)(?:$|\?)/)?.[1] || 'jpg').toLowerCase();
    const fileName = `${n}.${ext}`;
    const filePath = join(folder, fileName);
    const relPath = join('images', folderName, fileName);

    // Idempotent: dacă fișierul există deja (>0 bytes), sărim.
    if (await exists(filePath)) {
      const st = await stat(filePath);
      if (st.size > 0) {
        rels.push(relPath);
        n++;
        continue;
      }
    }

    // Încearcă originalul full-res; la 404 fallback la cea mai mare variantă cache.
    let res = await fetchBuffer(img.original);
    let usedFallback = false;
    if (!res.ok && img.bestCache && img.bestCache !== img.original) {
      res = await fetchBuffer(img.bestCache);
      usedFallback = true;
    }
    if (!res.ok) {
      report.errors.push(
        `Imagine 404/eroare pentru ${product.code || product.slug}: ${img.original} (status ${res.status})`
      );
      continue;
    }
    await writeFile(filePath, res.buffer);
    if (usedFallback) {
      report.imageFallbacks.push(`${product.code || product.slug}: fallback cache pentru ${img.original}`);
    }
    rels.push(relPath);
    n++;
  }

  product.images = rels;
  delete product._imageOriginals;
  return rels.length;
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

async function main() {
  await mkdir(IMAGES_DIR, { recursive: true });

  const report = {
    errors: [],
    imageFallbacks: [],
    notFoundProducts: [],
  };

  // 1. Crawl categorii -> liste de URL-uri produs.
  log('==> Crawl categorii…');
  const catResults = [];
  for (const cat of CATEGORIES) {
    const urls = await crawlCategory(cat, report);
    catResults.push({ ...cat, found: urls.length, urls });
    const expStr = cat.expected == null ? '(necunoscut)' : cat.expected;
    const flag = cat.expected != null && urls.length !== cat.expected ? '  ⚠️ DISCREPANȚĂ' : '';
    log(`    ${cat.name}: găsit ${urls.length} / așteptat ${expStr}${flag}`);
  }

  // 2. Listă unică de produse (un produs poate apărea în mai multe categorii).
  //    Prima categorie care îl descoperă devine source_category; reținem și restul.
  const productTasks = []; // { url, category }
  const seenUrls = new Set();
  for (const cr of catResults) {
    for (const url of cr.urls) {
      if (seenUrls.has(url)) continue;
      seenUrls.add(url);
      productTasks.push({ url, category: cr });
    }
  }
  log(`==> ${productTasks.length} produse unice de procesat (din ${catResults.reduce((s, c) => s + c.found, 0)} apariții în grile).`);

  // 3. Procesare produse (parse + download) cu concurență limitată.
  let done = 0;
  const products = await pool(productTasks, CONCURRENCY, async (task) => {
    const res = await fetchText(task.url);
    done++;
    if (!res.ok) {
      report.notFoundProducts.push(`${task.url} (HTTP ${res.status})`);
      log(`    [${done}/${productTasks.length}] EȘUAT ${task.url} — HTTP ${res.status}`);
      return null;
    }
    const $ = load(res.body);
    const product = parseProduct($, task.url, task.category);

    const imgCount = await downloadImages(product, report);
    if (imgCount === 0) {
      report.notFoundProducts.push(`FĂRĂ IMAGINI: ${product.code || product.slug} (${task.url})`);
    }
    log(
      `    [${done}/${productTasks.length}] ${product.code || '???'} ${product.name || ''} — ${imgCount} imagini`
    );
    return product;
  });

  // 3b. Dedup la nivel de produs FIZIC (canonical): același produs poate fi
  //     listat în mai multe categorii, accesibil prin URL-uri nested diferite.
  //     Păstrăm un singur obiect, dar reținem toate categoriile + URL-urile sursă.
  const fetched = products.filter(Boolean);
  const byCanonical = new Map();
  for (const p of fetched) {
    const key = p.canonical || p.source_url;
    if (!byCanonical.has(key)) {
      p.source_categories = [p.source_category];
      p.source_urls = [p.source_url];
      byCanonical.set(key, p);
    } else {
      const ex = byCanonical.get(key);
      if (!ex.source_categories.some((c) => c.slug === p.source_category.slug)) {
        ex.source_categories.push(p.source_category);
      }
      if (!ex.source_urls.includes(p.source_url)) ex.source_urls.push(p.source_url);
    }
  }
  const validProducts = [...byCanonical.values()];
  const crossListed = validProducts.filter((p) => p.source_categories.length > 1);
  const totalImages = validProducts.reduce((s, p) => s + p.images.length, 0);
  log(
    `==> ${fetched.length} apariții -> ${validProducts.length} produse unice (canonical); ` +
      `${crossListed.length} cross-listate în mai multe categorii.`
  );

  // 4. Scriere output JSON.
  await writeFile(join(OUT_DIR, 'products.json'), JSON.stringify(validProducts, null, 2), 'utf-8');
  await writeFile(
    join(OUT_DIR, 'categories.json'),
    JSON.stringify(
      catResults.map((c) => ({
        name: c.name,
        slug: c.slug,
        url: `${BASE}/${c.slug}`,
        expected: c.expected,
        found: c.found,
        match: c.expected == null ? null : c.found === c.expected,
      })),
      null,
      2
    ),
    'utf-8'
  );

  // 5. Raport markdown.
  const noImages = validProducts.filter((p) => p.images.length === 0);
  const lines = [];
  lines.push('# Raport scraping — mobilier-stradal.ro');
  lines.push('');
  lines.push(`Generat de \`scripts/scraper/scrape.mjs\`.`);
  lines.push('');
  lines.push('## Per categorie (așteptat vs găsit)');
  lines.push('');
  lines.push('| Categorie | URL | Așteptat | Găsit | Status |');
  lines.push('|---|---|---:|---:|---|');
  for (const c of catResults) {
    const exp = c.expected == null ? '—' : c.expected;
    let status = '✅';
    if (c.expected == null) status = 'ℹ️ necunoscut';
    else if (c.found !== c.expected) status = `⚠️ diferență (${c.found - c.expected})`;
    lines.push(`| ${c.name} | /${c.slug} | ${exp} | ${c.found} | ${status} |`);
  }
  const expectedTotal = catResults.reduce((s, c) => s + (c.expected || 0), 0);
  const foundTotal = catResults.reduce((s, c) => s + c.found, 0);
  lines.push('');
  lines.push(`**Total apariții în grile:** ${foundTotal} (cunoscute însumate: ${expectedTotal})`);
  lines.push(`**Produse unice salvate (după canonical):** ${validProducts.length}`);
  lines.push(`**Produse cross-listate (în ≥2 categorii):** ${crossListed.length}`);
  lines.push(`**Total imagini descărcate:** ${totalImages}`);
  lines.push('');

  lines.push('## Produse cross-listate (același produs în mai multe categorii)');
  lines.push('');
  if (crossListed.length === 0) lines.push('_Niciunul._');
  else
    for (const p of crossListed) {
      lines.push(`- ${p.code || '?'} — ${p.name}: ${p.source_categories.map((c) => c.name).join(', ')}`);
    }
  lines.push('');

  lines.push('## Produse FĂRĂ imagini');
  lines.push('');
  if (noImages.length === 0) lines.push('_Niciunul — toate produsele au ≥1 imagine._');
  else for (const p of noImages) lines.push(`- ${p.code || '?'} — ${p.name} (${p.source_url})`);
  lines.push('');

  // Coduri duplicate pe produse DISTINCTE (erori de date la sursă) — important
  // pentru faza de import: codul NU poate fi cheie unică.
  const byCode = new Map();
  for (const p of validProducts) {
    if (!p.code) continue;
    if (!byCode.has(p.code)) byCode.set(p.code, []);
    byCode.get(p.code).push(p);
  }
  const dupCodes = [...byCode.entries()].filter(([, v]) => v.length > 1);
  lines.push('## ⚠️ Coduri duplicate pe produse distincte (eroare date sursă)');
  lines.push('');
  lines.push('_Codul NU e cheie unică pe site-ul vechi — de tratat la import._');
  lines.push('');
  if (dupCodes.length === 0) lines.push('_Niciunul._');
  else
    for (const [code, ps] of dupCodes) {
      lines.push(`- **${code}**: ${ps.map((p) => `${p.name} (\`${p.slug}\`)`).join(' — vs — ')}`);
    }
  lines.push('');

  const noDescription = validProducts.filter((p) => !p.description || p.description.trim() === '');
  lines.push('## Produse FĂRĂ descriere (descriere goală la sursă)');
  lines.push('');
  if (noDescription.length === 0) lines.push('_Toate produsele au descriere._');
  else for (const p of noDescription) lines.push(`- ${p.code || '?'} — ${p.name} (${p.canonical})`);
  lines.push('');

  lines.push('## URL-uri produs eșuate (404/eroare)');
  lines.push('');
  if (report.notFoundProducts.length === 0) lines.push('_Niciunul._');
  else for (const e of report.notFoundProducts) lines.push(`- ${e}`);
  lines.push('');

  lines.push('## Imagini cu fallback la varianta cache');
  lines.push('');
  if (report.imageFallbacks.length === 0) lines.push('_Niciuna — toate full-res originale au răspuns 200._');
  else for (const e of report.imageFallbacks) lines.push(`- ${e}`);
  lines.push('');

  lines.push('## Alte erori');
  lines.push('');
  if (report.errors.length === 0) lines.push('_Niciuna._');
  else for (const e of report.errors) lines.push(`- ${e}`);
  lines.push('');

  await writeFile(join(OUT_DIR, 'report.md'), lines.join('\n'), 'utf-8');

  log('');
  log('==> GATA.');
  log(`    Produse unice: ${validProducts.length}`);
  log(`    Imagini descărcate: ${totalImages}`);
  log(`    Produse fără imagini: ${noImages.length}`);
  log(`    Output: ${OUT_DIR}`);
}

main().catch((err) => {
  console.error('EROARE FATALĂ:', err);
  process.exit(1);
});
