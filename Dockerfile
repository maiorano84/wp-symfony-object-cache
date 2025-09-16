ARG PHP_VERSION=8.3

FROM wordpress:php${PHP_VERSION}

RUN apt-get update && \
    apt-get install -y zlib1g-dev libzip-dev unzip && \
    pecl install redis xdebug && \
    docker-php-ext-enable xdebug redis;

COPY --from=composer /usr/bin/composer /usr/bin/composer
