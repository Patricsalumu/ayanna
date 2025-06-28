<?php
/**
 * Script pour voir la répartition des classes comptables
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ClasseComptable;
use Illuminate\Support\Facades\DB;

echo "=== RÉPARTITION DES CLASSES COMPTABLES ===\n\n";

try {
    // Voir toutes les classes avec leur entreprise_id
    $classes = ClasseComptable::select('entreprise_id', DB::raw('count(*) as count'))
        ->groupBy('entreprise_id')
        ->orderBy('entreprise_id')
        ->get();
    
    echo "Répartition par entreprise_id:\n";
    foreach ($classes as $groupe) {
        echo "  Entreprise {$groupe->entreprise_id}: {$groupe->count} classes\n";
    }
    
    echo "\nExemples de classes (premières 10):\n";
    $exemples = ClasseComptable::take(10)->get();
    foreach ($exemples as $classe) {
        echo "  - ID {$classe->id}: Classe {$classe->numero} ({$classe->nom}) - Entreprise {$classe->entreprise_id}\n";
    }
    
    // Vérifier s'il y a des classes avec entreprise_id NULL
    $nullCount = ClasseComptable::whereNull('entreprise_id')->count();
    echo "\nClasses avec entreprise_id NULL: {$nullCount}\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
