<?php

echo "=== TEST RAPIDE COMPTABILITÉ ===\n";

// Test simple via artisan pour vérifier que Laravel démarre
$output = shell_exec('php artisan --version 2>&1');
echo "Laravel version : " . trim($output) . "\n";

// Test de connexion à la base
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ayanna', 'root', '');
    echo "✓ Connexion base de données OK\n";
    
    // Compter les écritures récentes
    $stmt = $pdo->query("SELECT COUNT(*) as nb FROM journal_comptables WHERE DATE(created_at) = CURDATE()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "- Écritures aujourd'hui : " . $result['nb'] . "\n";
    
    // Dernière écriture
    $stmt = $pdo->query("SELECT numero_piece, libelle, montant_total, created_at FROM journal_comptables ORDER BY created_at DESC LIMIT 1");
    $derniere = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($derniere) {
        echo "- Dernière écriture : Pièce " . $derniere['numero_piece'] . 
             " | " . $derniere['libelle'] . 
             " | " . $derniere['montant_total'] . "€" .
             " | " . $derniere['created_at'] . "\n";
    } else {
        echo "- Aucune écriture trouvée\n";
    }
    
    // Vérifier l'équilibre général
    $stmt = $pdo->query("SELECT SUM(debit) as total_debit, SUM(credit) as total_credit FROM ecriture_comptables");
    $equilibre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($equilibre['total_debit'] == $equilibre['total_credit']) {
        echo "✓ Équilibre général OK (Débit: " . $equilibre['total_debit'] . "€ = Crédit: " . $equilibre['total_credit'] . "€)\n";
    } else {
        echo "✗ DÉSÉQUILIBRE GÉNÉRAL ! (Débit: " . $equilibre['total_debit'] . "€ ≠ Crédit: " . $equilibre['total_credit'] . "€)\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Erreur base de données : " . $e->getMessage() . "\n";
    echo "Vérifiez que :\n";
    echo "- MySQL est démarré\n";
    echo "- La base 'ayanna' existe\n";
    echo "- Les identifiants sont corrects\n";
}

echo "\n=== INSTRUCTIONS POUR TEST MANUEL ===\n";
echo "1. Ouvrez votre application web\n";
echo "2. Créez une nouvelle commande\n";
echo "3. Ajoutez quelques produits\n";
echo "4. Validez la vente\n";
echo "5. Allez dans Comptabilité → Journal\n";
echo "6. Vérifiez qu'une nouvelle ligne apparaît\n";
echo "7. Re-exécutez ce script pour voir l'évolution\n";

echo "\n=== FIN TEST ===\n";

?>
