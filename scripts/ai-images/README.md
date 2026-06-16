# Poze AI — Nano Banana (Gemini image)

Trece pozele de produs scrape-uite prin **Nano Banana** (Gemini image) → studio shots premium,
păstrând identitatea exactă a produsului. Flux în 3 timpi cu **poartă de review**.

## Flux

```
1. GENERARE (staging)   node generate.mjs --limit 5
2. REVIEW (înainte/după) node review.mjs        -> deschide storage/scrape/images-ai/review.html
3. PROMOVARE (după OK)   php artisan images:promote-ai
4. REVERT (dacă e cazul) php artisan images:revert-ai [--only <slug>]
```

`review.mjs` rulează și **auto-QA** (sharp): marchează output-urile suspecte (aspect ≠ 1:1, rezoluție
mică, fundal non-neutru, posibilă schimbare de culoare) ca să le verifici țintit.

- **Sursă (intactă):** `storage/scrape/images/<slug>/<file>` — backup pristin, nu se atinge.
- **Staging:** `storage/scrape/images-ai/<slug>/<file>` (+ `manifest.json`, `review.html`).
- **Public (după promovare):** disk-ul `public` Laravel — `storage/app/public/products/<slug>/<file>`.
- **Backup la promovare:** poza publică curentă e salvată o dată în
  `storage/app/private/products-legacy-backup/<slug>/` înainte de overwrite (dublă plasă).

## Promovare, revert, summary

```bash
php artisan images:promote-ai [--only <slug>] [--dry-run]   # AI -> public + source=ai + backup
php artisan images:revert-ai  [--only <slug>] [--dry-run]   # restaurează originalul pristin + source=legacy
php artisan catalog:summary                                 # câte imagini ai vs legacy
```

Produsele fără output AI valid rămân pe `legacy` (catalog consistent, mix ai/legacy).

## Deploy (poze NU călătoresc prin git)

`storage/` și `public/products` sunt **gitignored** — pozele nu intră în git. La deploy pe producție:

1. **Codul** (migrație, comenzi, `source`/`enhanced_at`) ajunge prin git/merge.
2. **Pozele AI** se duc manual pe server: sync `storage/scrape/images-ai/` → server, apoi
   `php artisan images:promote-ai` **rulat pe server** (copiază în public + setează `source=ai`).
3. `php artisan storage:link` trebuie să existe pe server (public/storage → storage/app/public).

> Adică: merge-ul aduce doar codul; **promovarea efectivă se face rulând comanda în fiecare mediu.**

## Setup

```bash
cd scripts/ai-images
npm install            # @google/genai
```

`GEMINI_API_KEY` se citește din `.env` (rădăcina proiectului) sau din environment.

> Rulează `generate.mjs` / `review.mjs` dintr-un checkout care are `storage/scrape/` (pozele
> scrape-uite). Căile sunt rezolvate relativ la rădăcina proiectului.

## generate.mjs

| Flag | Efect |
|------|-------|
| `--limit N` | maxim N imagini (testare) |
| `--only <slug>` | doar un produs |
| `--model <id>` | default `gemini-3-pro-image` (Pro). Alt.: `gemini-3.1-flash-image` (rapid) |
| `--size 1K\|2K\|4K` | rezoluție (default 2K) |
| `--aspect 1:1` | aspect ratio (default 1:1) |
| `--concurrency N` | cereri concurente (default 3) |
| `--retries N` | reîncercări pe 429/5xx (default 4) |
| `--force` | re-generează chiar dacă există output |
| `--all` | necesar pentru **batch-ul complet** (când lipsesc `--limit`/`--only`) |
| `--out <dir>` | dir staging alternativ (relativ la rădăcină), ex. pentru comparații între modele |
| `--cost-per-image U` | USD/imagine pentru estimare cost |

### Comparație între modele

```bash
node generate.mjs --limit 5                                              # Pro -> images-ai/
node generate.mjs --limit 5 --model gemini-3.1-flash-image --out storage/scrape/images-ai-flash
node review.mjs --compare storage/scrape/images-ai-flash --compare-label "Flash"   # -> compare.html (inainte/Pro/Flash)
```

> Observație testată pe sample: **Flash păstrează watermark-ul sursei** și transformă minimal;
> **Pro elimină watermark-ul** și face studio shot real, păstrând identitatea → folosește **Pro** pentru catalog.

- **Idempotent / reluabil:** sare peste imaginile cu output deja în staging; `manifest.json`
  ține status per fișier (`done`/`failed`), model, timestamp, eroare. Se salvează după fiecare imagine.
- **Robust:** concurență mică + retry cu backoff pe 429/5xx; un refuz/eșec per produs e marcat
  `failed` și **nu** oprește batch-ul.

Recomandare: rulează întâi ~5 produse cu `gemini-3-pro-image` și `gemini-3.1-flash-image` și compară
înainte de batch-ul complet (~195 imagini).

## Note API

- image-to-image: `generateContent` cu `contents:[{text}, {inlineData:{mimeType,data}}]`;
  `config.imageConfig = { aspectRatio, imageSize }`. Răspunsul vine ca `part.inlineData.data` (base64).
- Modelul poate fi pe canal preview — dacă API-ul respinge `gemini-3-pro-image`, încearcă
  `--model gemini-3-pro-image-preview`.
- Toate ies cu **SynthID** (watermark invizibil, inevitabil — ok pentru poze de produs).
- Output-ul AI e PNG, salvat cu **același nume de fișier** ca sursa (ex. `1.jpg`) ca să rămână valid
  `product_images.path` la promovare.
