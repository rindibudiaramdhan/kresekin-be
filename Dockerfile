FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        git \
        libpq-dev \
        linux-headers \
        oniguruma-dev \
        sqlite-dev \
        unzip \
        libxml2-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        mbstring \
        pdo_pgsql \
        pgsql \
        pdo_sqlite \
        sqlite3 \
        xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php-fpm"]
