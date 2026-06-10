FROM php:8.1-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules used by routing and security headers
RUN a2enmod rewrite headers

# Update Apache config to allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN printf "ServerName localhost\nServerTokens Prod\nServerSignature Off\n" > /etc/apache2/conf-available/zz-security-hardening.conf \
    && a2enconf zz-security-hardening
RUN printf "expose_php=Off\ndisplay_errors=Off\ndisplay_startup_errors=Off\nlog_errors=On\nsession.use_strict_mode=1\nsession.use_only_cookies=1\nsession.cookie_httponly=1\nsession.cookie_samesite=Lax\n" > /usr/local/etc/php/conf.d/security-hardening.ini

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Adjust permissions
RUN chown -R www-data:www-data /var/www/html
