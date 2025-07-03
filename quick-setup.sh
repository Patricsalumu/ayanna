#!/bin/bash

# Configuration automatique pour Ayanna
# Base de données: conquerorstudent_wp208
# Utilisateur: conquerorstudent_cm

echo "=== Configuration automatique Ayanna ==="

# Demander seulement l'URL du sous-domaine
read -p "URL de votre sous-domaine (ex: https://ayanna.conqueror.com): " APP_URL

# Créer le fichier .env directement avec les bonnes informations
cat > .env << 'EOF'
APP_NAME=Ayanna
APP_ENV=production
APP_KEY=base64:D0iJWVus9qxSSXrdCp4z0Q9bQkOw4xUh35xttBmMHuM=
APP_DEBUG=false
APP_URL=https://ayanna.conqueror.com

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=conquerorstudent_wp208
DB_USERNAME=conquerorstudent_cm
DB_PASSWORD=FCKQ3RCwgQal

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@ayanna.com
MAIL_FROM_NAME="Ayanna"
EOF

# Remplacer l'URL dans le fichier
sed -i "s|https://ayanna.conqueror.com|$APP_URL|g" .env

echo "✅ Fichier .env créé avec succès"
echo ""
echo "Configuration utilisée :"
echo "- Base de données: conquerorstudent_wp208"
echo "- Utilisateur: conquerorstudent_cm"
echo "- URL: $APP_URL"
echo ""
echo "Génération d'une nouvelle clé d'application..."
php artisan key:generate --force

echo "✅ Configuration terminée"
echo "Prochaine étape: bash deploy.sh"
