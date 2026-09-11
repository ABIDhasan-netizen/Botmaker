FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Security hardening
RUN { \
    echo "disable_functions=exec,shell_exec,system,passthru,proc_open,popen,pcntl_exec,proc_get_status,proc_nice,proc_terminate"; \
    echo "expose_php=Off"; \
    echo "display_errors=Off"; \
    echo "log_errors=On"; \
    echo "upload_max_filesize=15M"; \
    echo "post_max_size=16M"; \
    } > /usr/local/etc/php/conf.d/hardening.ini

WORKDIR /var/www/html
COPY . /var/www/html

# Create storage directory and set correct permissions
RUN mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80
