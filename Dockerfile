# Base image
FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# Copy Symfony application files to the container
COPY . /var/www/html

# Install Symfony dependencies
RUN composer install --no-scripts --no-autoloader

# Run Symfony build commands
RUN composer dump-autoload --optimize && \
    composer run-script --no-dev post-install-cmd && \
    composer run-script --no-dev post-update-cmd

# Expose port
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
