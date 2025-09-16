# Use the official PHP + Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite (often needed for clean URLs)
RUN a2enmod rewrite

# Copy your PHP code into the Apache web root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Make sure files have the right permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 (Apache default)
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]