FROM php:7.4-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update && apt-get install -y libpng-dev && docker-php-ext-install gd
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads
EXPOSE 80