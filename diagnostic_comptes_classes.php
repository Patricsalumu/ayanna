<?php

require_once __DIR__ . '/vendor/autoload.php';

// Initialisation de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Compte;
use App\Models\ClasseComptable;
use App\Models\EcritureComptable;

echo "=== DIAGNOSTIC PROBLÈME BILAN/COMPTE DE RÉSULTAT ===\n\n";

echo "1. VÉRIFICATION DES COMPTES DE VENTES (CLASSE 7) :\n";

$comptesVente = Compte::where('numero', 'LIKE', '7%')
    ->orWhere('classe_comptable', '7')
    ->orWhere('type', 'produit')
    ->get();

echo "Nombre de comptes de vente trouvés : " . $comptesVente->count() . "\n\n";

foreach ($comptesVente as $compte) {
    echo "Compte : {$compte->numero} - {$compte->nom}\n";
    echo "- Type : {$compte->type}\n";
    echo "- Classe (string) : {$compte->classe_comptable}\n";
    echo "- Classe ID (FK) : " . ($compte->classe_comptable_id ?? 'NULL') . "\n";
    
    // Vérifier si classe comptable liée existe
    if ($compte->classe_comptable_id) {
        $classe = ClasseComptable::find($compte->classe_comptable_id);
        echo "- Classe liée : " . ($classe ? $classe->nom : 'INTROUVABLE') . "\n";
    }
    echo "---\n";
}

echo "\n2. VÉRIFICATION DES CLASSES COMPTABLES :\n";

$classes = ClasseComptable::all();
echo "Classes comptables disponibles :\n";
foreach ($classes as $classe) {
    echo "- ID: {$classe->id} | Numéro: {$classe->numero} | Nom: {$classe->nom}\n";
}

echo "\n3. COMPTES AVEC PROBLÈMES DE LIAISON :\n";

$comptesProblematiques = Compte::whereNotNull('classe_comptable')
    ->whereNull('classe_comptable_id')
    ->get();

echo "Comptes avec classe_comptable rempli mais classe_comptable_id NULL : " . $comptesProblematiques->count() . "\n";

foreach ($comptesProblematiques as $compte) {
    echo "- {$compte->numero} | {$compte->nom} | Classe: {$compte->classe_comptable}\n";
}

echo "\n4. VÉRIFICATION DES ÉCRITURES COMPTABLES :\n";

// Compter les écritures par classe de compte
$ecritures = \Illuminate\Support\Facades\DB::table('ecriture_comptables')
    ->join('comptes', 'ecriture_comptables.compte_id', '=', 'comptes.id')
    ->selectRaw('comptes.classe_comptable, 
                 SUM(ecriture_comptables.debit) as total_debit,
                 SUM(ecriture_comptables.credit) as total_credit,
                 COUNT(*) as nb_ecritures')
    ->groupBy('comptes.classe_comptable')
    ->get();

echo "Répartition des écritures par classe :\n";
foreach ($ecritures as $ecriture) {
    $solde = $ecriture->total_credit - $ecriture->total_debit;
    echo "- Classe {$ecriture->classe_comptable} : {$ecriture->nb_ecritures} écritures | Solde: {$solde}€\n";
}

echo "\n5. SOLUTION PROPOSÉE :\n";

// Identifier les corrections nécessaires
$corrections = [];
foreach ($comptesProblematiques as $compte) {
    $classeCorrespondante = ClasseComptable::where('numero', $compte->classe_comptable)->first();
    if ($classeCorrespondante) {
        $corrections[] = [
            'compte_id' => $compte->id,
            'compte_numero' => $compte->numero,
            'classe_id' => $classeCorrespondante->id,
            'classe_nom' => $classeCorrespondante->nom
        ];
    }
}

if (count($corrections) > 0) {
    echo "Corrections à appliquer :\n";
    foreach ($corrections as $correction) {
        echo "- Compte {$correction['compte_numero']} → Classe {$correction['classe_nom']}\n";
    }
    
    echo "\nScript de correction disponible. Exécutez fix_comptes_classes.php pour appliquer.\n";
} else {
    echo "Aucune correction automatique possible.\n";
    echo "Vérifiez manuellement la configuration des comptes.\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";

?>
