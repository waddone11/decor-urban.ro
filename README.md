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

## În afara scope-ului (Faza 0)

Design final, import produse din site-ul vechi (OpenCart), rute storefront, coș,
checkout, plată online, notificări — toate în fazele următoare.
