<?php
/**
 * Test simple pour vérifier l'intégration comptable
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Commande;
use App\Models\JournalComptable;
use App\Services\ComptabiliteService;

// Configuration Laravel pour le test
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST INTÉGRATION COMPTABLE ===\n\n";

try {
    // 1. Vérifier la dernière commande
    $derniereCommande = Commande::orderBy('created_at', 'desc')->first();
    
    if (!$derniereCommande) {
        echo "❌ Aucune commande trouvée dans la base de données\n";
        exit(1);
    }
    
    echo "✅ Dernière commande trouvée:\n";
    echo "   - ID: {$derniereCommande->id}\n";
    echo "   - Montant: {$derniereCommande->montant_total} FCFA\n";
    echo "   - Status: {$derniereCommande->status}\n";
    echo "   - Date: {$derniereCommande->created_at}\n\n";
    
    // 2. Vérifier les écritures comptables associées
    $ecritures = JournalComptable::where('commande_id', $derniereCommande->id)->get();
    
    echo "📊 Écritures comptables pour cette commande:\n";
    if ($ecritures->count() > 0) {
        echo "   ✅ {$ecritures->count()} écriture(s) trouvée(s):\n";
        foreach ($ecritures as $ecriture) {
            $type = $ecriture->debit ? 'Débit' : 'Crédit';
            echo "   - {$ecriture->compte->numero} {$ecriture->compte->libelle}: {$type} {$ecriture->montant} FCFA\n";
        }
    } else {
        echo "   ❌ Aucune écriture comptable trouvée\n";
        
        // Test du service directement
        echo "\n🔧 Test du service ComptabiliteService:\n";
        try {
            $service = new ComptabiliteService();
            $journal = $service->enregistrerVente($derniereCommande);
            
            if ($journal) {
                echo "   ✅ Service fonctionne, journal créé: {$journal->numero_piece}\n";
                
                // Recharger les écritures
                $nouvellesEcritures = JournalComptable::where('commande_id', $derniereCommande->id)->get();
                echo "   ✅ {$nouvellesEcritures->count()} nouvelle(s) écriture(s) générée(s)\n";
            } else {
                echo "   ❌ Service n'a pas retourné de journal\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Erreur dans le service: " . $e->getMessage() . "\n";
        }
    }
    
    // 3. Statistiques globales
    echo "\n📈 Statistiques générales:\n";
    $totalCommandes = Commande::count();
    $totalEcritures = JournalComptable::count();
    $commandesAvecEcritures = Commande::whereHas('journalComptable')->count();
    
    echo "   - Total commandes: {$totalCommandes}\n";
    echo "   - Total écritures: {$totalEcritures}\n";
    echo "   - Commandes avec écritures: {$commandesAvecEcritures}\n";
    echo "   - Taux d'intégration: " . round(($commandesAvecEcritures / max($totalCommandes, 1)) * 100, 2) . "%\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
?>
