# PHP + Apache image for deploying MemoCraft on Render (or any Docker host)
FROM php:8.2-apache

# PostgreSQL driver for PDO (Supabase)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Production PHP settings (hides PHP errors from visitors) and allow bigger image uploads
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf "upload_max_filesize=10M\npost_max_size=12M\n" > "$PHP_INI_DIR/conf.d/uploads.ini"

COPY . /var/www/html/

# Apache must be able to write uploaded product and profile images
RUN mkdir -p /var/www/html/admin/uploads /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html/admin/uploads /var/www/html/uploads

# Render tells the app which port to use through $PORT (defaults to 80 when run elsewhere)
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
    && sed -i "s/:80>/:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
