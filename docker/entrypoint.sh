#!/usr/bin/env bash
set -e

cd /var/www/html

# Ensure .env exists (use the docker template baked into the image)
if [ ! -f .env ]; then
    cp .env.docker .env
fi

# Generate an app key if one isn't set yet
if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# Wait for the database to accept connections before booting
echo "Waiting for database at ${DB_HOST}:${DB_PORT} ..."
until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent 2>/dev/null; do
    sleep 2
done
echo "Database is up."

# Cache config/routes/views for a faster boot (non-fatal)
php artisan storage:link 2>/dev/null || true
php artisan config:clear || true
php artisan view:cache || true

exec "$@"
