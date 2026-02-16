FROM php:8.1-fpm

WORKDIR /app

# System tools + libs for PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev \
    libpq-dev \
 && docker-php-ext-install bcmath zip pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /app/

# Install dependencies
RUN composer install --no-dev --no-scripts --no-autoloader \
    && composer dump-autoload --optimize

# Nginx user (optional, for FPM)
RUN useradd -m -s /bin/bash www-data || true

EXPOSE 9000

CMD ["php-fpm"]

