<?php

// Script de diagnostic pour vérifier les relations classes comptables <-> comptes

use App\Models\ClasseComptable;
use App\Models\Compte;

echo "=== DIAGNOSTIC CLASSES COMPTABLES ET COMPTES ===\n\n";

// 1. Vérifier les classes comptables
echo "1. Classes comptables existantes :\n";
$classes = ClasseComptable::with('comptes')->get();
foreach ($classes as $classe) {
    echo "  - Classe {$classe->numero}: {$classe->nom} ({$classe->comptes->count()} comptes)\n";
}

echo "\n2. Comptes avec classe_comptable_id :\n";
$comptesAvecClasse = Compte::whereNotNull('classe_comptable_id')->get();
echo "  - Nombre de comptes avec classe_comptable_id: " . $comptesAvecClasse->count() . "\n";

echo "\n3. Comptes sans classe_comptable_id :\n";
$comptesSansClasse = Compte::whereNull('classe_comptable_id')->get();
echo "  - Nombre de comptes sans classe_comptable_id: " . $comptesSansClasse->count() . "\n";

echo "\n4. Vérification des classe_comptable_id orphelins :\n";
$comptes = Compte::whereNotNull('classe_comptable_id')->get();
foreach ($comptes as $compte) {
    $classe = ClasseComptable::find($compte->classe_comptable_id);
    if (!$classe) {
        echo "  - PROBLÈME: Compte {$compte->numero} référence classe_comptable_id {$compte->classe_comptable_id} qui n'existe pas\n";
    }
}

echo "\n5. Détail par classe :\n";
foreach ($classes as $classe) {
    $comptes = $classe->comptes;
    echo "  - Classe {$classe->numero} ({$classe->nom}):\n";
    foreach ($comptes as $compte) {
        echo "    * Compte {$compte->numero}: {$compte->nom}\n";
    }
    if ($comptes->count() === 0) {
        echo "    (Aucun compte)\n";
    }
}

echo "\n=== FIN DIAGNOSTIC ===\n";
