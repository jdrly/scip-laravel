ARG PHP_VERSION=8.4
FROM composer:2 AS composer-bin

FROM php:${PHP_VERSION}-cli

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install --yes --no-install-recommends git unzip \
    && rm -r /var/lib/apt/lists/*

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress

COPY . .

RUN composer check && composer audit --locked --no-interaction
