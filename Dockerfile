# Étape 1 : Builder les dépendances
FROM composer:latest AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Étape 2 : Image finale avec PHP
FROM php:8.2-cli

# Installer les dépendances système PHP
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip libpq-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && apt-get clean

# Copier Composer depuis l'étape précédente (facultatif si on n'a pas besoin de le réutiliser ensuite)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le code source
COPY . .

# Copier les vendor depuis l'étape "vendor"
COPY --from=vendor /app/vendor ./vendor

# Rendre git safe si besoin
RUN git config --global --add safe.directory /var/www/html

# Ne PAS faire `cache:clear` ici ! À faire uniquement dans le docker-compose ou dans l'entrypoint.sh

# Commande de démarrage
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
