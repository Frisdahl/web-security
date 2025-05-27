FROM php:8.2-apache

# Enable Apache rewrite if needed
RUN a2enmod rewrite

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy all project files into Apache root
COPY ./app /var/www/html/

# Set proper permissions (optional)
RUN chown -R www-data:www-data /var/www/html

# Expose the default Apache port
EXPOSE 80
