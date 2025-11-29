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

RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Create user/group (1000:1000)
RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D -u 1000 www

# php-fpm config override (logs to stderr + run workers as www)
COPY ./docker/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY ./docker/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# Entrypoint
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ✅ IMPORTANT: do NOT switch USER here.
# FPM master should start as root, but workers will run as www via pool config.

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm", "-F"]
