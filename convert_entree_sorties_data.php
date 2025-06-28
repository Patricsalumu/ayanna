<?php
/**
 * Script pour convertir les données de la table entree_sorties
 * de 'credit'/'debit' vers 'entree'/'sortie'
 */

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CONVERSION DES DONNÉES ENTREE_SORTIES ===\n\n";

try {
    // Vérifier d'abord la structure actuelle
    echo "1. Vérification de la structure actuelle...\n";
    $columns = DB::select("DESCRIBE entree_sorties");
    foreach ($columns as $column) {
        if ($column->Field === 'type') {
            echo "   Champ 'type' trouvé: {$column->Type}\n";
            break;
        }
    }
    
    // Compter les enregistrements actuels
    $totalRecords = DB::table('entree_sorties')->count();
    echo "   Total des enregistrements: {$totalRecords}\n\n";
    
    if ($totalRecords > 0) {
        // Compter par type actuel
        $creditCount = DB::table('entree_sorties')->where('type', 'credit')->count();
        $debitCount = DB::table('entree_sorties')->where('type', 'debit')->count();
        
        echo "2. Répartition actuelle:\n";
        echo "   - Type 'credit': {$creditCount}\n";
        echo "   - Type 'debit': {$debitCount}\n\n";
        
        echo "3. Conversion en cours...\n";
        
        // Commencer une transaction
        DB::beginTransaction();
        
        try {
            // Convertir temporairement en VARCHAR pour permettre les nouvelles valeurs
            echo "   Modification temporaire du type de colonne...\n";
            DB::statement("ALTER TABLE entree_sorties MODIFY COLUMN type VARCHAR(10)");
            
            // Convertir credit -> entree
            if ($creditCount > 0) {
                $updated = DB::table('entree_sorties')
                    ->where('type', 'credit')
                    ->update(['type' => 'entree']);
                echo "   Converti {$updated} enregistrements de 'credit' vers 'entree'\n";
            }
            
            // Convertir debit -> sortie
            if ($debitCount > 0) {
                $updated = DB::table('entree_sorties')
                    ->where('type', 'debit')
                    ->update(['type' => 'sortie']);
                echo "   Converti {$updated} enregistrements de 'debit' vers 'sortie'\n";
            }
            
            DB::commit();
            echo "   ✅ Conversion réussie!\n\n";
            
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        
    } else {
        echo "2. Aucun enregistrement à convertir.\n\n";
    }
    
    // Vérifier le résultat
    echo "4. Vérification après conversion:\n";
    $entreeCount = DB::table('entree_sorties')->where('type', 'entree')->count();
    $sortieCount = DB::table('entree_sorties')->where('type', 'sortie')->count();
    echo "   - Type 'entree': {$entreeCount}\n";
    echo "   - Type 'sortie': {$sortieCount}\n\n";
    
    echo "✅ Conversion terminée avec succès!\n";
    echo "Vous pouvez maintenant lancer la migration: php artisan migrate\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la conversion: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
