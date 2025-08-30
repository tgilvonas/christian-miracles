# PHP 8.2 with FPM (used with Nginx)
FROM php:8.4-fpm

# System dependencies required for extensions and composer
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    g++ \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Install Redis extension (common in Laravel projects)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Xdebug (optional, for debugging)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy Composer from official image
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source code into container
COPY . .

# Permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
