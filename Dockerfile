FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    autoconf \
    bash \
    curl \
    g++ \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    make \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    nodejs \
    npm \
    supervisor

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo \
    pdo_pgsql \
    xml \
    zip

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install Node dependencies and build assets
RUN npm ci && npm run build && rm -rf node_modules

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy and register the bootstrap entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
