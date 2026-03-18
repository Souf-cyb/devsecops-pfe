FROM php:7.4-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update && apt-get install -y libpng-dev zip unzip curl \
    && docker-php-ext-install gd

RUN a2enmod rewrite

COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads
RUN mkdir -p /var/www/html/assets/uploads && chmod 777 /var/www/html/assets/uploads

EXPOSE 80