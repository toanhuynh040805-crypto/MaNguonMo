FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/openshop-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/openshop-entrypoint && chmod +x /usr/local/bin/openshop-entrypoint

EXPOSE 8000
ENTRYPOINT ["openshop-entrypoint"]
