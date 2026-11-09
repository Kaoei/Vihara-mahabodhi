# Gunakan official image PHP 8.3 dengan Apache
FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies sistem yang dibutuhkan untuk Laravel, SQLite, dan manipulasi gambar/file
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi Apache DocumentRoot ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin file proyek ke dalam container
COPY . /var/www/html

# Install dependensi PHP via Composer untuk production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Berikan izin eksekusi pada script entrypoint
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Expose port 80 untuk Apache
EXPOSE 80

# Jalankan entrypoint script saat container start
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

