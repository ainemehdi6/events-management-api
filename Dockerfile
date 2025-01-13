FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
