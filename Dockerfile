FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libpq-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar TODO el proyecto
COPY . .

# Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Node
RUN npm install

# Vite
RUN npm run build

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan storage:link || true && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000}