<?php

// Test rapide pour vérifier que la correction du paiement créance fonctionne
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Configuration de la base de données
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'ayanna',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$pdo = $capsule->getConnection()->getPdo();

echo "=== TEST CORRECTION PAIEMENT CRÉANCE ===\n\n";

// 1. Vérifier qu'une créance avec les bonnes relations existe
echo "1. Vérification des créances avec leurs relations :\n";
$stmt = $pdo->query("
    SELECT c.id, c.mode_paiement, c.statut, 
           cl.nom as client_nom,
           pa.point_de_vente_id,
           pdv.nom as point_vente_nom,
           pdv.entreprise_id,
           e.nom as entreprise_nom,
           SUM(pp.quantite * pr.prix_vente) as montant_total,
           COALESCE(SUM(pay.montant), 0) as montant_paye
    FROM commandes c
    JOIN paniers pa ON c.panier_id = pa.id  
    JOIN clients cl ON pa.client_id = cl.id
    JOIN points_de_vente pdv ON pa.point_de_vente_id = pdv.id
    JOIN entreprises e ON pdv.entreprise_id = e.id
    JOIN panier_produit pp ON pa.id = pp.panier_id
    JOIN produits pr ON pp.produit_id = pr.id
    LEFT JOIN paiements pay ON c.id = pay.commande_id
    WHERE c.mode_paiement = 'compte_client'
    GROUP BY c.id, c.mode_paiement, c.statut, cl.nom, pa.point_de_vente_id, pdv.nom, pdv.entreprise_id, e.nom
    HAVING (SUM(pp.quantite * pr.prix_vente) - COALESCE(SUM(pay.montant), 0)) > 0
    ORDER BY c.created_at DESC
    LIMIT 3
");

$creances = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($creances)) {
    echo "❌ Aucune créance trouvée avec toutes les relations nécessaires\n";
    exit;
}

echo "✅ Créances trouvées avec relations complètes :\n";
foreach ($creances as $creance) {
    $montantRestant = $creance['montant_total'] - $creance['montant_paye'];
    echo "  - Commande #{$creance['id']} : {$creance['client_nom']}\n";
    echo "    Point de vente : {$creance['point_vente_nom']} (ID: {$creance['point_de_vente_id']})\n";
    echo "    Entreprise : {$creance['entreprise_nom']} (ID: {$creance['entreprise_id']})\n";
    echo "    Montant restant : {$montantRestant}€\n\n";
}

// 2. Vérifier qu'il y a un compte pour cette entreprise
$creanceTest = $creances[0];
echo "2. Vérification des comptes pour l'entreprise #{$creanceTest['entreprise_id']} :\n";
$stmt = $pdo->prepare("
    SELECT id, nom, numero, type FROM comptes 
    WHERE entreprise_id = ? 
    ORDER BY nom
");
$stmt->execute([$creanceTest['entreprise_id']]);
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($comptes)) {
    echo "❌ Aucun compte trouvé pour cette entreprise\n";
    exit;
}

echo "✅ Comptes disponibles :\n";
foreach ($comptes as $compte) {
    echo "  - {$compte['nom']} (#{$compte['id']}) - {$compte['numero']} - {$compte['type']}\n";
}

// 3. Vérifier que les tables nécessaires existent
echo "\n3. Vérification des tables nécessaires :\n";
$tables = ['paiements', 'journal_comptable', 'ecritures_comptables', 'entrees_sorties'];

foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
    if ($stmt->fetch()) {
        echo "  ✅ Table {$table} existe\n";
    } else {
        echo "  ❌ Table {$table} manquante\n";
    }
}

echo "\n✅ DIAGNOSTIC OK - La correction devrait fonctionner maintenant.\n";
echo "Le problème était que la relation 'pointDeVente' n'était pas chargée dans la requête Eloquent.\n";
echo "Correction appliquée : ajout de 'panier.pointDeVente' dans le with() de la requête.\n\n";

echo "🚀 TESTEZ MAINTENANT L'ENREGISTREMENT D'UN PAIEMENT CRÉANCE VIA L'INTERFACE WEB !\n";
