FROM php:8.2-cli

# Installer les dépendances système nécessaires à Symfony et à PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers PHP et composer.json dans le conteneur
COPY . .

# Modifier les permissions des fichiers (si nécessaire)
RUN chown -R www-data:www-data /var/www/html

# Installer les dépendances PHP via Composer (y compris Symfony Runtime si besoin)
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Ajouter symfony/runtime si nécessaire (dans le cas où ce n'est pas déjà fait)
RUN composer require symfony/runtime

# Exécuter les autres commandes Symfony
RUN php bin/console cache:clear && php bin/console assets:install public

# Exposer le port d'écoute
EXPOSE 80

# Démarrer le serveur PHP intégré
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
