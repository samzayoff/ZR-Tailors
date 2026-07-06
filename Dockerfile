# syntax=docker/dockerfile:1

# ---------- Stage 1: Install PHP (Composer) dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ---------- Stage 2: Build frontend assets ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

# ---------- Stage 3: Final runtime image ----------
FROM php:8.2-cli

# Install system dependencies + PHP extensions
# ca-certificates is required for SSL connections to TiDB Cloud / managed MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    ca-certificates \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath intl \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor dependencies and built frontend assets from earlier stages
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Ensure Laravel's writable directories exist and have correct permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Start the Laravel app
# NOTE: php artisan serve is fine for small/low-traffic apps.
# For production-grade traffic, consider swapping to php-fpm + nginx instead.
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]