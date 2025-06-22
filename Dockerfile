# Use PHP CLI version
FROM php:8.1-cli

# Install ffmpeg
RUN apt-get update && apt-get install -y ffmpeg

# App location
WORKDIR /app

# Copy everything
COPY . .

# Start PHP script
CMD ["php", "stream.php"]
