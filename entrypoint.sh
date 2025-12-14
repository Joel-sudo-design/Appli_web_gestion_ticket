#!/bin/bash
set -e

echo "🚀 Démarrage de l'application..."

# -----------------------------
# Vérifications minimales
# -----------------------------
if [ "$APP_ENV" != "prod" ]; then
  echo "⚠️  APP_ENV n'est pas défini sur 'prod'"
fi

if [ -z "$APP_SECRET" ] || [ ${#APP_SECRET} -lt 32 ]; then
  echo "❌ APP_SECRET manquant ou trop court"
  exit 1
fi

if [ ! -f "vendor/autoload.php" ]; then
  echo "❌ vendor/autoload.php manquant"
  exit 1
fi

# -----------------------------
# Préparer les dossiers runtime
# -----------------------------
mkdir -p var/cache var/log var/sessions
chmod -R 775 var

# -----------------------------
# Lancer FrankenPHP / Caddy
# -----------------------------
echo "✅ Application prête"
exec "$@"
