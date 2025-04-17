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

# Copie le projet dans le conteneur
COPY . /var/www/html/

# Définit le dossier public comme racine du serveur
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Installe les dépendances
RUN composer install --no-dev --optimize-autoloader

# Configuration Apache pour Symfony
RUN echo '<Directory /var/www/html/public>
    AllowOverride All
</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# Port exposé
EXPOSE 80
