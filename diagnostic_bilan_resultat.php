<?php

// Test diagnostic pour compte de résultat et bilan

require_once __DIR__ . '/vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ayanna', 'root', '');
    echo "=== DIAGNOSTIC COMPTE DE RÉSULTAT ET BILAN ===\n\n";

    // 1. Vérifier les comptes par classe
    echo "1. RÉPARTITION DES COMPTES PAR CLASSE :\n";
    $stmt = $pdo->query("
        SELECT classe_comptable, COUNT(*) as nb_comptes, GROUP_CONCAT(CONCAT(numero, '-', nom) SEPARATOR ', ') as comptes
        FROM comptes 
        GROUP BY classe_comptable 
        ORDER BY classe_comptable
    ");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($classes as $classe) {
        echo "Classe {$classe['classe_comptable']}: {$classe['nb_comptes']} comptes\n";
        echo "  → " . substr($classe['comptes'], 0, 100) . (strlen($classe['comptes']) > 100 ? '...' : '') . "\n\n";
    }

    // 2. Vérifier les écritures par classe de compte
    echo "2. MOUVEMENTS PAR CLASSE DE COMPTE (derniers 30 jours) :\n";
    $stmt = $pdo->query("
        SELECT 
            c.classe_comptable,
            c.numero,
            c.nom,
            SUM(e.debit) as total_debit,
            SUM(e.credit) as total_credit,
            (SUM(e.credit) - SUM(e.debit)) as solde_credit,
            (SUM(e.debit) - SUM(e.credit)) as solde_debit
        FROM comptes c
        LEFT JOIN ecriture_comptables e ON c.id = e.compte_id
        LEFT JOIN journal_comptables j ON e.journal_id = j.id
        WHERE j.date_ecriture >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY c.id
        HAVING (SUM(e.debit) > 0 OR SUM(e.credit) > 0)
        ORDER BY c.classe_comptable, c.numero
    ");
    $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $classes_resumees = [];
    foreach ($mouvements as $mvt) {
        $classe = $mvt['classe_comptable'];
        if (!isset($classes_resumees[$classe])) {
            $classes_resumees[$classe] = [
                'comptes' => [],
                'total_debit' => 0,
                'total_credit' => 0
            ];
        }
        $classes_resumees[$classe]['comptes'][] = $mvt;
        $classes_resumees[$classe]['total_debit'] += $mvt['total_debit'];
        $classes_resumees[$classe]['total_credit'] += $mvt['total_credit'];
    }

    foreach ($classes_resumees as $classe => $data) {
        echo "CLASSE {$classe} :\n";
        echo "  Total débits: " . number_format($data['total_debit'], 0, ',', ' ') . " FCFA\n";
        echo "  Total crédits: " . number_format($data['total_credit'], 0, ',', ' ') . " FCFA\n";
        
        foreach ($data['comptes'] as $compte) {
            echo "  - {$compte['numero']} {$compte['nom']} : ";
            echo "D=" . number_format($compte['total_debit'], 0, ',', ' ') . " ";
            echo "C=" . number_format($compte['total_credit'], 0, ',', ' ') . "\n";
        }
        echo "\n";
    }

    // 3. Test spécifique pour les ventes (devrait être en classe 7)
    echo "3. VÉRIFICATION DES COMPTES DE VENTE :\n";
    $stmt = $pdo->query("
        SELECT * FROM comptes 
        WHERE (nom LIKE '%vente%' OR nom LIKE '%chiffre%' OR numero LIKE '7%')
        ORDER BY classe_comptable, numero
    ");
    $comptesVente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($comptesVente)) {
        echo "❌ PROBLÈME : Aucun compte de vente trouvé !\n";
    } else {
        foreach ($comptesVente as $compte) {
            echo "- {$compte['numero']} {$compte['nom']} (Classe: {$compte['classe_comptable']}, Type: {$compte['type']})\n";
        }
    }

    // 4. Test de calcul comme dans le contrôleur
    echo "\n4. SIMULATION DU CALCUL DU COMPTE DE RÉSULTAT :\n";
    
    // Produits (classe 7)
    $stmt = $pdo->query("
        SELECT 
            c.numero,
            c.nom,
            SUM(e.credit) as total_credit,
            SUM(e.debit) as total_debit,
            (SUM(e.credit) - SUM(e.debit)) as montant_produit
        FROM comptes c
        LEFT JOIN ecriture_comptables e ON c.id = e.compte_id
        LEFT JOIN journal_comptables j ON e.journal_id = j.id
        WHERE c.classe_comptable = '7'
        AND j.date_ecriture >= '2025-01-01'
        GROUP BY c.id
        HAVING (SUM(e.credit) - SUM(e.debit)) > 0
        ORDER BY c.numero
    ");
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "PRODUITS (Classe 7) :\n";
    $totalProduits = 0;
    if (empty($produits)) {
        echo "❌ Aucun produit avec solde créditeur trouvé\n";
    } else {
        foreach ($produits as $produit) {
            echo "- {$produit['numero']} {$produit['nom']} : " . number_format($produit['montant_produit'], 0, ',', ' ') . " FCFA\n";
            $totalProduits += $produit['montant_produit'];
        }
    }
    echo "TOTAL PRODUITS : " . number_format($totalProduits, 0, ',', ' ') . " FCFA\n\n";
    
    // Charges (classe 6)
    $stmt = $pdo->query("
        SELECT 
            c.numero,
            c.nom,
            SUM(e.debit) as total_debit,
            SUM(e.credit) as total_credit,
            (SUM(e.debit) - SUM(e.credit)) as montant_charge
        FROM comptes c
        LEFT JOIN ecriture_comptables e ON c.id = e.compte_id
        LEFT JOIN journal_comptables j ON e.journal_id = j.id
        WHERE c.classe_comptable = '6'
        AND j.date_ecriture >= '2025-01-01'
        GROUP BY c.id
        HAVING (SUM(e.debit) - SUM(e.credit)) > 0
        ORDER BY c.numero
    ");
    $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "CHARGES (Classe 6) :\n";
    $totalCharges = 0;
    if (empty($charges)) {
        echo "❌ Aucune charge avec solde débiteur trouvée\n";
    } else {
        foreach ($charges as $charge) {
            echo "- {$charge['numero']} {$charge['nom']} : " . number_format($charge['montant_charge'], 0, ',', ' ') . " FCFA\n";
            $totalCharges += $charge['montant_charge'];
        }
    }
    echo "TOTAL CHARGES : " . number_format($totalCharges, 0, ',', ' ') . " FCFA\n\n";
    
    $resultat = $totalProduits - $totalCharges;
    echo "RÉSULTAT : " . number_format($resultat, 0, ',', ' ') . " FCFA\n\n";

    // 5. Vérification du bilan
    echo "5. SIMULATION DU CALCUL DU BILAN :\n";
    
    // Actifs (classes 1, 2, 3, 4, 5 sauf si type='passif')
    $stmt = $pdo->query("
        SELECT 
            c.numero,
            c.nom,
            c.type,
            c.classe_comptable,
            c.solde_initial,
            SUM(e.debit) as total_debit,
            SUM(e.credit) as total_credit
        FROM comptes c
        LEFT JOIN ecriture_comptables e ON c.id = e.compte_id
        WHERE c.type = 'actif'
        GROUP BY c.id
        ORDER BY c.numero
    ");
    $actifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "ACTIFS :\n";
    $totalActif = 0;
    foreach ($actifs as $actif) {
        $solde = $actif['solde_initial'] + $actif['total_debit'] - $actif['total_credit'];
        if ($solde > 0) {
            echo "- {$actif['numero']} {$actif['nom']} : " . number_format($solde, 0, ',', ' ') . " FCFA\n";
            $totalActif += $solde;
        }
    }
    echo "TOTAL ACTIF : " . number_format($totalActif, 0, ',', ' ') . " FCFA\n\n";

    echo "=== DIAGNOSTIC TERMINÉ ===\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "\n";
    echo "Vérifiez que :\n";
    echo "- MySQL est démarré\n";
    echo "- La base 'ayanna' existe\n";
    echo "- Les paramètres de connexion sont corrects\n";
}

?>
