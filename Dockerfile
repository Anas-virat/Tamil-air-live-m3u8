# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Install FFmpeg
RUN apt-get update && apt-get install -y ffmpeg curl unzip

# Enable .htaccess and mod_rewrite
RUN a2enmod rewrite

# Set working dir
WORKDIR /var/www/html

# Copy everything to container
COPY . .

# Set proper permissions
RUN chmod -R 777 /var/www/html

# Expose HTTP port
EXPOSE 80
