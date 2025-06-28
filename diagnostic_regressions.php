<?php
/**
 * Script de diagnostic pour identifier les régressions introduites
 */

echo "=== DIAGNOSTIC DES RÉGRESSIONS ===\n";

$problemes = [];

// 1. Vérifier les doublons dans navigation2
echo "\n1. NAVIGATION2.BLADE.PHP :\n";
$nav2_content = file_get_contents('resources/views/layouts/navigation2.blade.php');
$classes_comptables_count = substr_count($nav2_content, 'Classes Comptables');
$plan_comptable_count = substr_count($nav2_content, 'Plan comptable');

if ($classes_comptables_count > 1) {
    echo "✗ Doublon 'Classes Comptables' détecté ($classes_comptables_count occurrences)\n";
    $problemes[] = "Doublon Classes Comptables dans navigation2";
} else {
    echo "✓ Pas de doublon 'Classes Comptables'\n";
}

if ($plan_comptable_count > 1) {
    echo "✗ Doublon 'Plan comptable' détecté ($plan_comptable_count occurrences)\n";
    $problemes[] = "Doublon Plan comptable dans navigation2";
} else {
    echo "✓ Pas de doublon 'Plan comptable'\n";
}

// 2. Vérifier les erreurs FCFA dans les vues comptables
echo "\n2. ERREURS FCFA DANS LES VUES :\n";
$fichiers_comptables = [
    'resources/views/classes-comptables/index.blade.php',
    'resources/views/classes-comptables/show.blade.php',
    'resources/views/classes-comptables/bilan.blade.php',
    'resources/views/classes-comptables/compte-resultat.blade.php',
    'resources/views/comptes/index.blade.php'
];

foreach ($fichiers_comptables as $fichier) {
    if (file_exists($fichier)) {
        $content = file_get_contents($fichier);
        $fcfa_count = substr_count($content, 'FCFA');
        if ($fcfa_count > 0) {
            echo "✗ $fichier contient $fcfa_count occurrences de 'FCFA'\n";
            $problemes[] = "FCFA dans $fichier";
        } else {
            echo "✓ $fichier : pas de FCFA\n";
        }
    }
}

// 3. Vérifier les erreurs de routage
echo "\n3. ERREURS DE ROUTAGE :\n";
$comptes_index = file_get_contents('resources/views/comptes/index.blade.php');
if (strpos($comptes_index, 'comptabilite.comptes') !== false) {
    echo "✗ Route erronée 'comptabilite.comptes' trouvée\n";
    $problemes[] = "Route erronée comptabilite.comptes";
} else {
    echo "✓ Pas de route erronée dans comptes/index\n";
}

// 4. Vérifier les layouts utilisés
echo "\n4. LAYOUTS UTILISÉS :\n";
foreach ($fichiers_comptables as $fichier) {
    if (file_exists($fichier)) {
        $content = file_get_contents($fichier);
        if (strpos($content, "@extends('layouts.app')") !== false) {
            echo "✗ $fichier utilise 'layouts.app' au lieu de 'layouts.appsalle'\n";
            $problemes[] = "Layout incorrect dans $fichier";
        } else {
            echo "✓ $fichier utilise le bon layout\n";
        }
    }
}

// 5. Vérifier les backgrounds restaurés
echo "\n5. BACKGROUNDS RESTAURÉS :\n";
$pointsdevente_show = 'resources/views/pointsDeVente/show.blade.php';
if (file_exists($pointsdevente_show)) {
    $content = file_get_contents($pointsdevente_show);
    if (strpos($content, 'background') !== false || strpos($content, 'bg-gradient') !== false) {
        echo "✗ Backgrounds détectés dans pointsDeVente/show\n";
        $problemes[] = "Backgrounds dans pointsDeVente/show";
    } else {
        echo "✓ Pas de backgrounds dans pointsDeVente/show\n";
    }
}

echo "\n=== RÉSUMÉ ===\n";
if (empty($problemes)) {
    echo "🟢 Aucun problème détecté\n";
} else {
    echo "🔴 " . count($problemes) . " problème(s) détecté(s) :\n";
    foreach ($problemes as $probleme) {
        echo "  - $probleme\n";
    }
}

echo "\n=== ACTIONS À EFFECTUER ===\n";
if (!empty($problemes)) {
    echo "1. Corriger les doublons dans navigation2\n";
    echo "2. Remplacer FCFA par FC dans les vues comptables\n";
    echo "3. Corriger les routes erronées\n";
    echo "4. Vérifier les layouts\n";
    echo "5. Supprimer les backgrounds indésirables\n";
}
?>
