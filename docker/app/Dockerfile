# PHP 8.4 runtime shared by the app, queue-worker, and scheduler services.
FROM php:8.4-cli-alpine

# pdo_pgsql  -> PostgreSQL access
# pcntl      -> graceful queue worker signal handling
# bcmath     -> precise numeric operations for analytics aggregation
# redis      -> phpredis client for cache and queues
RUN apk add --no-cache postgresql-dev linux-headers \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
