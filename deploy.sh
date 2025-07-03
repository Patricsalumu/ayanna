#!/bin/bash

# Script de déploiement Ayanna pour cPanel
echo "=== Déploiement Ayanna sur cPanel ==="

# Variables (à modifier selon votre configuration)
PROJECT_DIR=$(pwd)
SUBDOMAIN_DIR="public_html/ayanna"  # Remplacez par votre sous-domaine

echo "Répertoire de travail: $PROJECT_DIR"

# 1. Vérification de PHP
echo "=== Vérification de PHP ==="
php -v
if [ $? -ne 0 ]; then
    echo "ERREUR: PHP n'est pas disponible"
    exit 1
fi

# 2. Installation de Composer (si nécessaire)
echo "=== Installation/Vérification de Composer ==="
if [ ! -f composer.phar ]; then
    echo "Téléchargement de Composer..."
    curl -sS https://getcomposer.org/installer | php
    if [ $? -ne 0 ]; then
        echo "ERREUR: Impossible de télécharger Composer"
        exit 1
    fi
fi

# 3. Installation des dépendances
echo "=== Installation des dépendances ==="
php composer.phar install --no-dev --optimize-autoloader --no-interaction
if [ $? -ne 0 ]; then
    echo "ERREUR: Installation des dépendances échouée"
    exit 1
fi

# 4. Configuration de l'environnement
echo "=== Configuration de l'environnement ==="
if [ -f .env.production ]; then
    cp .env.production .env
    echo "Fichier .env copié depuis .env.production"
else
    echo "ATTENTION: Fichier .env.production non trouvé"
fi

# 5. Génération de la clé d'application
echo "=== Génération de la clé d'application ==="
php artisan key:generate --force
if [ $? -ne 0 ]; then
    echo "ERREUR: Génération de clé échouée"
    exit 1
fi

# 6. Migration de la base de données
echo "=== Migration de la base de données ==="
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "ERREUR: Migration de la base de données échouée"
    exit 1
fi

# 7. Optimisations Laravel
echo "=== Optimisations Laravel ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Création du lien symbolique pour le storage
echo "=== Création du lien storage ==="
php artisan storage:link
if [ $? -ne 0 ]; then
    echo "ATTENTION: Lien storage non créé (normal si déjà existant)"
fi

# 9. Optimisation des permissions
echo "=== Optimisation des permissions ==="
find storage -type f -exec chmod 644 {} \;
find storage -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;

# 10. Vérification des logs
echo "=== Création des dossiers de logs ==="
mkdir -p storage/logs
touch storage/logs/laravel.log
chmod 644 storage/logs/laravel.log

# 11. Test de l'application
echo "=== Test de configuration ==="
php artisan about
if [ $? -eq 0 ]; then
    echo "✅ Configuration OK"
else
    echo "❌ Problème de configuration détecté"
fi

echo "=== Déploiement terminé avec succès ==="
echo "Votre application Ayanna est prête !"
echo ""
echo "Prochaines étapes :"
echo "1. Vérifiez votre fichier .env avec les bonnes informations de base de données"
echo "2. Configurez le Document Root vers le dossier public/"
echo "3. Testez l'accès à votre sous-domaine"
