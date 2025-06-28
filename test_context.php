<?php
/**
 * Test calcul montant journal comptable
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configuration Laravel pour le test
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST CALCUL MONTANT JOURNAL ===\n\n";

try {
    // Trouver un journal récent avec commande
    $journal = \App\Models\JournalComptable::with(['commande.panier.produits'])
        ->where('type_operation', 'vente')
        ->orderBy('created_at', 'desc')
        ->first();
    
    if (!$journal) {
        echo "❌ Aucun journal de vente trouvé\n";
        exit(1);
    }
    
    echo "📊 Journal trouvé:\n";
    echo "   - ID: {$journal->id}\n";
    echo "   - Numéro: {$journal->numero_piece}\n";
    echo "   - Montant journal: {$journal->montant_total} FCFA\n";
    echo "   - Commande ID: {$journal->commande_id}\n\n";
    
    if ($journal->commande && $journal->commande->panier) {
        $panier = $journal->commande->panier;
        echo "📦 Détails panier:\n";
        echo "   - Panier ID: {$panier->id}\n";
        echo "   - Nombre de produits: " . $panier->produits->count() . "\n";
        
        $montantCalcule = 0;
        foreach ($panier->produits as $produit) {
            $sousTotal = $produit->pivot->quantite * $produit->prix_vente;
            $montantCalcule += $sousTotal;
            echo "   - {$produit->nom}: {$produit->pivot->quantite} x {$produit->prix_vente} = {$sousTotal} FCFA\n";
        }
        
        echo "\n💰 Résumé:\n";
        echo "   - Montant calculé: {$montantCalcule} FCFA\n";
        echo "   - Montant journal: {$journal->montant_total} FCFA\n";
        
        if ($montantCalcule == $journal->montant_total) {
            echo "   ✅ Les montants correspondent !\n";
        } else {
            echo "   ❌ DIFFÉRENCE détectée !\n";
            echo "   🔧 Correction nécessaire...\n";
            
            // Corriger le montant
            $journal->montant_total = $montantCalcule;
            $journal->save();
            echo "   ✅ Montant corrigé dans le journal\n";
        }
    } else {
        echo "❌ Pas de panier associé à cette commande\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
?>
