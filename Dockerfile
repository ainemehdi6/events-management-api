# Use PHP 8.1 FPM as the base image
FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    default-mysql-client \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql intl zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set permissions for the Symfony CLI
RUN chmod +x /usr/local/bin/symfony

# Copy application source code
COPY . /var/www/html

# Install application dependencies
RUN composer install --no-scripts --no-autoloader

# Generate application autoloader
RUN composer dump-autoload --optimize

# Set permissions for the application
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public

# Expose the FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
