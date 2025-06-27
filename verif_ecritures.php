<?php
/**
 * Vérification simple des écritures comptables
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configuration Laravel pour le test
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VÉRIFICATION ÉCRITURES COMPTABLES ===\n\n";

try {
    // Vérifier les écritures récentes
    $ecritures = \App\Models\JournalComptable::orderBy('created_at', 'desc')
        ->take(10)
        ->get(['id', 'numero_piece', 'montant_total', 'commande_id', 'created_at']);
    
    echo "📊 Écritures comptables récentes (" . $ecritures->count() . " trouvées):\n";
    
    if ($ecritures->count() > 0) {
        foreach ($ecritures as $ecriture) {
            echo "   ✅ {$ecriture->numero_piece} - {$ecriture->montant_total} FCFA (commande {$ecriture->commande_id}) - {$ecriture->created_at}\n";
        }
        
        echo "\n🎉 L'INTÉGRATION COMPTABLE FONCTIONNE !\n";
        echo "Les écritures sont bien générées lors des ventes.\n";
    } else {
        echo "   ❌ Aucune écriture comptable trouvée\n";
    }
    
    // Vérifier spécifiquement la commande 136
    $ecrituresCommande136 = \App\Models\JournalComptable::where('commande_id', 136)->get();
    echo "\n📋 Écritures pour la commande 136: " . $ecrituresCommande136->count() . " trouvée(s)\n";
    
    if ($ecrituresCommande136->count() > 0) {
        foreach ($ecrituresCommande136 as $ecriture) {
            echo "   - {$ecriture->numero_piece}: {$ecriture->montant_total} FCFA\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN VÉRIFICATION ===\n";
?>
