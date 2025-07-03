#!/bin/bash

echo "=== CORRECTION DOCUMENT ROOT ==="
echo ""

# Créer le bon lien symbolique si nécessaire
echo "1. Création/Vérification du dossier public correct:"
cd /home/$USER/public_html/

# Si le sous-domaine pointe vers le mauvais dossier
if [ -d "solac.congomemoire.net" ] && [ ! -L "solac.congomemoire.net" ]; then
    echo "Dossier solac.congomemoire.net existe, vérification de la structure..."
    
    # Si c'est un dossier et non un lien vers public
    if [ -f "solac.congomemoire.net/index.php" ] && [ ! -d "solac.congomemoire.net/public" ]; then
        echo "PROBLÈME: Le sous-domaine pointe vers la racine au lieu de /public"
        echo "Solution: Reconfigurer dans cPanel pour pointer vers public_html/solac.congomemoire.net/public"
    fi
fi

echo ""
echo "2. Vérification de la structure correcte:"
echo "Le dossier de l'application doit être:"
echo "  /home/$USER/public_html/solac.congomemoire.net/ (contient Laravel)"
echo "Le Document Root doit pointer vers:"
echo "  /home/$USER/public_html/solac.congomemoire.net/public/ (contient index.php)"

echo ""
echo "3. Structure actuelle:"
ls -la /home/$USER/public_html/ | grep solac

echo ""
echo "4. Contenu du dossier application:"
if [ -d "/home/$USER/public_html/solac.congomemoire.net" ]; then
    ls -la /home/$USER/public_html/solac.congomemoire.net/ | head -10
fi
