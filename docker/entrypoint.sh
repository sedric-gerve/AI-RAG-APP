#!/bin/sh
set -e

# Render's dashboard env var fields have, in practice, let a stray CR/LF
# through on paste — enough to make Laravel's URL generator throw on every
# boot ("A URI cannot contain CR/LF/TAB characters"), and enough to make
# the backfill route's hash_equals() token check silently fail (404).
# Sanitize every manually-entered secret defensively rather than relying
# on re-entry being perfect every time.
export APP_KEY=$(printf '%s' "$APP_KEY" | tr -d '\r\n\t')
export APP_URL=$(printf '%s' "$APP_URL" | tr -d '\r\n\t')
export DB_URL=$(printf '%s' "$DB_URL" | tr -d '\r\n\t')
export ANTHROPIC_API_KEY=$(printf '%s' "$ANTHROPIC_API_KEY" | tr -d '\r\n\t')
export VOYAGE_API_KEY=$(printf '%s' "$VOYAGE_API_KEY" | tr -d '\r\n\t')
export BACKFILL_TOKEN=$(printf '%s' "$BACKFILL_TOKEN" | tr -d '\r\n\t')

echo ">>> ENTRYPOINT START"
echo ">>> APP_URL is: [${APP_URL}]"
echo ">>> DB_URL is set: $([ -n "$DB_URL" ] && echo yes || echo no)"

echo ">>> config:clear"
php artisan config:clear

echo ">>> package:discover"
php artisan package:discover --ansi

echo ">>> migrate"
php artisan migrate --force

# Demo data is reseeded on every boot (updateOrCreate = idempotent) so the
# demo account survives a free-tier Postgres reset without manual steps.
echo ">>> db:seed"
php artisan db:seed --class="Database\Seeders\DemoSeeder" --force

echo ">>> config:cache"
php artisan config:cache
echo ">>> route:cache"
php artisan route:cache
echo ">>> view:cache"
php artisan view:cache

# php artisan serve is single-threaded and not meant for real traffic —
# Render's health checks tripped it up in practice. Nginx + PHP-FPM is the
# standard, robust way to serve Laravel; nginx's listen port is templated
# because Render assigns $PORT dynamically at runtime.
echo ">>> rendering nginx config for port ${PORT:-10000}"
sed "s/__PORT__/${PORT:-10000}/" /etc/nginx/nginx-site.conf.template > /etc/nginx/sites-available/default

echo ">>> starting php-fpm"
php-fpm -D
echo ">>> starting nginx"
exec nginx -g 'daemon off;'
