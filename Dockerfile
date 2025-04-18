# Utilisation de l'image PHP officielle
FROM php:8.2-fpm

# Installe les dépendances nécessaires pour Composer et Symfony
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Installe Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définit le répertoire de travail
WORKDIR /var/www/html

# Copie le fichier composer.json et composer.lock
COPY composer.json composer.lock ./

# Installe les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Copie tout le projet dans le conteneur
COPY . .

# Exécute les commandes Symfony
RUN php bin/console cache:clear \
    && php bin/console assets:install public

# Déplace les droits après l'installation de Composer et la génération des dossiers
RUN chown -R www-data:www-data var vendor

# Définit le port d'écoute
EXPOSE 9000

# Lancer le serveur PHP intégré
CMD ["php-fpm"]
