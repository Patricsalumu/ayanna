<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\Commande;
use App\Models\JournalComptable;
use App\Models\EcritureComptable;
use App\Models\Compte;

// Configuration de la base de données (adaptez selon votre config)
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'nom_de_votre_base', // Changez ici
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== VÉRIFICATION COMPTABILITÉ ===\n\n";

// 1. Dernières commandes validées
echo "1. DERNIÈRES COMMANDES VALIDÉES :\n";
$dernieresCommandes = Capsule::table('commandes')
    ->where('statut', 'validee')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'panier_id', 'mode_paiement', 'created_at']);

foreach ($dernieresCommandes as $cmd) {
    echo "- Commande {$cmd->id} | Panier {$cmd->panier_id} | Mode: {$cmd->mode_paiement} | Date: {$cmd->created_at}\n";
}

echo "\n2. ÉCRITURES COMPTABLES CORRESPONDANTES :\n";

// 2. Vérifier les écritures pour ces commandes
foreach ($dernieresCommandes as $cmd) {
    $journal = Capsule::table('journal_comptables')
        ->where('commande_id', $cmd->id)
        ->first(['id', 'numero_piece', 'libelle', 'montant_total', 'date_ecriture']);
    
    if ($journal) {
        echo "✓ Commande {$cmd->id} → Journal {$journal->id} | Pièce: {$journal->numero_piece} | Montant: {$journal->montant_total}€\n";
        
        // Vérifier les écritures débit/crédit
        $ecritures = Capsule::table('ecriture_comptables')
            ->join('comptes', 'ecriture_comptables.compte_id', '=', 'comptes.id')
            ->where('journal_id', $journal->id)
            ->get(['ecriture_comptables.*', 'comptes.nom as compte_nom', 'comptes.numero as compte_numero']);
        
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($ecritures as $ecriture) {
            $type = $ecriture->debit > 0 ? 'DÉBIT' : 'CRÉDIT';
            $montant = $ecriture->debit > 0 ? $ecriture->debit : $ecriture->credit;
            echo "  - {$type}: {$ecriture->compte_numero} {$ecriture->compte_nom} = {$montant}€\n";
            
            $totalDebit += $ecriture->debit;
            $totalCredit += $ecriture->credit;
        }
        
        // Vérifier l'équilibre
        if ($totalDebit == $totalCredit) {
            echo "  ✓ Équilibre OK (Débit: {$totalDebit}€ = Crédit: {$totalCredit}€)\n";
        } else {
            echo "  ✗ DÉSÉQUILIBRE ! (Débit: {$totalDebit}€ ≠ Crédit: {$totalCredit}€)\n";
        }
    } else {
        echo "✗ Commande {$cmd->id} → AUCUNE ÉCRITURE COMPTABLE TROUVÉE !\n";
    }
    echo "\n";
}

// 3. Vérifier la cohérence des montants avec les paniers
echo "3. VÉRIFICATION COHÉRENCE MONTANTS :\n";

foreach ($dernieresCommandes as $cmd) {
    // Calculer le montant depuis le panier
    $montantPanier = Capsule::table('panier_produit')
        ->join('produits', 'panier_produit.produit_id', '=', 'produits.id')
        ->where('panier_produit.panier_id', $cmd->panier_id)
        ->sum(Capsule::raw('panier_produit.quantite * produits.prix_vente'));
    
    // Récupérer le montant du journal
    $journal = Capsule::table('journal_comptables')
        ->where('commande_id', $cmd->id)
        ->first(['montant_total']);
    
    if ($journal) {
        if (abs($montantPanier - $journal->montant_total) < 0.01) {
            echo "✓ Commande {$cmd->id}: Panier {$montantPanier}€ = Journal {$journal->montant_total}€\n";
        } else {
            echo "✗ Commande {$cmd->id}: INCOHÉRENCE ! Panier {$montantPanier}€ ≠ Journal {$journal->montant_total}€\n";
        }
    }
}

echo "\n4. STATISTIQUES GÉNÉRALES :\n";

// Nombre total d'écritures
$nbEcritures = Capsule::table('journal_comptables')->count();
echo "- Nombre total d'écritures dans le journal : {$nbEcritures}\n";

// Écritures par type
$typesEcritures = Capsule::table('journal_comptables')
    ->select('type_operation', Capsule::raw('COUNT(*) as count'))
    ->groupBy('type_operation')
    ->get();

echo "- Répartition par type :\n";
foreach ($typesEcritures as $type) {
    echo "  * {$type->type_operation}: {$type->count}\n";
}

// Montant total des ventes comptabilisées
$totalVentes = Capsule::table('journal_comptables')
    ->where('type_operation', 'vente')
    ->sum('montant_total');
echo "- Total des ventes comptabilisées : {$totalVentes}€\n";

echo "\n=== FIN VÉRIFICATION ===\n";

?>
