#!/bin/bash

# Script de déploiement Ayanna pour serveurs partagés
echo "=== Déploiement Ayanna (serveur partagé) ==="

PROJECT_DIR=$(pwd)
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

# 3. Installation des dépendances (avec contournement des extensions manquantes)
echo "=== Installation des dépendances (mode serveur partagé) ==="
php composer.phar install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-fileinfo --ignore-platform-req=ext-gd --ignore-platform-req=ext-zip
if [ $? -ne 0 ]; then
    echo "ERREUR: Installation des dépendances échouée"
    echo "Tentative avec mise à jour forcée..."
    php composer.phar update --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
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
    echo "Vérifiez vos paramètres de base de données dans .env"
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
find storage -type f -exec chmod 644 {} \; 2>/dev/null
find storage -type d -exec chmod 755 {} \; 2>/dev/null
find bootstrap/cache -type f -exec chmod 644 {} \; 2>/dev/null
find bootstrap/cache -type d -exec chmod 755 {} \; 2>/dev/null

# 10. Vérification des logs
echo "=== Création des dossiers de logs ==="
mkdir -p storage/logs
touch storage/logs/laravel.log
chmod 644 storage/logs/laravel.log 2>/dev/null

# 11. Test de l'application
echo "=== Test de configuration ==="
php artisan about
if [ $? -eq 0 ]; then
    echo "✅ Configuration OK"
else
    echo "❌ Problème de configuration détecté"
    echo "Vérifiez les logs: tail storage/logs/laravel.log"
fi

echo "=== Déploiement terminé ==="
echo "Votre application Ayanna est prête !"
echo ""
echo "Prochaines étapes :"
echo "1. Configurez le Document Root vers le dossier public/"
echo "2. Testez l'accès à votre sous-domaine"
echo "3. En cas de problème: bash maintenance.sh logs"
