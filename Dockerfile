FROM php:8.2-fpm

# Installer outils nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer Node.js et Yarn
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install --global yarn

# Copier ton application
WORKDIR /app
COPY . .

# Installer dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installer dépendances JS/CSS et builder
RUN yarn install
RUN yarn build

# Exposer le port (optionnel selon serveur utilisé)
EXPOSE 8000

# Démarrer Symfony
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
