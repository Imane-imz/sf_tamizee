FROM php:8.2-fpm

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpq-dev libicu-dev \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /app

# Copier TOUT le projet
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installer NodeJS, Yarn et construire les assets
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install --global yarn && \
    yarn install && \
    yarn build

# Exposer le port 8000
EXPOSE 8000

# Lancer le serveur PHP interne
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
