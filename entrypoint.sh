#!/bin/bash
set -e

echo "⏳ Attente du port 3306..."
/wait-for-it.sh db:3306 -t 30

php bin/console doctrine:database:create --if-not-exists

php bin/console doctrine:migrations:migrate --no-interaction

composer run-script post-install-cmd

echo "⏳ Build des assets..."
yarn install && yarn build

exec apache2-foreground
