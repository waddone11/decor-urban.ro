#!/usr/bin/env node
// Construieste un contact-sheet de review (inainte -> dupa) din pozele generate in staging.
// Output: storage/scrape/images-ai/review.html  (deschide-l in browser).
//
// Grupeaza pe categorie (din products.json) ca sa verifici usor un esantion reprezentativ:
// min. 1 produs / categorie, plus cele cu metal vopsit si cu lemn.
//
//   node review.mjs [--slug <slug>] [--failed]
//     --slug                doar un produs
//     --failed              include si esecurile (cu marcaj), pentru diagnostic
//     --compare <dir>       a doua coloana AI (alt model), ex. storage/scrape/images-ai-flash
//                           -> scrie compare.html cu 3 coloane: inainte / AI / AI(compare)
//     --compare-label <s>   eticheta pt coloana de comparatie (default: numele dir-ului)

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { analyze, pool } from './qa.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..', '..');
const OUT_DIR = path.join(ROOT, 'storage', 'scrape', 'images-ai');
const SRC_IMAGES = path.join(ROOT, 'storage', 'scrape', 'images');
const MANIFEST = path.join(OUT_DIR, 'manifest.json');
const PRODUCTS = path.join(ROOT, 'storage', 'scrape', 'products.json');
const REVIEW = path.join(OUT_DIR, 'review.html');

function parseArgs(argv) {
  const a = { slug: null, failed: false, compare: null, compareLabel: null, noQa: false };
  for (let i = 0; i < argv.length; i++) {
    if (argv[i] === '--slug') a.slug = argv[++i];
    else if (argv[i] === '--failed') a.failed = true;
    else if (argv[i] === '--compare') a.compare = argv[++i];
    else if (argv[i] === '--compare-label') a.compareLabel = argv[++i];
    else if (argv[i] === '--no-qa') a.noQa = true;
  }
  return a;
}

function esc(s) {
  return String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
}

function loadProductsBySlug() {
  const map = new Map();
  if (fs.existsSync(PRODUCTS)) {
    try {
      const arr = JSON.parse(fs.readFileSync(PRODUCTS, 'utf8'));
      for (const p of arr) map.set(p.slug, p);
    } catch { /* ignore */ }
  }
  return map;
}

async function main() {
  const args = parseArgs(process.argv.slice(2));

  const compareAbs = args.compare ? path.resolve(ROOT, args.compare) : null;
  const compareLabel = args.compareLabel || (compareAbs ? path.basename(compareAbs) : null);
  const outFile = compareAbs ? path.join(OUT_DIR, 'compare.html') : REVIEW;

  if (!fs.existsSync(MANIFEST)) {
    console.error(`Nu gasesc ${MANIFEST}. Ruleaza intai generate.mjs.`);
    process.exit(1);
  }
  const manifest = JSON.parse(fs.readFileSync(MANIFEST, 'utf8'));
  const products = loadProductsBySlug();

  // intrari -> {slug, file, rel, status, error, category, name, code}
  const rows = [];
  for (const [rel, e] of Object.entries(manifest.items || {})) {
    const m = rel.match(/^images\/([^/]+)\/(.+)$/);
    if (!m) continue;
    const [, slug, file] = m;
    if (args.slug && slug !== args.slug) continue;
    if (e.status !== 'done' && !(args.failed && e.status === 'failed')) continue;
    const p = products.get(slug);
    rows.push({
      slug, file, rel, status: e.status, error: e.error, model: e.model,
      category: p?.source_category?.name || 'Necategorizat',
      name: p?.name || slug,
      code: p?.code || '',
    });
  }

  // grupare pe categorie
  rows.sort((a, b) =>
    a.category.localeCompare(b.category) || a.slug.localeCompare(b.slug) ||
    a.file.localeCompare(b.file, undefined, { numeric: true }));

  // ---- Auto-QA: semnaleaza output-urile suspecte ----
  if (!args.noQa) {
    const doneRows = rows.filter((r) => r.status === 'done');
    process.stdout.write(`Auto-QA pe ${doneRows.length} imagini… `);
    await pool(doneRows, async (r) => {
      const aiPath = path.join(OUT_DIR, r.slug, r.file);
      const origPath = path.join(SRC_IMAGES, r.slug, r.file);
      r.qa = await analyze(aiPath, fs.existsSync(origPath) ? origPath : null);
    }, 8);
    console.log('gata.');
  }
  const flagged = rows.filter((r) => r.qa?.flags?.length);

  const byCat = new Map();
  for (const r of rows) {
    if (!byCat.has(r.category)) byCat.set(r.category, []);
    byCat.get(r.category).push(r);
  }

  const doneCount = rows.filter((r) => r.status === 'done').length;
  const failCount = rows.filter((r) => r.status === 'failed').length;

  let html = `<!doctype html><html lang="ro"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Review poze AI — inainte/dupa</title>
<style>
  :root{--bg:#0f0f10;--card:#1a1a1c;--muted:#9aa0a6;--line:#2a2a2d;--ok:#3fb950;--bad:#f85149;}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:#e6e6e6;font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
  header{padding:20px 24px;border-bottom:1px solid var(--line);position:sticky;top:0;background:var(--bg);z-index:2}
  h1{margin:0 0 4px;font-size:18px}
  .sub{color:var(--muted)}
  h2{margin:28px 24px 8px;font-size:15px;color:#cdd3da;border-left:3px solid #4a90d9;padding-left:10px}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;padding:8px 24px 24px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:10px;overflow:hidden}
  .pair{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--line)}
  .pair.three{grid-template-columns:1fr 1fr 1fr}
  .pair figure{margin:0;background:#000}
  .pair img{display:block;width:100%;height:200px;object-fit:contain;background:#111}
  .pair figcaption{font-size:11px;color:var(--muted);text-align:center;padding:4px 0;background:var(--card)}
  .meta{padding:8px 12px}
  .name{font-weight:600}
  .code{color:var(--muted);font-size:12px}
  .badge{display:inline-block;font-size:11px;padding:1px 7px;border-radius:999px;border:1px solid var(--line)}
  .badge.ok{color:var(--ok);border-color:var(--ok)}
  .badge.bad{color:var(--bad);border-color:var(--bad)}
  .badge.flag{color:#d29922;border-color:#d29922}
  .flags{font-size:11px;color:#d29922;padding:0 12px 8px}
  .card.flagged{outline:2px solid #d29922}
  .summary{margin:12px 24px;padding:10px 14px;background:#1a1a1c;border:1px solid #d29922;border-radius:8px;font-size:13px}
  .summary a{color:#d29922}
  .err{color:var(--bad);font-size:12px;padding:0 12px 10px}
  .check{color:var(--muted);font-size:12px;padding:6px 12px 12px;border-top:1px dashed var(--line)}
</style></head><body>
<header>
  <h1>Review poze AI — ${compareAbs ? `inainte / AI / ${esc(compareLabel)}` : 'inainte (scrape) / dupa (Nano Banana)'}</h1>
  <div class="sub">${doneCount} generate${failCount ? ` · ${failCount} esecuri` : ''}${args.noQa ? '' : ` · ${flagged.length} flagged auto-QA`} · verifica: numar de sipci/bare · proportii · culori · identitate produs</div>
</header>`;

  if (!args.noQa && flagged.length) {
    html += `\n<div class="summary"><b>Auto-QA — ${flagged.length} de verificat tintit:</b><br>` +
      flagged.map((r) => `<a href="#${esc(r.slug)}-${esc(r.file)}">${esc(r.code || r.slug)} / ${esc(r.file)}</a>: ${esc(r.qa.flags.map((f) => f.msg).join('; '))}`).join('<br>') +
      `</div>`;
  }

  for (const [cat, items] of byCat) {
    html += `\n<h2>${esc(cat)} <span class="sub">(${items.length})</span></h2>\n<div class="grid">`;
    for (const r of items) {
      // cai relative la review.html (care sta in images-ai/)
      const before = `../images/${esc(r.slug)}/${esc(r.file)}`;
      const after = `${esc(r.slug)}/${esc(r.file)}`;
      const badge = r.status === 'done'
        ? '<span class="badge ok">AI ok</span>'
        : '<span class="badge bad">esec</span>';
      let cmpFigure = '';
      if (compareAbs) {
        const cmpAbsFile = path.join(compareAbs, r.slug, r.file);
        const cmpRel = esc(path.relative(OUT_DIR, cmpAbsFile));
        cmpFigure = fs.existsSync(cmpAbsFile)
          ? `<figure><img loading="lazy" src="${cmpRel}" alt="${esc(compareLabel)}"><figcaption>${esc(compareLabel)}</figcaption></figure>`
          : `<figure><div style="height:200px;display:flex;align-items:center;justify-content:center;color:#9aa0a6">(lipsa)</div><figcaption>${esc(compareLabel)}</figcaption></figure>`;
      }
      const qaFlags = r.qa?.flags || [];
      html += `
  <div class="card${qaFlags.length ? ' flagged' : ''}" id="${esc(r.slug)}-${esc(r.file)}">
    <div class="pair${compareAbs ? ' three' : ''}">
      <figure><img loading="lazy" src="${before}" alt="inainte"><figcaption>INAINTE (scrape)</figcaption></figure>
      ${r.status === 'done'
          ? `<figure><img loading="lazy" src="${after}" alt="dupa"><figcaption>${compareAbs ? 'AI (primary)' : 'DUPA (AI)'}</figcaption></figure>`
          : `<figure><div style="height:200px;display:flex;align-items:center;justify-content:center;color:#f85149">esec</div><figcaption>DUPA</figcaption></figure>`}
      ${cmpFigure}
    </div>
    <div class="meta"><span class="name">${esc(r.name)}</span> <span class="code">${esc(r.code)}</span> ${badge}${qaFlags.length ? ' <span class="badge flag">⚑ QA</span>' : ''}</div>
    ${r.error ? `<div class="err">${esc(r.error)}</div>` : ''}
    ${qaFlags.length ? `<div class="flags">⚑ ${esc(qaFlags.map((f) => f.msg).join('; '))}</div>` : ''}
    <div class="check">${esc(r.file)} · ${esc(r.model || '')}${r.qa?.width ? ` · ${r.qa.width}×${r.qa.height}` : ''}</div>
  </div>`;
    }
    html += `\n</div>`;
  }

  html += `\n</body></html>`;

  fs.mkdirSync(OUT_DIR, { recursive: true });
  fs.writeFileSync(outFile, html);
  console.log(`Scris ${outFile}`);
  console.log(`  ${doneCount} ${compareAbs ? 'randuri inainte/AI/' + compareLabel : 'perechi inainte/dupa'}${failCount ? `, ${failCount} esecuri` : ''}, ${byCat.size} categorii.`);
  console.log(`Deschide ${path.basename(outFile)} in browser si verifica pastrarea identitatii inainte de promovare.`);
  if (!args.noQa) {
    console.log(`\nAuto-QA: ${flagged.length} flagged (de verificat tintit):`);
    for (const r of flagged) {
      console.log(`  ⚑ ${r.code || r.slug}/${r.file}: ${r.qa.flags.map((f) => f.msg).join('; ')}`);
    }
  }
}

main().catch((e) => { console.error(e); process.exit(1); });
