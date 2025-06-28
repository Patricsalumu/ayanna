<?php

// Script pour corriger automatiquement les classe_comptable_id manquants

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClasseComptable;
use App\Models\Compte;

echo "=== CORRECTION DES CLASSES COMPTABLES MANQUANTES ===\n\n";

// Règles de classification basées sur les numéros de compte
$regles = [
    '1' => 1,   // Comptes 1xx → Classe 1 (Immobilisations)
    '2' => 2,   // Comptes 2xx → Classe 2 (Stocks)
    '3' => 3,   // Comptes 3xx → Classe 3 (Tiers)
    '4' => 4,   // Comptes 4xx → Classe 4 (Tiers)
    '5' => 5,   // Comptes 5xx → Classe 5 (Financiers)
    '6' => 6,   // Comptes 6xx → Classe 6 (Charges)
    '7' => 7,   // Comptes 7xx → Classe 7 (Produits)
];

// Récupérer tous les comptes sans classe
$comptesSansClasse = Compte::whereNull('classe_comptable_id')->get();
echo "Comptes trouvés sans classe: " . $comptesSansClasse->count() . "\n\n";

$corriges = 0;
$erreurs = 0;

foreach ($comptesSansClasse as $compte) {
    // Déterminer la classe basée sur le premier chiffre du numéro
    $premierChiffre = substr($compte->numero, 0, 1);
    
    if (isset($regles[$premierChiffre])) {
        $numeroClasse = $regles[$premierChiffre];
        
        // Trouver la classe comptable correspondante pour cette entreprise
        $classe = ClasseComptable::where('numero', $numeroClasse)
                                ->where('entreprise_id', $compte->entreprise_id)
                                ->first();
        
        if ($classe) {
            // Mettre à jour le compte
            $compte->classe_comptable_id = $classe->id;
            $compte->save();
            
            echo "✅ Compte {$compte->numero} ({$compte->nom}) → Classe {$numeroClasse}\n";
            $corriges++;
        } else {
            echo "❌ Classe {$numeroClasse} introuvable pour l'entreprise {$compte->entreprise_id}\n";
            $erreurs++;
        }
    } else {
        echo "⚠️  Compte {$compte->numero} : numéro non reconnu\n";
        $erreurs++;
    }
}

echo "\n=== RÉSUMÉ ===\n";
echo "Comptes corrigés: {$corriges}\n";
echo "Erreurs: {$erreurs}\n";

// Vérification finale
echo "\n=== VÉRIFICATION FINALE ===\n";
$classes = ClasseComptable::withCount('comptes')->get();
foreach ($classes as $classe) {
    echo "Classe {$classe->numero}: {$classe->nom} → {$classe->comptes_count} comptes\n";
}

echo "\n=== FIN ===\n";
