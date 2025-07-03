#!/bin/bash

# Configuration rapide pour cPanel - Ayanna
echo "=== Configuration Ayanna pour cPanel ==="

# Valeurs par défaut basées sur votre configuration
DEFAULT_DB_NAME="conquerorstudent_wp208"
DEFAULT_DB_USER="conquerorstudent_cm"
DEFAULT_DB_PASS="FCKQ3RCwgQal"

# Demander les informations de base de données
echo "Informations de votre base de données MySQL :"
read -p "Nom de la base de données [$DEFAULT_DB_NAME]: " DB_NAME
DB_NAME=${DB_NAME:-$DEFAULT_DB_NAME}

read -p "Utilisateur de la base [$DEFAULT_DB_USER]: " DB_USER
DB_USER=${DB_USER:-$DEFAULT_DB_USER}

read -p "Mot de passe de la base [$DEFAULT_DB_PASS]: " DB_PASS
DB_PASS=${DB_PASS:-$DEFAULT_DB_PASS}
echo ""

read -p "URL de votre sous-domaine (ex: https://ayanna.votre-domaine.com): " APP_URL

# Créer le fichier .env
cat > .env << EOF
APP_NAME=Ayanna
APP_ENV=production
APP_KEY=base64:D0iJWVus9qxSSXrdCp4z0Q9bQkOw4xUh35xttBmMHuM=
APP_DEBUG=false
APP_URL=$APP_URL

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
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASS

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

echo "✅ Fichier .env créé avec succès"

# Générer une nouvelle clé d'application
echo "Génération d'une nouvelle clé d'application..."
php artisan key:generate --force

echo "✅ Configuration terminée"
echo ""
echo "Configuration utilisée :"
echo "- Base de données: $DB_NAME"
echo "- Utilisateur: $DB_USER"
echo "- URL: $APP_URL"
echo ""
echo "Prochaine étape: bash deploy.sh"
