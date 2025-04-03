#!/bin/bash
set -e

echo "Attente du port 3306..."
/wait-for-it.sh db:3306 -t 30

echo "Création de la base si besoin"
php bin/console doctrine:database:create --if-not-exists

echo "Migration de la base si besoin"
php bin/console doctrine:migrations:migrate --no-interaction

echo "Execution des autres scripts"
composer run-script post-install-cmd

echo "Build des assets..."
yarn install && yarn build

echo "Ajustement des permissions pour www-data..."
chown -R www-data:www-data /var/www/appli_web

echo "Lancement du serveur apache"
exec apache2-foreground
