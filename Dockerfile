FROM php:8.2-fpm

# Installer tout ce qu'il faut
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpq-dev libicu-dev \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /app

# Copier les fichiers de dépendances
COPY composer.json composer.lock symfony.lock* ./

# Installer les dépendances PHP (ça va générer VENDOR)
RUN composer install --no-dev --optimize-autoloader

# Copier le reste
COPY . .

# Installer NodeJS + Yarn + Assets
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install --global yarn && \
    yarn install && \
    yarn build

# Exposer le port
EXPOSE 8000

# Lancer serveur PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
