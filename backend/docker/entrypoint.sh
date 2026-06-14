#!/bin/sh

PORT="${PORT:-8000}"
export PORT

echo "boot: PORT=$PORT" >&2

mkdir -p storage/app/public storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
php artisan migrate --force
php artisan db:seed --force
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage/app/public storage bootstrap/cache
php artisan storage:link --force 2>/dev/null || php artisan storage:link 2>/dev/null || true

echo "starting server on 0.0.0.0:$PORT" >&2
exec php artisan serve --host=0.0.0.0 --port="$PORT" --no-reload
