FROM php:8.4-apache

# Installation des dépendances système
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        build-essential=12.9 \
        curl=7.88.* \
        zlib1g-dev=1:1.2.* \
        g++=4:12.* \
        libicu-dev=72.* \
        zip=3.* \
        libzip-dev=1.7.* \
        libpng-dev=1.6.* \
        libjpeg-dev=1:2.* \
        libwebp-dev=1.2.* \
        libfreetype-dev=2.12.* \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql intl opcache zip

# APCU
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# GD + EXIF
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd exif

# Apache modules (corrigé doublons)
RUN a2enmod rewrite ssl socache_shmcb

WORKDIR /var/www

# Installation Composer
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Permissions
RUN chown -R www-data:www-data /var/www