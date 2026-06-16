#!/usr/bin/env bash
# Construiește pachetul de deploy pentru shared hosting (fără SSH):
#   storage/app/prod/decor-urban-deploy-{ts}.zip  — cod + vendor(no-dev) + assets + poze live
#   storage/app/prod/.env.prod                    — config (secrete de completat)
#   (+ dump-ul SQL via db:dump-prod, separat)
#
# Rulează din rădăcina proiectului. Necesită Docker (app/node containers) + rsync + zip + npm pe host.
# NU atinge vendor-ul dev local (staging separat).
set -euo pipefail

TS=$(date +%Y%m%d-%H%M)
ROOT=$(pwd)
PROD="storage/app/prod"
STAGE="$PROD/staging"
DC="docker compose exec -T"

echo "==> 1. Curăț cache local (să NU ajungă config-ul local înghețat în zip)"
$DC app php artisan optimize:clear

echo "==> 2. Build assets (public/build/)"
npm run build

echo "==> 3. Dump DB (.sql + .sql.gz)"
$DC app php artisan db:dump-prod

echo "==> 4. Copie staging (exclud git/vendor/node_modules/.env/scrape/teste/scripturi dev)"
rm -rf "$STAGE"
mkdir -p "$STAGE"
rsync -a \
  --exclude='.git/' \
  --exclude='node_modules/' \
  --exclude='vendor/' \
  --exclude='.env' \
  --exclude='.env.*' \
  --exclude='storage/scrape/' \
  --exclude='storage/app/private/products-legacy-backup/' \
  --exclude='storage/app/prod/' \
  --exclude='storage/logs/*.log' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='bootstrap/cache/*.php' \
  --exclude='tests/' \
  --exclude='scripts/scraper/' \
  --exclude='scripts/ai-images/' \
  --exclude='scripts/enrich/' \
  --exclude='dist/' \
  --exclude='*.sql' \
  --exclude='*.sql.gz' \
  ./ "$STAGE/"

echo "==> 5. Vendor de PRODUCȚIE în staging (--no-dev)"
$DC -w "/var/www/html/$STAGE" app composer install --no-dev --optimize-autoloader --no-interaction

echo "==> 6. .env.prod (din example; secretele se completează pe server)"
cp .env.prod.example "$PROD/.env.prod"

echo "==> 7. ZIP (un folder rădăcină 'decor-urban/')"
cd "$PROD"
rm -f "decor-urban-deploy-$TS.zip"
mv staging decor-urban
zip -rqX "decor-urban-deploy-$TS.zip" decor-urban
mv decor-urban staging
cd "$ROOT"

echo "==> 8. Verificări"
ZIP="$PROD/decor-urban-deploy-$TS.zip"
echo "    ZIP: $ZIP ($(du -h "$ZIP" | cut -f1))"
echo -n "    vendor/ în zip:        "; unzip -l "$ZIP" | grep -q 'decor-urban/vendor/autoload.php' && echo OK || echo LIPSĂ
echo -n "    public/build/ în zip:  "; unzip -l "$ZIP" | grep -q 'decor-urban/public/build/' && echo OK || echo LIPSĂ
echo -n "    poze produse în zip:   "; unzip -l "$ZIP" | grep -q 'decor-urban/storage/app/public/products/' && echo OK || echo LIPSĂ
echo -n "    storage/scrape ABSENT: "; unzip -l "$ZIP" | grep -q 'storage/scrape/' && echo "!! PREZENT (greșit)" || echo OK
echo -n "    .env ABSENT:           "; unzip -l "$ZIP" | grep -qE 'decor-urban/\.env$' && echo "!! PREZENT (greșit)" || echo OK

rm -rf "$STAGE"
echo "==> Gata. Urcă zip-ul + importă dump-ul (vezi DEPLOY-PROD.md)."
