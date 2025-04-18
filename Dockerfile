FROM php:8.2-apache

# Installe les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip gd opcache

# Active mod_rewrite
RUN a2enmod rewrite

# Configure Apache pour pointer vers /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copie les fichiers dans le container
COPY . /var/www/html/

# Donne les bons droits
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Lance les commandes Symfony
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader \
    && php bin/console cache:clear \
    && php bin/console assets:install public
