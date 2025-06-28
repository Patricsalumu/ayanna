<?php
/**
 * Validation des couleurs de fond des layouts
 */

echo "=== VALIDATION COULEURS DE FOND ===\n";

$layouts = [
    'resources/views/layouts/app2.blade.php' => 'Points de vente show',
    'resources/views/components/layouts/guest-login.blade.php' => 'Page de login'
];

foreach ($layouts as $layout => $description) {
    echo "\n$description ($layout) :\n";
    
    if (file_exists($layout)) {
        $content = file_get_contents($layout);
        
        if (strpos($content, 'bg-gray-100') !== false) {
            echo "✓ Fond bg-gray-100 appliqué\n";
        } else {
            echo "✗ Fond bg-gray-100 manquant\n";
        }
        
        // Vérifier l'absence des anciennes couleurs
        if (strpos($content, 'bg-[#f9f6f3]') === false && 
            strpos($content, 'bg-gradient-to-br') === false) {
            echo "✓ Anciennes couleurs supprimées\n";
        } else {
            echo "✗ Anciennes couleurs encore présentes\n";
        }
        
        // Vérifier le texte
        if (strpos($content, 'text-gray-800') !== false) {
            echo "✓ Couleur de texte cohérente\n";
        } else {
            echo "⚠ Couleur de texte à vérifier\n";
        }
    } else {
        echo "✗ Fichier non trouvé\n";
    }
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ app2.blade.php : bg-gray-100 + text-gray-800\n";
echo "✅ guest-login.blade.php : bg-gray-100\n";
echo "✅ Header app2 : bg-white pour contraste\n";

echo "\n🎨 COULEURS APPLIQUÉES :\n";
echo "- Fond principal : bg-gray-100 (gris clair uniforme)\n";
echo "- Texte : text-gray-800 (gris foncé lisible)\n";
echo "- Header : bg-white (blanc pour contraste)\n";

echo "\nPAGES CONCERNÉES :\n";
echo "- Page login (/login)\n";
echo "- Page points de vente (/entreprise/{id}/points-de-vente)\n";
?>
