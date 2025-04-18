FROM php:8.2-cli

# Installer des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier l'application dans le conteneur
WORKDIR /var/www/html
COPY . .

# Modifier les permissions des fichiers
RUN chown -R www-data:www-data /var/www/html

# Installer les dépendances avec Composer
RUN composer install --no-dev --optimize-autoloader --verbose

# Exécuter les autres commandes Symfony (par exemple, cache:clear)
RUN php bin/console cache:clear && php bin/console assets:install public

# Définir le port d'écoute
EXPOSE 80

# Démarrer le serveur PHP
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
