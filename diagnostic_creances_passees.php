<?php

// Diagnostic approfondi du problème de relation pointDeVente pour les créances
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

echo "=== DIAGNOSTIC CRÉANCES PASSÉES ===\n\n";

// 1. Vérifier les créances avec problème de relation
echo "1. Analyse des créances avec relations problématiques :\n";
$stmt = $pdo->query("
    SELECT c.id, c.mode_paiement, c.created_at,
           pa.id as panier_id, pa.point_de_vente_id,
           pdv.id as pdv_id, pdv.nom as pdv_nom, pdv.entreprise_id,
           cl.nom as client_nom,
           CASE 
               WHEN pa.point_de_vente_id IS NULL THEN 'PANIER SANS POINT DE VENTE'
               WHEN pdv.id IS NULL THEN 'POINT DE VENTE INEXISTANT'
               WHEN pdv.entreprise_id IS NULL THEN 'POINT DE VENTE SANS ENTREPRISE'
               ELSE 'OK'
           END as probleme
    FROM commandes c
    JOIN paniers pa ON c.panier_id = pa.id
    LEFT JOIN points_de_vente pdv ON pa.point_de_vente_id = pdv.id
    LEFT JOIN clients cl ON pa.client_id = cl.id
    WHERE c.mode_paiement = 'compte_client'
    ORDER BY c.created_at DESC
    LIMIT 10
");

$creances = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($creances as $creance) {
    echo "  Commande #{$creance['id']} ({$creance['created_at']}) - {$creance['client_nom']}\n";
    echo "    Panier: #{$creance['panier_id']} → Point de vente: #{$creance['point_de_vente_id']}\n";
    echo "    Point de vente DB: #{$creance['pdv_id']} ({$creance['pdv_nom']}) → Entreprise: #{$creance['entreprise_id']}\n";
    echo "    STATUS: {$creance['probleme']}\n\n";
}

// 2. Identifier les créances problématiques
echo "2. Créances avec relations cassées :\n";
$stmt = $pdo->query("
    SELECT c.id, c.mode_paiement, c.created_at, cl.nom as client_nom
    FROM commandes c
    JOIN paniers pa ON c.panier_id = pa.id
    LEFT JOIN points_de_vente pdv ON pa.point_de_vente_id = pdv.id
    LEFT JOIN clients cl ON pa.client_id = cl.id
    WHERE c.mode_paiement = 'compte_client'
    AND (pa.point_de_vente_id IS NULL OR pdv.id IS NULL OR pdv.entreprise_id IS NULL)
    ORDER BY c.created_at DESC
");

$creancesProblematiques = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($creancesProblematiques)) {
    echo "  ✅ Aucune créance avec relation cassée trouvée\n";
} else {
    echo "  ❌ Créances problématiques trouvées :\n";
    foreach ($creancesProblematiques as $pb) {
        echo "    - Commande #{$pb['id']} ({$pb['created_at']}) - {$pb['client_nom']}\n";
    }
}

// 3. Vérifier si le problème vient des créances récentes vs anciennes
echo "\n3. Analyse par date de création :\n";
$stmt = $pdo->query("
    SELECT 
        DATE(c.created_at) as date_creation,
        COUNT(*) as total_creances,
        COUNT(pdv.id) as creances_avec_pdv,
        COUNT(pdv.entreprise_id) as creances_avec_entreprise
    FROM commandes c
    JOIN paniers pa ON c.panier_id = pa.id
    LEFT JOIN points_de_vente pdv ON pa.point_de_vente_id = pdv.id
    WHERE c.mode_paiement = 'compte_client'
    GROUP BY DATE(c.created_at)
    ORDER BY DATE(c.created_at) DESC
    LIMIT 7
");

$statsByDate = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($statsByDate as $stat) {
    $problemes = $stat['total_creances'] - $stat['creances_avec_entreprise'];
    echo "  {$stat['date_creation']} : {$stat['total_creances']} créances, {$problemes} avec problème\n";
}

// 4. Proposition de solution
echo "\n4. Solution recommandée :\n";
if (!empty($creancesProblematiques)) {
    echo "  ❌ Il y a des créances avec relations cassées\n";
    echo "  💡 Solution : Ajouter une vérification NULL dans le contrôleur\n";
} else {
    echo "  ✅ Toutes les créances ont des relations correctes\n";
    echo "  💡 Le problème pourrait venir du cache Eloquent ou d'un autre facteur\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";
