FROM php:8.0-apache

# Installer les extensions nécessaires
RUN docker-php-ext-install mysqli

# Copier le code
COPY . /var/www/html/

# Créer le dossier uploads
RUN mkdir -p /var/www/html/uploads
RUN chmod 777 /var/www/html/uploads

# Vulnérabilité : Permissions trop larges
RUN chmod 777 /var/www/html/

EXPOSE 80
