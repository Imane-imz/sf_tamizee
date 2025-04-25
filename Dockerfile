# Étape 1 : base PHP avec Composer et Node
FROM node:18-slim AS build

# Installe PHP et Composer
RUN apt-get update && \
    apt-get install -y php php-cli php-mbstring php-xml php-curl php-sqlite3 unzip curl git && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Copie les fichiers et build les assets
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN yarn install
RUN yarn encore production

# Étape 2 : serveur PHP pour production
FROM php:8.2-cli
WORKDIR /app
COPY --from=build /app /app

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
