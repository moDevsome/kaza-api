# --- Dockerfile for the PHP service ----
FROM php:8.3-fpm

CMD ["echo 'Set required extensions'"]
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

CMD ["echo 'Set required dependencies'"]
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    intl \
    zip \
    gd \
    pdo \
    pdo_mysql

# Required to test the database coonnection
RUN apk add --no-cache netcat-openbsd

WORKDIR /var/www
EXPOSE 9000

CMD ["php-fpm"]