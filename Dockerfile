FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite (not strictly required but useful)
RUN a2enmod rewrite

# Security hardening: disable dangerous functions site-wide.
# The panel and bot scripts never need to spawn shell processes,
# so this blocks the most common remote-code-execution abuse paths.
RUN { \
    echo "disable_functions=exec,shell_exec,system,passthru,proc_open,popen,pcntl_exec,proc_get_status,proc_nice,proc_terminate"; \
    echo "expose_php=Off"; \
    echo "display_errors=Off"; \
    echo "log_errors=On"; \
    echo "upload_max_filesize=15M"; \
    echo "post_max_size=16M"; \
    } > /usr/local/etc/php/conf.d/hardening.ini

# Apache should serve the public/ folder as document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . /var/www/html

# storage/ must be writable (SQLite DB + uploaded bot code + logs)
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80
