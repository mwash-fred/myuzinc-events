FROM php:7.4-fpm

WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    libonig-dev

#Set php environment
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11"

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql gd \
    && docker-php-ext-install opcache \ 
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && apt-get remove libicu-dev icu-devtools -y

#Configure opcache
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/php-opocache-cfg.ini

# Set working directory
WORKDIR /var/www

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN chown -R www-data:www-data /var/www

# Copy the Symfony application code
COPY . /var/www/html

# Install application dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000
CMD ["php-fpm"]
