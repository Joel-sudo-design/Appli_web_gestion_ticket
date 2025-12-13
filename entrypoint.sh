#!/bin/bash
set -e

echo "🚀 Démarrage de l'application en production..."

# Vérifier que les variables obligatoires sont définies
if [ "$APP_ENV" != "prod" ]; then
    echo "⚠️  Attention: APP_ENV n'est pas défini sur 'prod'"
fi

# Vérifier que le secret EXISTE et n'est pas vide
if [ -z "$APP_SECRET" ] || [ ${#APP_SECRET} -lt 32 ]; then
    echo "❌ ERREUR: APP_SECRET manquant ou trop court (min 32 chars)"
    exit 1
fi

# Extraire les infos de connexion depuis DATABASE_URL
DB_HOST=$(echo $DATABASE_URL | sed -n 's|.*@\([^:]*\):.*|\1|p')
DB_PORT=$(echo $DATABASE_URL | sed -n 's|.*:\([0-9]*\)/.*|\1|p')
DB_USER=$(echo $DATABASE_URL | sed -n 's|.*://\([^:]*\):.*|\1|p')
DB_PASS=$(echo $DATABASE_URL | sed -n 's|.*://[^:]*:\([^@]*\)@.*|\1|p')

# Attendre que la base de données soit prête
echo "⏳ Attente de la base de données ($DB_HOST:$DB_PORT)..."
MAX_TRIES=60
COUNTER=0

until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" --silent 2>/dev/null; do
  COUNTER=$((COUNTER+1))
  if [ $COUNTER -gt $MAX_TRIES ]; then
    echo "❌ Impossible de se connecter à la base de données après ${MAX_TRIES} tentatives"
    exit 1
  fi
  echo "  Tentative $COUNTER/$MAX_TRIES..."
  sleep 3
done
echo "✅ Base de données prête !"

# Vérification des dépendances (normalement déjà installées dans l'image)
if [ ! -f "vendor/autoload.php" ]; then
  echo "❌ ERREUR: Les dépendances Composer ne sont pas installées !"
  exit 1
fi

# Création de la base de données si nécessaire
echo "🗄️  Vérification de la base de données..."
php bin/console doctrine:database:create --if-not-exists --no-interaction || {
    echo "⚠️  La base de données existe déjà ou erreur lors de la création (ignoré)"
}

# Application des migrations (sans fixtures en prod)
echo "📄 Application des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || {
    echo "❌ ERREUR: Échec de l'application des migrations"
    exit 1
}

# Vérifier le schéma de base de données
echo "🔍 Vérification du schéma de base de données..."
php bin/console doctrine:schema:validate || {
    echo "⚠️  Le schéma de base de données n'est pas synchronisé avec les entités"
}

# Nettoyage du cache (forcé en production)
echo "🧹 Nettoyage du cache..."
php bin/console cache:clear --no-warmup --no-optional-warmers || {
    echo "❌ ERREUR: Échec du nettoyage du cache"
    exit 1
}

# Warmup du cache
echo "🔥 Préchauffage du cache..."
php bin/console cache:warmup || {
    echo "❌ ERREUR: Échec du warmup du cache"
    exit 1
}

# Création des répertoires supplémentaires pour FrankenPHP/Caddy
echo "📁 Création des répertoires manquants..."
mkdir -p var/caddy
chmod -R 777 var/caddy

# Vérification de la configuration Symfony
echo "🔧 Vérification de la configuration..."
php bin/console about || true

# Vérifier les permissions
echo "🔐 Vérification des permissions..."
if [ ! -w "var/cache" ] || [ ! -w "var/log" ]; then
    echo "⚠️  Attention: Problème de permissions sur var/cache ou var/log"
fi

# Créer un fichier de santé pour le healthcheck
mkdir -p public
echo "OK" > public/health

echo ""
echo "╔════════════════════════════════════════╗"
echo "║ ✅ Application prête en PRODUCTION !   ║"
echo "╠════════════════════════════════════════╣"
echo "║ 🌐 Port : 443 (HTTPS)                  ║"
echo "║ 📊 Environnement : ${APP_ENV}          ║"
echo "║ 🐛 Debug : ${APP_DEBUG}                ║"
echo "║ 🚀 OPcache : Activé + JIT              ║"
echo "║ 💾 APCu : Activé (512M)                ║"
echo "║ 🔒 SSL : Let's Encrypt auto            ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Afficher des avertissements si nécessaire
if [ "$APP_DEBUG" = "1" ]; then
    echo "⚠️  ATTENTION: Le mode debug est activé en production !"
fi

# Lancer la commande passée en argument (FrankenPHP)
exec "$@"