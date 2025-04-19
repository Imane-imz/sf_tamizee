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

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers nécessaires pour l'installation
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Puis copier le reste
COPY . .

# Exposer le port
EXPOSE 80

# Commande de lancement
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
