#!/bin/bash

# Commandes de maintenance Ayanna
echo "=== Maintenance Ayanna ==="

case "$1" in
    "cache-clear")
        echo "Nettoyage du cache..."
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        echo "✅ Cache nettoyé"
        ;;
    "cache-optimize")
        echo "Optimisation du cache..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        echo "✅ Cache optimisé"
        ;;
    "migrate")
        echo "Migration base de données..."
        php artisan migrate --force
        echo "✅ Migration terminée"
        ;;
    "permissions")
        echo "Correction des permissions..."
        find storage -type f -exec chmod 644 {} \;
        find storage -type d -exec chmod 755 {} \;
        find bootstrap/cache -type f -exec chmod 644 {} \;
        find bootstrap/cache -type d -exec chmod 755 {} \;
        echo "✅ Permissions corrigées"
        ;;
    "storage-link")
        echo "Recréation du lien storage..."
        php artisan storage:link --force
        echo "✅ Lien storage créé"
        ;;
    "logs")
        echo "Dernières lignes des logs:"
        tail -20 storage/logs/laravel.log
        ;;
    "status")
        echo "Statut de l'application:"
        php artisan about
        ;;
    "update")
        echo "Mise à jour de l'application..."
        php composer.phar install --no-dev --optimize-autoloader
        php artisan migrate --force
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        echo "✅ Mise à jour terminée"
        ;;
    *)
        echo "Usage: bash maintenance.sh [option]"
        echo ""
        echo "Options disponibles:"
        echo "  cache-clear    - Nettoyer le cache"
        echo "  cache-optimize - Optimiser le cache"
        echo "  migrate        - Exécuter les migrations"
        echo "  permissions    - Corriger les permissions"
        echo "  storage-link   - Recréer le lien storage"
        echo "  logs           - Afficher les derniers logs"
        echo "  status         - Statut de l'application"
        echo "  update         - Mettre à jour l'application"
        ;;
esac
