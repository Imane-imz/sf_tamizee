git pull origin main

npm install --legacy-peer-deps
npm run build

composer install
php bin/console d:m:m