# Deploy producție — Decor Urban (shared hosting, fără SSH)

Țintă: hosting shared (cPanel + phpMyAdmin + FTP). Un singur **ZIP** de urcat, un **dump** de
importat, comenzile prin **`/commands/...?secret=`** (runner securizat cu cheia `SECRET`).

## Artefacte (generate local, gitignored, în `storage/app/prod/`)
| Artefact | Cum îl generezi |
|---|---|
| `decor-urban-{ts}.sql(.gz)` — dump DB | `docker compose exec app php artisan db:dump-prod` |
| `decor-urban-deploy-{ts}.zip` — cod + vendor(no-dev) + assets + poze live | `./scripts/build-deploy-zip.sh` |
| `.env.prod` — config (secrete de completat) | generat de scriptul de mai sus (din `.env.prod.example`) |

> Înainte de zip rulează **`php artisan optimize:clear`** — altfel un config cache cu `.env`-ul
> local „îngheață" valorile tale (DB/secrete locale) pe prod. (Scriptul o face automat.)

## Pași pe server

1. **Urci + dezarhivezi** zip-ul în rădăcina aplicației (conține `app/`, `vendor/`, `public/`, etc.).
2. **`.env`**: copiază `.env.prod` ca `.env` și completează secretele:
   - `APP_KEY` — **copiat din `.env` local** (consistență cookie/sesiune; NU genera altul).
   - `DB_DATABASE/USERNAME/PASSWORD` — din cPanel.
   - `MAIL_*` — SMTP real (fără el, emailurile de comandă NU pleacă).
   - `SECRET` — cheie lungă/random (alta decât local).
3. **Bază de date**: creezi DB + user în cPanel; **importezi** `decor-urban-{ts}.sql` în phpMyAdmin.
   (Dump-ul include `migrations` → schema e la zi, **`migrate` nu e necesar**.)
4. **Document root**: setează domeniul pe folderul `public/` (sau mută conținutul `public/` în
   `public_html` și ajustează căile din `index.php` — specific hostingului).
5. **Permisiuni** write pe `storage/` și `bootstrap/cache/`.
6. **Pozele live** sunt deja în zip (`storage/app/public/products|projects`). Dacă adaugi altele
   ulterior, le urci prin FTP în aceleași căi.
7. **Comenzi** (din browser, cu cheia):
   - `/commands/create-storage-link?secret=...`
   - `/commands/optimize?secret=...`
   - `/commands/create-sitemap?secret=...`
   - (opțional `/commands/migrate-status?secret=...` ca să confirmi schema)
8. **Schimbă parola adminului** imediat (Filament → profil) — vine `password` din dump.
9. **Verifici**: homepage, un produs (poză + specs), `/catalog` cu filtru material, meniu mobil,
   o comandă test → confirmi emailul primit.

## Securitate
- `/commands` rămâne activ permanent, protejat de `SECRET` (404 fără cheie). Ține `SECRET` secret,
  rotește-l periodic, nu-l pune în linkuri partajate.
- NU comite niciodată: dump-ul (date), zip-ul, `.env`-ul real, `SECRET`-ul.

## Checklist non-cod înainte de lansare
- [ ] `SECRET` pe prod ≠ cel local.
- [ ] Parola admin schimbată (și user-ul secundar dacă are parolă slabă).
- [ ] SMTP real configurat + test comandă.
- [ ] Pagini legale (confidențialitate/termeni/cookies) verificate de specialist.
- [ ] CAEN 4619 vs „producător direct" — confirmat cu contabilul.
