FROM php:8.2-apache

# Install PDO MySQL extension for database connectivity
RUN docker-php-ext-install pdo pdo_mysql

# Configure Apache to listen on the port Render dynamically provides (default: 10000)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Set working directory
WORKDIR /var/www/html

# Copy all project files to the container
COPY . .

# Expose port 10000
EXPOSE 10000

# Start Apache in the foreground
CMD ["apache2-foreground"]
