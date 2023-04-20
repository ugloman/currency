FROM php:8.2-fpm

WORKDIR /usr/src/currency

RUN apt-get update && apt-get install -y --no-install-recommends apt-utils\
    libpq-dev \
    git \
    zlib1g-dev \
    zip \
    libzip-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-install zip \
    && docker-php-ext-install sockets

RUN rm -rf /var/lib/apt/lists/*

RUN pecl channel-update pecl.php.net \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY docker/php.ini /usr/local/etc/php/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /usr/src/currency

COPY docker/bin/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]