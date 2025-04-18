FROM php:8.2-apache

# Installe les dépendances
RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install intl pdo pdo_mysql opcache

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie les fichiers de l'app
COPY . /var/www/html/

# Active le rewrite module d'Apache
RUN a2enmod rewrite

# Change le répertoire de travail
WORKDIR /var/www/html/

# Droits + nettoyage
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html/
