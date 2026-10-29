# Use the official PHP 8.4 image with Apache web server
FROM php:8.4-apache

# Install required system dependencies, PHP extensions, and Node.js/NPM
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite (required for Laravel routing)
RUN a2enmod rewrite

# Point Apache to Laravel's /public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy your GitHub repository files into the container
COPY . .

# Install Laravel's backend dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Install Node dependencies and compile frontend Vite assets
RUN npm install
RUN npm run build

# Grant necessary permissions to Laravel's storage and cache folders
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
