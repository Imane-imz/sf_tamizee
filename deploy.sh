#!/bin/bash

echo "🔁 Pulling latest code from Git..."
git pull origin main

echo "📦 Installing frontend dependencies..."
npm install --legacy-peer-deps

echo "🛠 Building frontend assets..."
npm run build

echo "🎼 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "🧬 Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "🧹 Clearing and warming up cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

echo "📤 Deploying public assets to public_html/..."
rsync -av --delete ./public/ ~/public_html/

echo "✅ Deployment completed."
