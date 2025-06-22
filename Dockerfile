FROM php:8.1-apache

# Install ffmpeg and enable rewrite module
RUN apt-get update && apt-get install -y ffmpeg curl unzip && a2enmod rewrite

# Enable directory listing and override
RUN echo '<Directory /var/www/html>' \
        '\nOptions +Indexes +FollowSymLinks' \
        '\nAllowOverride All' \
        '\nRequire all granted' \
        '\n</Directory>' >> /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy app files
COPY . .

# Set permissions for Apache to read/write
RUN chmod -R 777 /var/www/html

EXPOSE 80
