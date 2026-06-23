FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Increase PHP limits for file uploads (default is 2M which breaks uploads)
RUN echo "upload_max_filesize=30M\npost_max_size=32M\nmax_execution_time=120\nmemory_limit=256M" > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD php -S 0.0.0.0:8080 -t public
