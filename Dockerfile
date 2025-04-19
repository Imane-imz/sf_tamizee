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
    libonig-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers de projet
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Assurer que symfony/runtime est installé
RUN composer require symfony/runtime

# Supprimer le DebugBundle du kernel en prod (sécurité en cas de fallback)
RUN sed -i '/DebugBundle/d' config/bundles.php

# Port utilisé par le serveur PHP
EXPOSE 80

# Lancer le serveur PHP
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
