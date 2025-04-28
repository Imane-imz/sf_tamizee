# Utiliser une image PHP officielle
FROM php:8.2-fpm

# Installer quelques outils nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier ton application
WORKDIR /app
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installer les dépendances JS/CSS et compiler
RUN yarn install
RUN yarn build

# Exposer le port (optionnel selon serveur utilisé)
EXPOSE 8000

# Commande pour démarrer Symfony (exemple avec PHP built-in server)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
