# Decor Urban — PHP 8.3 FPM image for Laravel 13
FROM php:8.3-fpm-bookworm

# System deps required to build the PHP extensions below
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        curl \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Core PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        intl \
        exif \
        pcntl \
        opcache

# Redis extension via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer (copied from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Custom php.ini tweaks
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Match the host user so files written from the container are owned correctly.
# UID/GID are overridable as build args (default 1000 like most Linux/macOS setups).
ARG UID=1000
ARG GID=1000
RUN groupmod -o -g "${GID}" www-data \
    && usermod -o -u "${UID}" -g "${GID}" www-data

WORKDIR /var/www/html

# Run as the host-matched user
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
