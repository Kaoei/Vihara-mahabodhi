#!/bin/sh
set -e

# Salin .env.example jika .env belum ada
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Pastikan default DB adalah sqlite bila belum diset
if [ -z "$DB_CONNECTION" ]; then
    export DB_CONNECTION=sqlite
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' /var/www/html/.env
fi

# Generate APP_KEY jika belum terisi
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
        php artisan key:generate --force --no-interaction
    fi
fi

# Buat database sqlite jika belum ada
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Pastikan permission folder tepat
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Jalankan migrasi dan seeder bila belum ada tabel
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction || true

# Link storage publik jika belum terhubung
php artisan storage:link || true

# Optimasi cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan Apache di foreground
exec apache2-foreground
