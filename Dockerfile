FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS composer_deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libpq-dev libzip-dev unzip python3 python3-pip poppler-utils \
    && docker-php-ext-install intl pdo_pgsql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY . .
COPY --from=composer_deps /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
RUN pip3 install --break-system-packages --no-cache-dir pypdf \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && chown -R www-data:www-data storage bootstrap/cache
COPY docker/entrypoint.sh /usr/local/bin/aman-entrypoint
RUN chmod +x /usr/local/bin/aman-entrypoint
ENV PORT=8080
EXPOSE 8080
ENTRYPOINT ["aman-entrypoint"]
CMD ["apache2-foreground"]
