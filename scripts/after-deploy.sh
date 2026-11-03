#!/usr/bin/env bash
# Runs on the live server after deploy.ps1 has unpacked a new version.
# Expects to be run from the project folder. Safe to run more than once.
set -euo pipefail

if [ ! -f artisan ]; then
    echo "after-deploy: run this from the project folder" >&2
    exit 1
fi

echo "== Server: $(php -r 'echo PHP_VERSION;') in $(pwd)"

# Never let a dev-server pointer reach production (it makes pages load CSS from a PC).
rm -f public/hot

if [ "${RUN_COMPOSER:-0}" = "1" ]; then
    if command -v composer >/dev/null 2>&1; then
        echo "== composer.json/lock changed: installing PHP packages"
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        echo "!! composer.json/lock changed but composer isn't available here - install packages manually" >&2
    fi
fi

echo "== Database migrations"
php artisan migrate --force

if [ ! -e public/storage ]; then
    php artisan storage:link
fi

echo "== Refreshing caches"
php artisan optimize:clear
php artisan optimize
php artisan filament:optimize

echo "== Deploy finished"
