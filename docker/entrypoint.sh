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

# php artisan serve is single-threaded and not meant for real traffic —
# Render's health checks tripped it up in practice. Nginx + PHP-FPM is the
# standard, robust way to serve Laravel; nginx's listen port is templated
# because Render assigns $PORT dynamically at runtime.
sed "s/__PORT__/${PORT:-10000}/" /etc/nginx/nginx-site.conf.template > /etc/nginx/sites-available/default

php-fpm -D
exec nginx -g 'daemon off;'
