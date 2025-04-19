FROM php:8.2-cli

# Définir les variables d’environnement
ENV APP_ENV=prod
ENV APP_DEBUG=0

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

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers Composer d'abord
COPY composer.json composer.lock ./

# Installer les dépendances PHP en production
RUN composer install --no-dev --optimize-autoloader

# Copier le reste du projet
COPY . .

# Commande de démarrage : afficher les extensions PHP, effectuer les migrations, vider le cache, puis lancer le serveur
CMD php -m && \
    php bin/console doctrine:migrations:migrate --no-interaction && \
    php bin/console cache:clear --no-warmup && \
    php -S 0.0.0.0:80 -t public
