ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install --yes --no-install-recommends git unzip \
    && rm -r /var/lib/apt/lists/*

WORKDIR /opt/scip-laravel

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-dev

COPY bin ./bin
COPY src ./src
COPY README.md ./README.md

RUN composer dump-autoload --no-dev --optimize \
    && chmod +x bin/scip-laravel

ENTRYPOINT ["/opt/scip-laravel/bin/scip-laravel"]
