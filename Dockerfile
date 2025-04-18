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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers PHP
COPY . .

# Installer les dépendances PHP dans le conteneur
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Port utilisé par le serveur PHP
EXPOSE 80

# Démarrer le serveur PHP intégré
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
