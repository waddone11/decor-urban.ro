# Poze AI — Nano Banana (Gemini image)

Trece pozele de produs scrape-uite prin **Nano Banana** (Gemini image) → studio shots premium,
păstrând identitatea exactă a produsului. Flux în 3 timpi cu **poartă de review**.

## Flux

```
1. GENERARE (staging)   node generate.mjs --limit 5
2. REVIEW (înainte/după) node review.mjs        -> deschide storage/scrape/images-ai/review.html
3. PROMOVARE (după OK)   php artisan images:promote-ai
```

- **Sursă (intactă):** `storage/scrape/images/<slug>/<file>` — backup pristin, nu se atinge.
- **Staging:** `storage/scrape/images-ai/<slug>/<file>` (+ `manifest.json`, `review.html`).
- **Public (după promovare):** disk-ul `public` Laravel — `storage/app/public/products/<slug>/<file>`.

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
| `--cost-per-image U` | USD/imagine pentru estimare cost |

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
