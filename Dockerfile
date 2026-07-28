FROM php:8.3-cli

# System deps for PHP extensions + Node.js 20
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring gd zip bcmath exif intl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
EXPOSE 80 5173

# The SSR daemon runs in a supervision loop and is decoupled from storage:link
# with `;` (not `&&`): a storage:link failure must never stop SSR from starting,
# and a crashed daemon must come back on its own — otherwise the site silently
# serves the client-rendered shell until someone notices.
CMD ["sh", "-c", "php artisan storage:link --force || true; : > storage/logs/ssr.log; (while true; do php artisan inertia:start-ssr >> storage/logs/ssr.log 2>&1; echo '[ssr] daemon exited, restarting in 5s' >> storage/logs/ssr.log; sleep 5; done &) ; php artisan serve --host=0.0.0.0 --port=80"]
