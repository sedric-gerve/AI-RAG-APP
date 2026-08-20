#!/bin/sh
set -e

php artisan config:clear
php artisan package:discover --ansi
php artisan migrate --force

# Demo data is reseeded on every boot (updateOrCreate = idempotent) so the
# demo account survives a free-tier Postgres reset without manual steps.
php artisan db:seed --class="Database\Seeders\DemoSeeder" --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
