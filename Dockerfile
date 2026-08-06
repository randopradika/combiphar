# nginx + PHP-FPM, supervised.
#
# Replaces `php artisan serve`, which Laravel documents as a development server
# only. Two concrete problems it had in production:
#   * the PHP built-in server EXECUTES any .php in the document root, and
#     public/storage symlinks to the CMS upload tree — one writable script
#     there was remote code execution;
#   * PHP_CLI_SERVER_WORKERS capped the entire site at 10 concurrent requests,
#     with no request timeouts or body-size limits.
FROM php:8.3-fpm

# System deps for the PHP extensions, plus nginx/supervisor and Node.js 20
# (Vite build + the Inertia SSR daemon).
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates nginx supervisor \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring gd zip bcmath exif intl opcache \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP-FPM listens on loopback only. nginx is in the same container and the port
# is never published, so FPM must not be reachable from the network — an
# exposed FastCGI port is a direct path to arbitrary file read/execute.
RUN sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf

COPY docker/nginx-site.conf   /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf  /etc/supervisor/conf.d/app.conf
COPY docker/php-hardening.ini /usr/local/etc/php/conf.d/php-hardening.ini
COPY docker/entrypoint.sh     /usr/local/bin/entrypoint.sh

# Debian ships a default vhost on :80 that would shadow ours.
RUN rm -f /etc/nginx/sites-enabled/default \
    && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
EXPOSE 80 5173

CMD ["/usr/local/bin/entrypoint.sh"]
