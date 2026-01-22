FROM php:8.2-fpm-alpine

# Install PHP extensions: pdo_sqlite and redis
# Using install-php-extensions for simplicity (handles dependencies automatically)
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_sqlite redis

WORKDIR /var/www/html

# Copy application code
COPY src/ .

# Set proper permissions for the data directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
