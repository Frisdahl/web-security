FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy SSL certificate (Aiven's CA cert)
COPY ./app/aiven-ca.pem /etc/ssl/certs/aiven-ca.pem

# Copy your application files
COPY ./app /var/www/html/

# Set permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for HTTP traffic
EXPOSE 80
