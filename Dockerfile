FROM php:8.1-apache

# Install ffmpeg
RUN apt-get update && apt-get install -y ffmpeg

# Enable Apache modules
RUN a2enmod rewrite

# Enable directory listing for HLS output
RUN echo '<Directory /var/www/html>' \
        '\nOptions +Indexes +FollowSymLinks' \
        '\nAllowOverride All' \
        '\nRequire all granted' \
        '\n</Directory>' >> /etc/apache2/apache2.conf

# Set working directory to web root
WORKDIR /var/www/html

# Copy your PHP files (stream.php, etc.)
COPY . .

# 🔥 This runs your HLS script in background + Apache as web server
CMD php stream.php & apache2-foreground
