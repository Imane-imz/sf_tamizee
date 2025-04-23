FROM php:8.2-cli

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Ajouter le dossier comme sûr pour Git
RUN git config --global --add safe.directory /var/www/html

# Installer les dépendances PHP sans scripts (comme cache:clear)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# (Optionnel) Vous pouvez exécuter le cache:clear dans le CMD/ENTRYPOINT une fois la DB disponible
# CMD php bin/console cache:clear --env=prod

# Commande de démarrage
CMD php -S 0.0.0.0:8000 -t public
