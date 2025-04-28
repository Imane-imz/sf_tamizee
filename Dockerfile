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

# Définir le dossier de travail
WORKDIR /app

# --- Optimisation du cache Docker ---

# 1. Copier uniquement les fichiers de dépendances
COPY composer.json composer.lock symfony.lock* ./
COPY package.json yarn.lock* ./

# 2. Installer les dépendances PHP et JS (si les fichiers n'ont pas changé, Docker utilisera le cache)
RUN composer install --no-dev --optimize-autoloader
RUN yarn install

# 3. Copier ensuite tout le projet (le code Symfony)
COPY . .

# 4. Compiler les assets (JS/CSS)
RUN yarn build

# Exposer le port HTTP
EXPOSE 8000

# Lancer le serveur Symfony en mode production
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
