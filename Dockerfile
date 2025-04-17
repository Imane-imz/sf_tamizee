# Utilise une image PHP avec Composer et Apache préinstallés
FROM php:8.2-apache

# Installe les extensions nécessaires pour Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Active le mod_rewrite d’Apache pour Symfony
RUN a2enmod rewrite

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définit le DocumentRoot Apache vers /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Modifie la config Apache pour utiliser le nouveau DocumentRoot
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Copie le projet dans le conteneur
COPY . /var/www/html/

# Donne les bons droits
RUN chown -R www-data:www-data /var/www/html

# Installe les dépendances (et corrige les erreurs root + symfony/runtime)
RUN composer install --no-dev --optimize-autoloader \
    && composer require symfony/runtime

# Configuration Apache supplémentaire (permissions dossier public)
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# Port exposé
EXPOSE 80
