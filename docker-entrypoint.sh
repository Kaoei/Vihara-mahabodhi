#!/bin/sh
set -e

# Buat database sqlite jika belum ada
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Pastikan folder storage dan database memiliki permission yang tepat
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Jalankan migrasi dan seeder bila belum ada tabel
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

# Link storage publik jika belum terhubung
php artisan storage:link || true

# Optimasi cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan Apache di foreground
exec apache2-foreground

