ARG PHP_VERSION=8.3

FROM wordpress:php${PHP_VERSION}

RUN pecl install redis xdebug && docker-php-ext-enable xdebug redis;

COPY --from=composer /usr/bin/composer /usr/bin/composer
