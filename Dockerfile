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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie d'abord composer.json et composer.lock
COPY composer.json composer.lock ./

# Installation des dépendances (après nettoyage + mise à jour)
RUN composer install --no-dev --optimize-autoloader

# Puis copie du reste du projet
COPY . .

# Commande de démarrage : migrations + serveur PHP
CMD php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:80 -t public
