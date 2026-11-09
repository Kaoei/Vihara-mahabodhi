#!/bin/sh
set -e

# Salin .env.example jika .env belum ada
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Pastikan default DB adalah sqlite bila belum diset
if [ -z "$DB_CONNECTION" ]; then
    export DB_CONNECTION=sqlite
fi

# Tulis DB_CONNECTION=sqlite ke .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' /var/www/html/.env 2>/dev/null || true

# Generate APP_KEY jika belum terisi
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
        php artisan key:generate --force --no-interaction || true
    fi
fi

# Buat database sqlite jika belum ada
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Pastikan permission folder tepat
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Jalankan migrasi dan seeder bila belum ada tabel (jangan sampai menggagalkan container startup)
php artisan migrate --force --no-interaction || true
php artisan db:seed --force --no-interaction || true

# Link storage publik jika belum terhubung
php artisan storage:link || true

# Bersihkan dan cache konfigurasi Laravel
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Sesuaikan port Apache jika platform menyediakan environment variable $PORT
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf
fi

# Jalankan Apache di foreground
exec apache2-foreground
