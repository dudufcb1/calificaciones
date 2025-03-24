FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Install dependencies including libzip (not just libzip-dev)
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    curl \
    zip \
    unzip \
    git \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libpng \
    libzip-dev \
    libzip   # Add this line to install the actual libzip library

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql bcmath gd zip

# Copy application files
COPY . .

# Install dependencies with zip extension req ignored
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-zip

# Generate APP_KEY
RUN php artisan key:generate

# Clear composer cache
RUN composer clear-cache

# Adjust file permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]