<?php
/**
 * Script de test pour ClasseComptable
 */

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\ClasseComptable;
use App\Models\User;

echo "=== TEST CLASSE COMPTABLE ===\n\n";

try {
    // Test 1: Vérifier si la table existe
    echo "1. Test de la table ClasseComptable...\n";
    $count = ClasseComptable::count();
    echo "   ✅ Table existe - Total des classes: {$count}\n\n";
    
    // Test 2: Vérifier un utilisateur
    echo "2. Test utilisateur...\n";
    $user = User::first();
    if ($user) {
        echo "   ✅ Utilisateur trouvé: {$user->name}\n";
        echo "   Entreprise ID: " . ($user->entreprise_id ?? 'NULL') . "\n\n";
        
        if ($user->entreprise_id) {
            // Test 3: Classes pour cette entreprise
            echo "3. Test classes pour l'entreprise {$user->entreprise_id}...\n";
            $classesEntreprise = ClasseComptable::where('entreprise_id', $user->entreprise_id)->get();
            echo "   Classes trouvées: " . $classesEntreprise->count() . "\n";
            
            foreach ($classesEntreprise as $classe) {
                echo "   - Classe {$classe->numero}: {$classe->nom}\n";
            }
        } else {
            echo "   ⚠️ L'utilisateur n'a pas d'entreprise_id\n";
        }
    } else {
        echo "   ❌ Aucun utilisateur trouvé\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
