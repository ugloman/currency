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

COPY docker/php.ini /usr/local/etc/php/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /usr/src/currency

RUN composer install

ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh /usr/bin/wait-for-it
RUN chmod +x /usr/bin/wait-for-it

ENTRYPOINT ["/usr/bin/wait-for-it", "-t", "20", "rabbitmq:5672", "--", "php", "bin/console", "rabbitmq:consumer", "xml_currency_rates"]