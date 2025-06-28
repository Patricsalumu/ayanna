<?php
/**
 * Script pour créer les classes comptables de base pour une entreprise
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ClasseComptable;
use App\Models\User;

echo "=== CRÉATION DES CLASSES COMPTABLES ===\n\n";

$entrepriseId = 5; // ID de l'entreprise

// Classes comptables de base selon le plan comptable général
$classesDeBase = [
    ['numero' => '1', 'nom' => 'Comptes de capitaux', 'est_principale' => true],
    ['numero' => '2', 'nom' => 'Comptes d\'immobilisations', 'est_principale' => true],
    ['numero' => '3', 'nom' => 'Comptes de stocks et en-cours', 'est_principale' => true],
    ['numero' => '4', 'nom' => 'Comptes de tiers', 'est_principale' => true],
    ['numero' => '5', 'nom' => 'Comptes de trésorerie', 'est_principale' => true],
    ['numero' => '6', 'nom' => 'Comptes de charges', 'est_principale' => true],
    ['numero' => '7', 'nom' => 'Comptes de produits', 'est_principale' => true],
];

try {
    echo "Création des classes comptables pour l'entreprise {$entrepriseId}...\n\n";
    
    foreach ($classesDeBase as $classeData) {
        // Vérifier si la classe existe déjà
        $existe = ClasseComptable::where('entreprise_id', $entrepriseId)
            ->where('numero', $classeData['numero'])
            ->first();
            
        if (!$existe) {
            $classe = ClasseComptable::create([
                'numero' => $classeData['numero'],
                'nom' => $classeData['nom'],
                'est_principale' => $classeData['est_principale'],
                'entreprise_id' => $entrepriseId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ Créée : Classe {$classe->numero} - {$classe->nom}\n";
        } else {
            echo "⚠️ Existe déjà : Classe {$classeData['numero']} - {$classeData['nom']}\n";
        }
    }
    
    echo "\n🎉 Terminé ! Vérification...\n";
    $count = ClasseComptable::where('entreprise_id', $entrepriseId)->count();
    echo "Total des classes pour l'entreprise {$entrepriseId}: {$count}\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
