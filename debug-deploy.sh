#!/bin/bash

echo "=== DIAGNOSTIC DÉPLOIEMENT AYANNA ==="
echo "Date: $(date)"
echo ""

echo "1. Vérification de la structure des dossiers:"
echo "-------------------------------------------"
ls -la /home/$USER/public_html/solac.congomemoire.net/

echo ""
echo "2. Contenu du dossier public:"
echo "-----------------------------"
ls -la /home/$USER/public_html/solac.congomemoire.net/public/

echo ""
echo "3. Vérification du fichier index.php:"
echo "-------------------------------------"
if [ -f "/home/$USER/public_html/solac.congomemoire.net/public/index.php" ]; then
    echo "✓ index.php existe"
    head -5 /home/$USER/public_html/solac.congomemoire.net/public/index.php
else
    echo "✗ index.php MANQUANT!"
fi

echo ""
echo "4. Vérification des permissions:"
echo "--------------------------------"
ls -la /home/$USER/public_html/solac.congomemoire.net/public/index.php

echo ""
echo "5. Test de la configuration Apache (.htaccess):"
echo "-----------------------------------------------"
if [ -f "/home/$USER/public_html/solac.congomemoire.net/public/.htaccess" ]; then
    echo "✓ .htaccess existe"
    cat /home/$USER/public_html/solac.congomemoire.net/public/.htaccess
else
    echo "✗ .htaccess MANQUANT!"
fi

echo ""
echo "6. Vérification Document Root cPanel:"
echo "-------------------------------------"
echo "Le Document Root doit pointer vers: public_html/solac.congomemoire.net/public"
echo "Vérifiez cela dans votre panneau cPanel > Sous-domaines"

echo ""
echo "7. Test de connectivité base de données:"
echo "----------------------------------------"
cd /home/$USER/public_html/solac.congomemoire.net
if [ -f "artisan" ]; then
    echo "Testing database connection..."
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" 2>&1 || echo "Erreur DB"
else
    echo "✗ Laravel non installé correctement"
fi

echo ""
echo "8. Logs d'erreur Laravel:"
echo "-------------------------"
if [ -f "/home/$USER/public_html/solac.congomemoire.net/storage/logs/laravel.log" ]; then
    echo "Dernières erreurs:"
    tail -10 /home/$USER/public_html/solac.congomemoire.net/storage/logs/laravel.log
else
    echo "Pas de logs Laravel trouvés"
fi
