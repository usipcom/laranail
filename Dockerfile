FROM php:8.1-cli

RUN apt-get update \
    && apt-get install --assume-yes --quiet \
    apt-utils vim curl git \
    libzip-dev zip libicu-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
    --with-jpeg --with-freetype \
    && docker-php-ext-install zip gd bcmath exif intl

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
