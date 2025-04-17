#!/usr/bin/env bash
set -e

composer install --no-dev --optimize-autoloader
php bin/console cache:clear
php bin/console assets:install public
