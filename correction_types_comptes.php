<?php

// Script pour corriger automatiquement les types selon les classes comptables

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ayanna', 'root', '');
    echo "=== CORRECTION DES TYPES DE COMPTES ===\n\n";

    echo "1. ANALYSE ACTUELLE :\n";
    
    // Vérifier les incohérences
    $stmt = $pdo->query("
        SELECT 
            cc.numero as classe,
            cc.nom as classe_nom,
            c.type,
            COUNT(*) as nb_comptes
        FROM comptes c
        JOIN classes_comptables cc ON c.classe_comptable_id = cc.id
        GROUP BY cc.numero, c.type
        ORDER BY cc.numero, c.type
    ");
    
    $repartition = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($repartition as $row) {
        echo "Classe {$row['classe']} ({$row['classe_nom']}) - Type: {$row['type']} → {$row['nb_comptes']} comptes\n";
    }
    
    echo "\n2. CORRECTIONS RECOMMANDÉES :\n";
    
    // Logique de correction
    $corrections = [
        '1' => 'passif',     // Comptes de capitaux
        '2' => 'actif',      // Immobilisations  
        '3' => 'actif',      // Stocks
        '4' => 'actif',      // Tiers (par défaut actif, mais peut varier)
        '5' => 'actif',      // Financiers
        '6' => 'charge',     // Charges (ne vont pas au bilan)
        '7' => 'produit'     // Produits (ne vont pas au bilan)
    ];
    
    foreach ($corrections as $classe => $typeAttendu) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as nb_a_corriger
            FROM comptes c
            JOIN classes_comptables cc ON c.classe_comptable_id = cc.id
            WHERE cc.numero = ? AND c.type != ?
        ");
        $stmt->execute([$classe, $typeAttendu]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['nb_a_corriger'] > 0) {
            echo "⚠️  Classe {$classe} : {$result['nb_a_corriger']} comptes à corriger vers '{$typeAttendu}'\n";
        } else {
            echo "✅ Classe {$classe} : Tous les comptes ont le bon type '{$typeAttendu}'\n";
        }
    }
    
    echo "\n3. VOULEZ-VOUS APPLIQUER LES CORRECTIONS ? (y/N) : ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    
    if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
        echo "\nApplication des corrections...\n";
        
        foreach ($corrections as $classe => $typeAttendu) {
            $stmt = $pdo->prepare("
                UPDATE comptes c
                JOIN classes_comptables cc ON c.classe_comptable_id = cc.id
                SET c.type = ?
                WHERE cc.numero = ? AND c.type != ?
            ");
            $result = $stmt->execute([$typeAttendu, $classe, $typeAttendu]);
            $nbModifies = $stmt->rowCount();
            
            if ($nbModifies > 0) {
                echo "✅ Classe {$classe} : {$nbModifies} comptes corrigés vers '{$typeAttendu}'\n";
            }
        }
        
        echo "\n✅ Corrections appliquées avec succès !\n";
        
    } else {
        echo "\nAucune correction appliquée.\n";
    }
    
    echo "\n4. RÉSULTAT FINAL RECOMMANDÉ :\n";
    echo "BILAN (actif/passif) :\n";
    echo "- Classes 1,2,3,4,5 → apparaissent dans le bilan\n";
    echo "- Actif : Classes 2,3,4,5 (patrimoine positif)\n";
    echo "- Passif : Classe 1 + certains comptes de classe 4 (dettes, capitaux)\n\n";
    
    echo "COMPTE DE RÉSULTAT (charge/produit) :\n";
    echo "- Classe 6 (charges) → n'apparaissent PAS dans le bilan\n";
    echo "- Classe 7 (produits) → n'apparaissent PAS dans le bilan\n";
    echo "- Le résultat (produits - charges) est reporté au bilan en classe 1\n";

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

?>
