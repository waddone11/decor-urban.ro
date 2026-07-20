# Decor Urban — decor-urban.ro

Magazin online de **mobilier stradal & urban** (bănci, coșuri de gunoi, jardiniere,
pergole, locuri de joacă etc.).

> **Stare: Faza 0 — infrastructură + stack.** Doar fundația tehnică rulează local.
> Storefront-ul, importul de produse, coșul și checkout-ul vin în fazele următoare.

## Stack

| Componentă        | Versiune              |
|-------------------|-----------------------|
| Laravel           | 13.x (PHP 8.3)        |
| Filament (admin)  | 5.x                   |
| Livewire          | 4.x                   |
| Tailwind CSS      | 4.x (CSS-first + Vite)|
| Vite              | 8.x                   |
| MySQL             | 8.0                   |
| Redis             | alpine                |
| Mailpit           | latest                |
| Playwright        | 1.x (E2E)             |

Totul rulează prin **Docker Compose** (custom, fără Laravel Sail).

## Cerințe

- Docker Desktop pornit.
- Node.js (pe host) doar pentru a rula testele Playwright. Restul (PHP, Composer,
  build-ul Vite) rulează în containere — nu ai nevoie de PHP/Composer pe host.

## Pornire rapidă

```bash
# 1. Configurare mediu (o singură dată)
cp .env.example .env
docker compose run --rm --no-deps app php artisan key:generate

# 2. Pornește tot stack-ul (așteaptă să fie toate healthy)
docker compose up -d --wait

# 3. Migrează baza de date + storage symlink
docker compose exec -e HOME=/tmp app php artisan migrate
docker compose exec -e HOME=/tmp app php artisan storage:link
```

> Repo-ul vine deja cu un `.env` funcțional pentru dev local și cu baza de date
> migrată, deci de regulă e suficient `docker compose up -d --wait`.

## URL-uri

| Serviciu              | URL                                    |
|-----------------------|----------------------------------------|
| Storefront (homepage) | http://localhost:8080                  |
| Admin (Filament)      | http://localhost:8080/admin            |
| Mailpit (email UI)    | http://localhost:8025                  |
| Vite dev server (HMR) | http://localhost:5173                  |
| MySQL                 | `localhost:3306` (db `decor_urban`)    |

## Cont admin (local)

```
Email:    admin@decor-urban.ro
Parolă:   DecorAdmin2026!
```

> ⚠️ **Parolă temporară — schimb-o.** E doar pentru dev local. Pentru a crea/reseta
> un admin: `docker compose exec -e HOME=/tmp app php artisan make:filament-user`.

## Comenzi utile

```bash
# Artisan / Composer în container (wrappere subțiri)
./scripts/artisan <comandă>          # ex: ./scripts/artisan migrate:fresh
./scripts/composer <comandă>         # ex: ./scripts/composer require vendor/pkg

# Echivalent manual
docker compose exec -e HOME=/tmp app php artisan tinker

# Build asset-uri (în container)
docker compose exec node npm run build

# Logs
docker compose logs -f app
docker compose logs -f web

# Oprire / repornire
docker compose down                  # oprește (păstrează volumele)
docker compose down -v               # oprește + șterge baza de date și node_modules
```

### Vite / Tailwind

Dev server-ul Vite (HMR) rulează automat în serviciul `node` (`npm run dev`).
Tailwind 4 e configurat **CSS-first** în `resources/css/app.css`
(`@import 'tailwindcss';`) cu plugin-ul `@tailwindcss/vite` — fără `tailwind.config.js`.

`node_modules` din container e izolat într-un volum Docker dedicat, ca să nu intre
în conflict cu `node_modules`-ul de pe host (macOS) folosit de Playwright.

## Teste E2E (Playwright)

Testele rulează pe **host** și lovesc stack-ul pornit în Docker.

```bash
# o singură dată
npm install
npx playwright install chromium

# rulează testele (stack-ul trebuie să fie pornit)
npx playwright test          # sau: npm run test:e2e
npx playwright test --ui     # mod interactiv
```

Smoke tests acoperite (`tests/e2e/smoke.spec.ts`):

- `GET /` → 200 + textul placeholder (Livewire 4 + Tailwind 4).
- counter-ul Livewire e interactiv (dovadă că Livewire funcționează).
- `/admin/login` randează formularul Filament.
- login admin reușit → ajunge pe dashboard.

## Structura Docker

```
docker/
  php/Dockerfile      # PHP 8.3-fpm + extensii (pdo_mysql, gd, intl, redis, ...) + Composer
  php/php.ini         # tweak-uri runtime (opcache, upload, timezone)
  nginx/default.conf  # nginx -> fastcgi app:9000, root /public
docker-compose.yml    # app, web, db, redis, mailpit, node
```

Servicii (`docker compose ps`):

| Serviciu  | Imagine            | Port host |
|-----------|--------------------|-----------|
| `app`     | PHP 8.3-fpm (build)| —         |
| `web`     | nginx:alpine       | 8080      |
| `db`      | mysql:8.0          | 3306      |
| `redis`   | redis:alpine       | —         |
| `mailpit` | axllent/mailpit    | 8025/1025 |
| `node`    | node:22 (Vite)     | 5173      |

## Storefront (Faza 4b)

Rute publice:

| Rută                      | Ce face                                                        |
|---------------------------|---------------------------------------------------------------|
| `/catalog`                | Catalog Livewire: filtre pe categorii, search, sortare, paginare (URL sincronizat) |
| `/categorie/{slug}`       | Listare categorie (sortare + paginare + breadcrumb)           |
| `/produs/{slug}`          | Pagină produs: galerie + info + CTA WhatsApp + produse similare |
| `/sitemap.xml`            | Sitemap dinamic (categorii + produse active)                  |
| `/robots.txt`             | Permite, indică sitemap, blochează `/admin` și `/commands`    |

Doar categoriile/produsele `is_active` apar în storefront; slug inexistent/inactiv → 404.
URL-urile vechi (`legacy_urls`) fac **301** către canonicalul nou (vezi `App\Support\LegacyRedirects`;
harta e cache-uită — rulează `cache:clear` după un reimport).

### Catalog snapshot & seeding pe prod (fără terminal/scrape)

Catalogul se reconstruiește dintr-un snapshot JSON commis, nu din scrape:

```bash
# local/dev: exportă DB curentă → database/data/catalog.json (commite fișierul)
php artisan catalog:export-snapshot

# pe prod: reconstruiește tot (rulat din /commands sau SSH)
php artisan migrate:fresh --seed --force   # CategorySeeder + CatalogSeeder
```

`CatalogSeeder` e idempotent (`updateOrCreate`). **Fișierele imagine NU sunt în git** — populăm
doar rândurile `product_images`. Pozele se urcă separat (FTP/cPanel) în
`storage/app/public/products/<slug>/`, apoi `php artisan storage:link`.

### Thumbnails (variante 400/800 WebP)

Pentru încărcare rapidă, listările/galeriile folosesc variante mici WebP, nu originalul full.
**Generare LOCALĂ** (shared hosting poate n-are GD/Imagick) — fișierele se urcă apoi pe prod.

```bash
# generează <base>-400.webp + <base>-800.webp lângă fiecare imagine (produse + proiecte)
php artisan images:thumbnails            # idempotent (sare peste cele existente)
php artisan images:thumbnails --force    # regenerează tot
php artisan images:thumbnails --only <slug>
```

- Necesită GD cu suport WebP (deja în imaginea Docker: `--with-webp`).
- Afișarea e **defensivă**: `thumbUrl()` cade pe original dacă varianta lipsește — site-ul nu
  se strică până urci variantele.
- `images:promote-ai` cheamă automat `images:thumbnails` la final, deci pozele AI noi capătă variante.
- **Deploy**: variantele stau lângă originale în `storage/app/public/...`, deci intră în
  `build-deploy-zip.sh` ca orice fișier; sau urci doar folderele `products`/`projects` prin FTP.

### `/commands` — helper artisan din URL (hosting fără SSH)

Rulează comenzi artisan din URL, securizat cu **o singură cheie `secret`** din `.env`.
Rutele rulează **fără sesiune/CSRF**, deci merg și pe o **DB proaspătă/goală** (înainte de migrate).
Runner-ul rămâne **permanent activ**; singura poartă este `SECRET`.

1. În `.env`: `SECRET=<cheie-lungă-random>` (`php artisan str:random 48`). **NU o comite.**
2. `/commands?secret=...` → pagină cu linkuri către comenzi. Fără `secret` corect (sau header
   `X-Command-Secret`) → **404** (nu dezvăluie că ruta există).
3. `migrate-fresh-seed` (distructiv) cere în plus `&confirm=YES`.
4. Fiecare invocare e logată în `storage/logs/commands.log` (IP, comandă, timestamp).
5. Folosește HTTPS, rotește `SECRET` periodic și nu pune cheia în linkuri partajate.

Comenzi mentenanță: `clear-cache`, `config-clear`, `route-clear`, `view-clear`, `optimize-clear`,
`config-cache`, `route-cache`, `view-cache`, `optimize`, `create-storage-link`, `create-sitemap`,
`migrate`, `migrate-fresh-seed` (confirm=YES), `migrate-status`, `about`, `catalog-summary`,
`queue-restart`, `trigger-queue`, `thumbnails`, `export-snapshot`.

Comenzi feed-uri: `feeds-google`, `feeds-meta`, `feeds-all`, `google-business-export`.
Endpointurile publice rămân separate: `/feeds/google-merchant.xml` și `/feeds/meta-catalog.csv`;
`/commands/feeds-*` doar regenerează cache-ul și întorc raport text cu produse incluse/excluse.

Cron cPanel pentru feeduri proaspete zilnic:

```cron
# zilnic 04:00 — regenerează feed-urile
0 4 * * * curl -s "https://decor-urban.ro/commands/feeds-all?secret=CHEIA" > /dev/null
```

> Helper pentru hosting fără SSH — **nu** înlocuiește un deploy real.

### Secvență deploy (hosting fără SSH)

1. urci codul (git pull / FTP) + `.env` (cu `SECRET` setat);
2. `/commands/migrate?secret=...` (sau `/commands/migrate-fresh-seed?secret=...&confirm=YES` la prima instalare);
3. urci fișierele imagine **+ variantele 400/800** în `storage/app/public/products|projects/...` (FTP);
   generează variantele local înainte (`php artisan images:thumbnails`);
4. `/commands/create-storage-link?secret=...`, `/commands/optimize?secret=...`, `/commands/create-sitemap?secret=...`;
5. verifică `/commands/feeds-all?secret=...` și rotește periodic `SECRET`.

## Coș & comandă (Faza 4c)

Prețurile sunt „la cerere", deci coșul e o **cerere de ofertă**, fără plată online.

| Rută                 | Ce face                                                            |
|----------------------|--------------------------------------------------------------------|
| `/cos`               | Coș guest (pe sesiune): cantități editabile, elimină, stare goală  |
| `/checkout`          | Formular guest (date + B2B firmă/CUI + metodă) → creează comanda   |
| `/comanda/{number}`  | Pagină succes: rezumat, „ți-am trimis email", buton WhatsApp       |

Fluxul: adaugi produse → `/checkout` → se creează `Order` + `OrderItem` (snapshot nume/cod/cantitate),
status `noua` → emailuri (client + admin) → pagina de succes cu deep-link `wa.me` (mesaj precompletat).
Comenzile se gestionează în Filament (`/admin` → grup **Comenzi**): status editabil, filtre, search,
„retrimite email"; produsele comenzii sunt read-only (snapshot).

### Mail (comenzi)

- **Local:** Mailpit (`MAIL_HOST=mailpit`, UI pe `:8025`).
- **Prod:** trebuie un **SMTP real** (al tău sau un serviciu tranzacțional). Setează
  `MAIL_HOST/PORT/USERNAME/PASSWORD/MAIL_SCHEME` + `MAIL_FROM_ADDRESS` în `.env`.
  **Fără SMTP real, emailurile de comandă NU pleacă** (comanda se salvează oricum și apare în panel —
  trimiterea e prinsă în try/catch și logată).
- Adresa care primește comenzile noi = `CONTACT_EMAIL` (`config/contact.php`).
- Trimitere **sincronă** (volum mic). Pe hosting fără worker de coadă, lasă așa; dacă pui worker,
  poți face Mailable-urile `ShouldQueue` și seta `QUEUE_CONNECTION`.

## În afara scope-ului (Faza 0)

Design final, import produse din site-ul vechi (OpenCart), rute storefront, coș,
checkout, plată online, notificări — toate în fazele următoare.
