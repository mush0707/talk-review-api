FROM php:8.3-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    zlib-dev \
    linux-headers \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    intl \
    zip \
    opcache \
    pcntl \
    sockets
RUN pecl install redis \
    && docker-php-ext-enable redis
# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Create a non-root user (optional but good)
RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D -u 1000 www

# Permissions for Laravel runtime dirs
RUN mkdir -p storage bootstrap/cache && \
    chown -R www:www /var/www/html

USER www

# Keep container running
CMD ["php-fpm"]
