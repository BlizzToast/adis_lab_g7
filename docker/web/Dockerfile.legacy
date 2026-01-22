# Use Debian as base image (similar to Ubuntu used in the exercise)
FROM debian:bookworm-slim

# Install Nginx, PHP-FPM, Redis server and build dependencies for php-redis
RUN apt-get update && \
    apt-get install -y \
    nginx \
    php-fpm \
    php-cli \
    php-sqlite3 \
    sqlite3 \
    redis-server \
    redis-tools \
    php-redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy Nginx configuration (use 'local' config for development without SSL)
COPY docker/nginx/default.local.conf /etc/nginx/sites-enabled/default

# Copy webapp files
COPY src/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Redis, PHP-FPM and Nginx
CMD ["sh", "-c", "redis-server --daemonize yes && service php8.2-fpm start && nginx -g 'daemon off;'"]
