FROM php:8.2-cli

# Installer les dépendances système et les extensions PHP nécessaires
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

# Copier les fichiers de projet
COPY . .

# Installer les dépendances PHP
RUN composer clear-cache \
    && composer install --no-dev --optimize-autoloader --prefer-dist

# Exposer le port utilisé par le serveur PHP
EXPOSE 80

# Lancer les migrations puis le serveur PHP
CMD bash -c "php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:80 -t public"
